<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMemberRequest;
use App\Http\Requests\UpdateMemberRequest;
use App\Http\Resources\MemberResource;
use App\Models\Member;
use App\Services\MemberCsvImporter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    /**
     * Display a listing of members.
     */
    public function index(Request $request): JsonResponse
    {
        $members = Member::paginate(10);

        return response()->json([
            'message' => 'Members retrieved successfully.',
            'data' => MemberResource::collection($members),
            'meta' => [
                'current_page' => $members->currentPage(),
                'last_page' => $members->lastPage(),
                'per_page' => $members->perPage(),
                'total' => $members->total(),
            ],
        ], 200);
    }

    /**
     * Store a newly created member in storage.
     */
    public function store(StoreMemberRequest $request): JsonResponse
    {
        $member = Member::create($request->validated());

        return response()->json([
            'message' => 'Member created successfully.',
            'data' => new MemberResource($member),
        ], 201);
    }

    /**
     * Display the specified member.
     */
    public function show(Member $member): JsonResponse
    {
        return response()->json([
            'message' => 'Member retrieved successfully.',
            'data' => new MemberResource($member),
        ], 200);
    }

    /**
     * Update the specified member in storage.
     */
    public function update(UpdateMemberRequest $request, Member $member): JsonResponse
    {
        $member->update($request->validated());

        return response()->json([
            'message' => 'Member updated successfully.',
            'data' => new MemberResource($member),
        ], 200);
    }

    /**
     * Remove the specified member from storage.
     */
    public function destroy(Member $member): JsonResponse
    {
        $member->delete();

        return response()->json([
            'message' => 'Member deleted successfully.',
        ], 200);
    }

    /**
     * Import members from a CSV file. Invalid files import nothing.
     */
    public function import(Request $request, MemberCsvImporter $importer): JsonResponse
    {
        return $importer->import($request);
    }

}