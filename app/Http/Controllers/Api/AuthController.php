<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Services\TotpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * Token-based auth for the REST api, matching the shared frontend contract.
 * The session-based Breeze routes in routes/auth.php remain for web usage.
 */
class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:255', 'unique:users'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['nullable', 'string', 'max:32'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        // Default role is created on first use so a fresh install works out of the box
        $role = Role::firstOrCreate(['name' => 'user']);

        $user = User::create([
            ...$validated,
            'password' => Hash::make($validated['password']),
            'role_id' => $role->id,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'User registered successfully',
            'data' => $this->tokenPayload($user),
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $login = $request->input('usernameOrEmail', $request->input('username_or_email'));
        $password = (string) $request->input('password');

        $user = User::where('username', $login)->orWhere('email', $login)->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid credentials',
            ], 401);
        }

        $user->forceFill(['is_online' => true])->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Login successful',
            'data' => $this->tokenPayload($user),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();
        $request->user()->forceFill(['is_online' => false])->save();

        return response()->json(['status' => 'success', 'message' => 'Logged out successfully']);
    }

    public function checkToken(Request $request): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Token is valid',
            'data' => ['user' => $request->user()],
        ]);
    }

    public function refreshToken(Request $request): JsonResponse
    {
        $refreshToken = $request->input('refreshToken', $request->input('refresh_token'));
        $accessToken = $refreshToken ? PersonalAccessToken::findToken($refreshToken) : null;

        if (! $accessToken || ! $accessToken->can('refresh')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid or expired refresh token',
            ], 401);
        }

        /** @var User $user */
        $user = $accessToken->tokenable;
        $accessToken->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Token refreshed successfully',
            'data' => $this->tokenPayload($user),
        ]);
    }

    public function changePassword(Request $request): JsonResponse
    {
        $current = $request->input('oldPassword', $request->input('current_password'));
        $new = $request->input('newPassword', $request->input('new_password'));

        if (! $current || ! Hash::check($current, $request->user()->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Current password is incorrect',
            ], 422);
        }

        $request->user()->forceFill(['password' => Hash::make((string) $new)])->save();

        return response()->json(['status' => 'success', 'message' => 'Password changed successfully']);
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $user = User::where('email', $request->input('email'))->first();
        // In production, email the token; templates keep it local to the database.
        $user?->forceFill(['reset_password_token' => Str::random(64)])->save();

        return response()->json([
            'status' => 'success',
            'message' => 'If the email exists, a reset link has been sent',
        ]);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $token = (string) $request->input('token');
        $user = $token !== '' ? User::where('reset_password_token', $token)->first() : null;

        if (! $user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid or expired reset token',
            ], 400);
        }

        $request->validate(['password' => ['required', Password::defaults()]]);
        $user->forceFill([
            'password' => Hash::make((string) $request->input('password')),
            'reset_password_token' => null,
        ])->save();

        return response()->json(['status' => 'success', 'message' => 'Password has been reset successfully']);
    }

    public function sendVerifyEmail(Request $request): JsonResponse
    {
        // In production, email the token; templates keep it local to the database.
        $request->user()->forceFill(['verify_token' => Str::random(64)])->save();

        return response()->json(['status' => 'success', 'message' => 'Verification email sent']);
    }

    public function verifyEmail(Request $request): JsonResponse
    {
        $token = (string) $request->input('token');
        $user = $token !== '' ? User::where('verify_token', $token)->first() : null;

        if (! $user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid or expired verification token',
            ], 400);
        }

        $user->markEmailAsVerified();
        $user->forceFill(['verify_token' => null])->save();

        return response()->json(['status' => 'success', 'message' => 'Email verified successfully']);
    }

    public function otpGenerate(Request $request): JsonResponse
    {
        $secret = TotpService::generateSecret();
        $request->user()->forceFill(['otp_secret' => $secret, 'is_otp_enabled' => false])->save();

        return response()->json([
            'status' => 'success',
            'message' => 'OTP secret generated',
            'data' => [
                'secret' => $secret,
                'otpauth_url' => TotpService::provisioningUri($secret, $request->user()->username),
            ],
        ]);
    }

    public function otpVerify(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user->otp_secret) {
            return response()->json(['status' => 'error', 'message' => 'OTP is not set up'], 400);
        }

        if (! TotpService::verify($user->otp_secret, (string) $request->input('otp'))) {
            return response()->json(['status' => 'error', 'message' => 'Invalid OTP code'], 401);
        }

        $user->forceFill(['is_otp_enabled' => true])->save();

        return response()->json(['status' => 'success', 'message' => 'OTP enabled successfully']);
    }

    public function otpValidate(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user->otp_secret) {
            return response()->json(['status' => 'error', 'message' => 'OTP is not set up'], 400);
        }

        if (! TotpService::verify($user->otp_secret, (string) $request->input('otp'))) {
            return response()->json(['status' => 'error', 'message' => 'Invalid OTP code'], 401);
        }

        return response()->json(['status' => 'success', 'message' => 'OTP is valid']);
    }

    public function otpDisable(Request $request): JsonResponse
    {
        $request->user()->forceFill(['otp_secret' => null, 'is_otp_enabled' => false])->save();

        return response()->json(['status' => 'success', 'message' => 'OTP disabled successfully']);
    }

    private function tokenPayload(User $user): array
    {
        return [
            'user' => $user->load('role'),
            'token' => $user->createToken('api')->plainTextToken,
            'refreshToken' => $user->createToken('refresh', ['refresh'])->plainTextToken,
        ];
    }
}
