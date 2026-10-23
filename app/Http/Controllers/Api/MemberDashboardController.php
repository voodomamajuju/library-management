<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BorrowingResource;
use App\Http\Resources\MemberResource;
use App\Models\Borrowing;
use App\Models\Member;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class MemberDashboardController extends Controller
{
    /**
     * Maximum number of active borrowings allowed per member.
     *
     * Mirrors the existing business rule enforced in BorrowingController::store()
     * ("Member cannot borrow more than 3 books at a time").
     */
    private const MAX_ACTIVE_BORROWINGS = 3;

    /**
     * Dashboard data for the authenticated member.
     *
     * The member is resolved from the Sanctum token only. No member id is
     * accepted from the request, so members cannot read each other's data.
     */
    public function index(Request $request): JsonResponse
    {
        $member = $request->user();

        if (! $member instanceof Member) {
            return response()->json([
                'message' => 'Forbidden.',
            ], 403);
        }

        $forMember = fn () => Borrowing::query()->where('member_id', $member->id);

        $currentlyBorrowed = $forMember()->whereNull('returned_at')->count();
        $overdue = $forMember()->whereNull('returned_at')->where('due_at', '<', now())->count();
        $returned = $forMember()->whereNotNull('returned_at')->count();

        $currentBorrowings = $forMember()
            ->with('book')
            ->whereNull('returned_at')
            ->orderByDesc('borrowed_at')
            ->get();

        $history = $forMember()
            ->with('book')
            ->orderByDesc('borrowed_at')
            ->get();

        return response()->json([
            'message' => 'Member dashboard retrieved successfully.',
            'data' => [
                'member' => (new MemberResource($member))->resolve(),
                'summary' => [
                    'currently_borrowed' => $currentlyBorrowed,
                    'overdue' => $overdue,
                    'returned' => $returned,
                    'borrowing_limit' => self::MAX_ACTIVE_BORROWINGS,
                    'remaining_capacity' => max(0, self::MAX_ACTIVE_BORROWINGS - $currentlyBorrowed),
                ],
                'current_borrowings' => $currentBorrowings
                    ->map(fn (Borrowing $borrowing) => $this->formatBorrowing($borrowing))
                    ->values(),
                'history' => $history
                    ->map(fn (Borrowing $borrowing) => $this->formatBorrowing($borrowing))
                    ->values(),
            ],
        ], 200);
    }

    /**
     * Reuse the existing BorrowingResource shape and add an overdue flag.
     *
     * @return array<string, mixed>
     */
    private function formatBorrowing(Borrowing $borrowing): array
    {
        $isOverdue = is_null($borrowing->returned_at)
            && $borrowing->due_at !== null
            && Carbon::parse($borrowing->due_at)->isPast();

        return array_merge(
            (new BorrowingResource($borrowing))->resolve(),
            ['is_overdue' => $isOverdue]
        );
    }
}
