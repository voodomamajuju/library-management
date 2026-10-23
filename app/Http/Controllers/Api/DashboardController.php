<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Borrowing;
use App\Models\Member;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    /**
     * Return library dashboard statistics calculated from current records.
     */
    public function index(): JsonResponse
    {
        $totalBooks = Book::count();
        $totalMembers = Member::count();

        $booksCurrentlyBorrowed = Borrowing::query()
            ->whereNull('returned_at')
            ->count();

        $availableBooks = (int) Book::query()->sum('available_copies');

        $overdueBooks = Borrowing::query()
            ->whereNull('returned_at')
            ->where('due_at', '<', now())
            ->count();

        $topBorrowedBooks = Book::query()
            ->withCount('borrowings')
            ->having('borrowings_count', '>', 0)
            ->orderByDesc('borrowings_count')
            ->orderBy('id')
            ->limit(5)
            ->get()
            ->map(function (Book $book) {
                return [
                    'book_id' => $book->id,
                    'title' => $book->title,
                    'author' => $book->author,
                    'borrow_count' => $book->borrowings_count,
                ];
            })
            ->values();

        return response()->json([
            'message' => 'Dashboard retrieved successfully.',
            'data' => [
                'total_books' => $totalBooks,
                'total_members' => $totalMembers,
                'books_currently_borrowed' => $booksCurrentlyBorrowed,
                'available_books' => $availableBooks,
                'overdue_books' => $overdueBooks,
                'top_borrowed_books' => $topBorrowedBooks,
            ],
        ], 200);
    }
}
