<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Http\Resources\BookResource;
use App\Models\Book;
use App\Services\BookCsvImporter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Throwable;

class BookController extends Controller
{
    /**
     * Display a listing of books.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Book::query();

        // Search by title
        if ($request->filled('title')) {
            $query->where('title', 'like', '%' . $request->title . '%');
        }

        // Search by author
        if ($request->filled('author')) {
            $query->where('author', 'like', '%' . $request->author . '%');
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Filter by publication year
        if ($request->filled('published_year')) {
            $query->where('published_year', $request->published_year);
        }

        // Sorting
        if ($request->filled('sort_by')) {
            $sortBy = $request->sort_by;

            if (in_array($sortBy, ['title', 'published_year'])) {
                $query->orderBy($sortBy, $request->get('sort_order', 'asc'));
            }
        }

        // Pagination
        $books = $query->paginate(10);

        return response()->json([
            'message' => 'Books retrieved successfully.',
            'data' => BookResource::collection($books),
            'meta' => [
                'current_page' => $books->currentPage(),
                'last_page' => $books->lastPage(),
                'per_page' => $books->perPage(),
                'total' => $books->total(),
            ],
        ], 200);
    }

    /**
     * Display the specified book.
     */
    public function show(Book $book): JsonResponse
    {
        return response()->json([
            'message' => 'Book retrieved successfully.',
            'data' => new BookResource($book),
        ], 200);
    }

    /**
     * Store a newly created book in storage.
     */
    public function store(StoreBookRequest $request): JsonResponse
    {
        $book = Book::create($request->validated());

        return response()->json([
            'message' => 'Book created successfully.',
            'data' => new BookResource($book),
        ], 201);
    }

    /**
     * Update the specified book in storage.
     */
    public function update(UpdateBookRequest $request, Book $book): JsonResponse
    {
        $book->update($request->validated());

        return response()->json([
            'message' => 'Book updated successfully.',
            'data' => new BookResource($book),
        ], 200);
    }

    /**
     * Remove the specified book from storage.
     *
     * A book can only be deleted if it has never been borrowed.
     */
    public function destroy(Book $book): JsonResponse
    {
        if ($book->borrowings()->exists()) {
            return response()->json([
                'message' => 'This book cannot be deleted because it has borrowing history.',
            ], 422);
        }

        $book->delete();

        return response()->json([
            'message' => 'Book deleted successfully.',
        ], 200);
    }

    /**
     * Import books from a CSV file. Invalid files import nothing.
     */
    public function import(Request $request, BookCsvImporter $importer): JsonResponse
    {
        return $importer->import($request);
    }
}
