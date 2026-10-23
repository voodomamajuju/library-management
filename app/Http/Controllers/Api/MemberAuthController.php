<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\MemberRegisterRequest;
use App\Models\Member;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class MemberAuthController extends Controller
{
    /**
     * Set the initial password for an existing active member.
     */
    public function register(MemberRegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        return DB::transaction(function () use ($validated): JsonResponse {
            $member = Member::where('email', $validated['email'])->lockForUpdate()->first();

            if (! $member) {
                return response()->json([
                    'message' => 'Unable to create a member account with these details.',
                ], 422);
            }

            if ($member->status !== 'Active') {
                return response()->json([
                    'message' => 'Your library account is not active.',
                ], 403);
            }

            if ($member->password) {
                return response()->json([
                    'message' => 'An account already exists for this member. Please log in.',
                ], 409);
            }

            $member->password = Hash::make($validated['password']);
            $member->save();

            return response()->json([
                'message' => 'Member account created successfully.',
            ], 201);
        });
    }

    /**
     * Authenticate a member and issue a Sanctum token.
     *
     * Only Active members with an activated password may log in.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $member = Member::where('email', $validated['email'])->first();

        // Non-existent member, not-yet-activated member (null password), or wrong
        // password all return the same generic error so account state is not leaked.
        if (! $member || ! $member->password || ! Hash::check($validated['password'], $member->password)) {
            return response()->json([
                'message' => 'Invalid email or password.',
            ], 401);
        }

        // Checked only after the password is proven correct.
        if ($member->status !== 'Active') {
            return response()->json([
                'message' => 'Your membership is inactive. Please contact the library.',
            ], 403);
        }

        $token = $member->createToken('member_auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Member logged in successfully.',
            'member' => $member,
            'token' => $token,
        ], 200);
    }

    /**
     * Revoke the current member token.
     */
    public function logout(Request $request): JsonResponse
    {
        $member = $request->user();

        if (! $member instanceof Member) {
            return response()->json([
                'message' => 'Forbidden.',
            ], 403);
        }

        $member->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully.',
        ], 200);
    }

    /**
     * Return the authenticated member (requires a member token).
     */
    public function me(Request $request): JsonResponse
    {
        $member = $request->user();

        if (! $member instanceof Member) {
            return response()->json([
                'message' => 'Forbidden.',
            ], 403);
        }

        return response()->json([
            'message' => 'Member authenticated.',
            'member' => $member,
        ], 200);
    }
}
