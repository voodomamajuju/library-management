<?php

namespace App\Services;

use App\Models\Member;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Throwable;

class MemberCsvImporter
{
    public function __construct(private MemberCsvParser $parser)
    {
    }

    /**
     * Import members from a CSV upload.
     *
     * All rows are validated before any insert. If any row fails, nothing is imported.
     */
    public function import(Request $request): JsonResponse
    {
        $fileError = $this->parser->validateFile($request);
        if ($fileError !== null) {
            return $fileError;
        }

        /** @var UploadedFile $file */
        $file = $request->file('file');
        $parsed = $this->parser->parse($file->getRealPath());
        if (isset($parsed['response'])) {
            return $parsed['response'];
        }

        $rows = $parsed['rows'];
        $errors = $this->parser->validateRows($rows);

        if ($errors !== []) {
            return response()->json([
                'success' => false,
                'message' => 'Import failed. No members were imported because one or more rows are invalid. The import is all-or-nothing.',
                'total_rows' => count($rows),
                'imported_rows' => 0,
                'failed_rows' => count(array_unique(array_column($errors, 'row'))),
                'errors' => $errors,
            ], 422);
        }

        try {
            DB::transaction(function () use ($rows) {
                foreach ($rows as $row) {
                    Member::create($row['data']);
                }
            });
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'success' => false,
                'message' => 'Import failed because of a database error. No members were imported.',
                'total_rows' => count($rows),
                'imported_rows' => 0,
                'failed_rows' => count($rows),
                'errors' => [[
                    'row' => null,
                    'field' => null,
                    'message' => 'A database error occurred while importing the members. No rows were saved.',
                ]],
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Members imported successfully.',
            'total_rows' => count($rows),
            'imported_rows' => count($rows),
            'failed_rows' => 0,
            'errors' => [],
        ], 201);
    }
}
