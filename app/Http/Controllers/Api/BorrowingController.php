<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBorrowingRequest;
use App\Http\Resources\BorrowingResource;
use App\Models\Book;
use App\Models\Borrowing;
use App\Models\Member;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class BorrowingController extends Controller
{
    /**
     * Display a listing of borrowings, newest first.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Borrowing::query()
            ->with('book', 'member')
            ->orderByDesc('borrowed_at');

        $borrowings = $query->paginate(10);

        return response()->json([
            'message' => 'Borrowings retrieved successfully.',
            'data' => BorrowingResource::collection($borrowings),
            'meta' => [
                'current_page' => $borrowings->currentPage(),
                'last_page' => $borrowings->lastPage(),
                'per_page' => $borrowings->perPage(),
                'total' => $borrowings->total(),
            ],
        ], 200);
    }

    /**
     * Display the specified borrowing.
     */
    public function show(Borrowing $borrowing): JsonResponse
    {
        $borrowing->load('book', 'member');

        return response()->json([
            'message' => 'Borrowing retrieved successfully.',
            'data' => new BorrowingResource($borrowing),
        ], 200);
    }

    /**
     * Store a newly created borrowing.
     */
    public function store(StoreBorrowingRequest $request): JsonResponse
    {
        $validated = $request->validated();

        return DB::transaction(function () use ($validated): JsonResponse {
            $book = Book::find($validated['book_id']);

            if (! $book) {
                return response()->json([
                    'message' => 'Book is unavailable.',
                ], 422);
            }

            $member = Member::find($validated['member_id']);

            if (! $member) {
                return response()->json([
                    'message' => 'Member is unavailable.',
                ], 422);
            }

            if ($member->status !== 'Active') {
                return response()->json([
                    'message' => 'Member is unavailable.',
                ], 422);
            }

            if ($book->available_copies < 1) {
                return response()->json([
                    'message' => 'Book has no available copies.',
                ], 422);
            }

            $activeBorrowing = Borrowing::where('book_id', $book->id)
                ->where('member_id', $member->id)
                ->whereNull('returned_at')
                ->first();

            if ($activeBorrowing) {
                return response()->json([
                    'message' => 'Member already has an active borrowing of this book.',
                ], 422);
            }

            $activeCount = Borrowing::where('member_id', $member->id)
                ->whereNull('returned_at')
                ->count();

            if ($activeCount >= 3) {
                return response()->json([
                    'message' => 'Member cannot borrow more than 3 books at a time.',
                ], 422);
            }

            $borrowedAt = $validated['borrowed_at'] ?? now();
            $borrowedAt = is_string($borrowedAt) ? Carbon::parse($borrowedAt) : $borrowedAt;
            $dueAt = $borrowedAt->copy()->addDays(14);

            $borrowing = Borrowing::create([
                'book_id' => $book->id,
                'member_id' => $member->id,
                'borrowed_at' => $borrowedAt,
                'due_at' => $dueAt,
                'returned_at' => null,
            ]);

            $book->update(['available_copies' => $book->available_copies - 1]);

            $borrowing->load('book', 'member');

            return response()->json([
                'message' => 'Book borrowed successfully.',
                'data' => new BorrowingResource($borrowing),
            ], 201);
        });
    }

    /**
     * Return a borrowed book.
     */
    public function returnBook(Borrowing $borrowing): JsonResponse
    {
        return DB::transaction(function () use ($borrowing): JsonResponse {
            if (! is_null($borrowing->returned_at)) {
                return response()->json([
                    'message' => 'Borrowing has already been returned.',
                ], 422);
            }

            $borrowing->update(['returned_at' => now()]);

            $book = Book::withTrashed()->find($borrowing->book_id);

            if ($book) {
                $book->update(['available_copies' => $book->available_copies + 1]);
            }

            $borrowing->load('book', 'member');

            return response()->json([
                'message' => 'Book returned successfully.',
                'data' => new BorrowingResource($borrowing),
            ], 200);
        });
    }
}