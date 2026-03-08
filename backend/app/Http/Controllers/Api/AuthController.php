<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    /**
     * 註冊（email + 密碼）
     *
     * POST /api/auth/register
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'username' => 'required|string|max:50|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'display_name' => 'nullable|string|max:100',
        ]);

        $user = User::create([
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password_hash' => Hash::make($validated['password']),
            'display_name' => $validated['display_name'] ?? null,
        ]);

        $token = $user->createToken('auth')->plainTextToken;
        $user->update(['last_login' => now()]);

        return $this->successResponse([
            'user' => $this->userResponse($user),
            'token' => $token,
            'token_type' => 'Bearer',
        ], '註冊成功', 201);
    }

    /**
     * 登入（email + 密碼）
     *
     * POST /api/auth/login
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password_hash)) {
            return $this->errorResponse('帳號或密碼錯誤', 401);
        }

        if (!$user->is_active) {
            return $this->errorResponse('帳號已停用', 403);
        }

        $user->update(['last_login' => now()]);
        $token = $user->createToken('auth')->plainTextToken;

        return $this->successResponse([
            'user' => $this->userResponse($user),
            'token' => $token,
            'token_type' => 'Bearer',
        ], '登入成功');
    }

    /**
     * 登出（撤銷目前 token）
     *
     * POST /api/auth/logout
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();
        return $this->successResponse(null, '已登出');
    }

    /**
     * 取得目前登入使用者
     *
     * GET /api/auth/me
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return $this->errorResponse('未登入', 401);
        }
        return $this->successResponse($this->userResponse($user), '取得使用者成功');
    }

    /**
     * 導向 Google OAuth 授權頁
     *
     * GET /api/auth/google
     */
    public function redirectToGoogle(): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    /**
     * Google OAuth 回調：建立/取得使用者並回傳 token，再導向前端
     *
     * GET /api/auth/google/callback
     * 成功後會 redirect 到前端登入成功頁並帶上 token（query string）
     */
    public function handleGoogleCallback(Request $request): \Illuminate\Http\RedirectResponse|JsonResponse
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
        } catch (\Throwable $e) {
            return $this->errorResponse('Google 登入失敗：' . $e->getMessage(), 400);
        }

        $user = User::where('google_id', $googleUser->getId())->first();

        if (!$user) {
            $user = User::where('email', $googleUser->getEmail())->first();
            if ($user) {
                $user->update([
                    'google_id' => $googleUser->getId(),
                    'display_name' => $user->display_name ?? $googleUser->getName(),
                ]);
            } else {
                $user = User::create([
                    'username' => $this->uniqueUsernameFromEmail($googleUser->getEmail()),
                    'email' => $googleUser->getEmail(),
                    'password_hash' => Hash::make(Str::random(32)),
                    'display_name' => $googleUser->getName(),
                    'google_id' => $googleUser->getId(),
                ]);
            }
        }

        if (!$user->is_active) {
            return $this->errorResponse('帳號已停用', 403);
        }

        $user->update(['last_login' => now()]);
        $token = $user->createToken('auth')->plainTextToken;

        $frontendUrl = rtrim(env('FRONTEND_URL', 'http://localhost:4200'), '/');
        $redirectUrl = $frontendUrl . '/auth/callback?token=' . urlencode($token);

        return redirect($redirectUrl);
    }

    private function userResponse(User $user): array
    {
        return [
            'id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
            'display_name' => $user->display_name,
        ];
    }

    private function uniqueUsernameFromEmail(string $email): string
    {
        $base = Str::before($email, '@');
        $base = preg_replace('/[^a-zA-Z0-9_]/', '_', $base) ?: 'user';
        $username = Str::limit($base, 45, '');
        $suffix = 0;
        while (User::where('username', $username)->exists()) {
            $username = Str::limit($base, 42, '') . '_' . (++$suffix);
        }
        return $username;
    }
}
