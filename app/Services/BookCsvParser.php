<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class BookCsvParser
{
    /**
     * @var list<string>
     */
    private array $columns = [
        'title',
        'author',
        'isbn',
        'category',
        'publisher',
        'published_year',
        'total_copies',
        'available_copies',
    ];

    public function validateFile(Request $request): ?JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'file' => ['required', 'file', 'max:2048'],
        ]);

        if ($validator->fails()) {
            return $this->fileError($validator->errors()->first('file') ?? 'A CSV file is required.');
        }

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());
        $mime = strtolower((string) $file->getMimeType());
        $allowedMimes = [
            'text/csv',
            'text/plain',
            'application/csv',
            'application/vnd.ms-excel',
            'text/comma-separated-values',
            'application/x-empty',
        ];

        if ($extension !== 'csv' || ! in_array($mime, $allowedMimes, true)) {
            return $this->fileError('The uploaded file must be a CSV file.');
        }

        return null;
    }

    /**
     * @return array{rows?: list<array{row: int, data: array<string, mixed>}>, response?: JsonResponse}
     */
    public function parse(string $path): array
    {
        $handle = fopen($path, 'r');
        if ($handle === false) {
            return ['response' => $this->fileError('The CSV file could not be read.')];
        }

        $header = fgetcsv($handle);
        if ($header === false || $this->normalizeHeader($header) === []) {
            fclose($handle);

            return ['response' => $this->fileError('The CSV file is empty.')];
        }

        if ($this->normalizeHeader($header) !== $this->columns) {
            fclose($handle);

            return ['response' => $this->fileError(
                'The CSV header is missing or incorrect. Expected columns: '.implode(',', $this->columns).'.',
                'header'
            )];
        }

        $body = $this->readBody($handle);
        fclose($handle);

        return $body;
    }

    /**
     * @param  list<array{row: int, data: array<string, mixed>}>  $rows
     * @return list<array{row: int, field: string|null, message: string}>
     */
    public function validateRows(array $rows): array
    {
        $errors = [];
        $seenIsbns = [];

        foreach ($rows as $row) {
            $isbn = (string) ($row['data']['isbn'] ?? '');

            if ($isbn !== '' && isset($seenIsbns[$isbn])) {
                $errors[] = [
                    'row' => $row['row'],
                    'field' => 'isbn',
                    'message' => 'The isbn has already been used in this CSV (row '.$seenIsbns[$isbn].').',
                ];
            } elseif ($isbn !== '') {
                $seenIsbns[$isbn] = $row['row'];
            }

            $validator = Validator::make($row['data'], $this->rules());
            if (! $validator->fails()) {
                continue;
            }

            foreach ($validator->errors()->messages() as $field => $messages) {
                foreach ($messages as $message) {
                    $errors[] = [
                        'row' => $row['row'],
                        'field' => $field,
                        'message' => $message,
                    ];
                }
            }
        }

        return $errors;
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:255'],
            'isbn' => ['required', 'string', 'max:255', Rule::unique('books', 'isbn')],
            'category' => ['required', 'string', 'max:255'],
            'publisher' => ['required', 'string', 'max:255'],
            'published_year' => ['required', 'integer', 'min:1', 'max:'.date('Y')],
            'total_copies' => ['required', 'integer', 'gt:0'],
            'available_copies' => ['required', 'integer', 'min:0', 'lte:total_copies'],
        ];
    }

    /**
     * @param  resource  $handle
     * @return array{rows?: list<array{row: int, data: array<string, mixed>}>, response?: JsonResponse}
     */
    private function readBody($handle): array
    {
        $rows = [];
        $line = 1;

        while (($record = fgetcsv($handle)) !== false) {
            $line++;

            if ($this->isBlank($record)) {
                continue;
            }

            if (count($record) !== count($this->columns)) {
                return ['response' => response()->json([
                    'success' => false,
                    'message' => 'Import failed. No books were imported because the CSV contains a malformed row. The import is all-or-nothing.',
                    'total_rows' => 0,
                    'imported_rows' => 0,
                    'failed_rows' => 1,
                    'errors' => [[
                        'row' => $line,
                        'field' => null,
                        'message' => 'This row does not have the expected number of columns.',
                    ]],
                ], 422)];
            }

            $values = array_map(fn ($value) => trim((string) $value), $record);
            $rows[] = [
                'row' => $line,
                'data' => array_combine($this->columns, $values),
            ];
        }

        if ($rows === []) {
            return ['response' => $this->fileError('The CSV file has no book rows to import.')];
        }

        return ['rows' => $rows];
    }

    /**
     * @param  list<string|null>  $header
     * @return list<string>
     */
    private function normalizeHeader(array $header): array
    {
        $columns = [];

        foreach ($header as $column) {
            $column = preg_replace('/^\xEF\xBB\xBF/', '', (string) $column) ?? '';
            $column = strtolower(trim($column));
            if ($column !== '') {
                $columns[] = $column;
            }
        }

        return $columns;
    }

    /**
     * @param  list<string|null>  $record
     */
    private function isBlank(array $record): bool
    {
        if ($record === [null]) {
            return true;
        }

        foreach ($record as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    private function fileError(string $message, string $field = 'file'): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'total_rows' => 0,
            'imported_rows' => 0,
            'failed_rows' => 0,
            'errors' => [[
                'row' => null,
                'field' => $field,
                'message' => $message,
            ]],
        ], 422);
    }
}
