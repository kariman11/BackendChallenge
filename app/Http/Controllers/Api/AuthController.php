<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\RecordLoginEvent;
use App\Models\User;
use App\Services\WebhookService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use PragmaRX\Google2FA\Google2FA;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\MagicLink;
use Illuminate\Support\Facades\Mail;
use App\Mail\MagicLoginLinkMail;


class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if (User::where('email', $validated['email'])->exists()) {
            return response()->json([
                'status' => false,
                'message' => 'Email already exists',
            ], 409);
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        event(new Registered($user));

        return response()->json([
            'status' => true,
            'message' => 'Registered successfully. Please verify your email before login.',
        ], 201);
    }



    public function loginOld(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'otp' => 'nullable|string'
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages(['email' => ['Invalid credentials.']]);
        }

        if (! $user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email not verified.'], 403);
        }

        // 2FA required?
        if ($user->two_factor_enabled) {

            // OTP missing?
            if (! $request->otp) {
                return response()->json(['message' => 'OTP required'], 422);
            }

            $google2fa = new Google2FA();

            // Check TOTP
            $otpValid = $google2fa->verifyKey($user->two_factor_secret, $request->otp);

            // Check backup codes
            $backupCodes = json_decode($user->two_factor_backup_codes, true);
            $isBackup = in_array($request->otp, $backupCodes);

            if (! $otpValid && ! $isBackup) {
                return response()->json(['message' => 'Invalid OTP or backup code'], 422);
            }

            // If backup code used → delete it
            if ($isBackup) {
                $backupCodes = array_diff($backupCodes, [$request->otp]);
                $user->two_factor_backup_codes = json_encode(array_values($backupCodes));
                $user->save();
            }
        }

        // Issue JWT token
        $token = Auth::guard('api')->login($user);

        return response()->json([
            'status' => true,
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::guard('api')->factory()->getTTL() * 60,
            'user' => $user,
        ]);
    }
    public function loginWith2FA(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $email = $request->email;
        $key = Str::lower($email) . '|' . $request->ip();  // unique per email + IP
        $maxAttempts = 5;
        $lockoutSeconds = 60;

        // 🔥 1) Check if user is locked out
        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {

            $seconds = RateLimiter::availableIn($key);

            return response()->json([
                'status' => false,
                'message' => 'Too many login attempts. Try again in ' . $seconds . ' seconds.',
                'retry_after_seconds' => $seconds,
            ], 429);
        }

        // Attempt to find user
        $user = User::where('email', $email)->first();

        // 🔥 2) Invalid credentials → hit throttle
        if (! $user || ! Hash::check($request->password, $user->password)) {

            RateLimiter::hit($key, $lockoutSeconds); // add attempt + lock for 60s

            return response()->json([
                'status' => false,
                'message' => 'Invalid credentials.'
            ], 401);
        }

        // 🔥 3) Email not verified
        if (! $user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email not verified.'
            ], 403);
        }

        // 🔥 4) Successful login → clear throttle attempts
        RateLimiter::clear($key);

        // 5) Generate JWT
        $token = Auth::guard('api')->login($user);

        return response()->json([
            'status' => true,
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::guard('api')->factory()->getTTL() * 60,
            'user' => $user,
        ]);
    }
//login with even login

    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="Login user",
     *     tags={"Auth"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", example="kariee@example.com"),
     *             @OA\Property(property="password", type="string", example="secret123")
     *         )
     *     ),
     *
     *     @OA\Response(response=200, description="Success"),
     *     @OA\Response(response=401, description="Invalid credentials"),
     *     @OA\Response(response=403, description="Email not verified")
     * )
     */

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $email = $request->email;
        $key = Str::lower($email) . '|' . $request->ip();  // unique per email + IP
        $maxAttempts = 5;
        $lockoutSeconds = 60;

        // 🔥 1) Check lockout
        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);

            return response()->json([
                'status'  => false,
                'message' => 'Too many login attempts. Try again in ' . $seconds . ' seconds.',
                'retry_after_seconds' => $seconds,
            ], 429);
        }

        // Attempt to find user
        $user = User::where('email', $email)->first();

        // 🔥 2) Invalid credentials → throttle
        if (! $user || ! Hash::check($request->password, $user->password)) {

            RateLimiter::hit($key, $lockoutSeconds);

            return response()->json([
                'status' => false,
                'message' => 'Invalid credentials.'
            ], 401);
        }

        // 🔥 3) Email not verified
        if (! $user->hasVerifiedEmail()) {
            return response()->json([
                'status' => false,
                'message' => 'Email not verified.'
            ], 403);
        }

        // 🔥 4) Success → clear throttle
        RateLimiter::clear($key);

        // ⭐ ADDED: Determine organization (optional header)
        $orgId = $request->header('X-Org-Id', null);

        // ⭐ ADDED: Update user login stats transactionally
        DB::transaction(function () use ($user) {
            $user->last_login_at = now();
            $user->login_count = $user->login_count + 1;
            $user->save();
        });

        // ⭐ ADDED: dispatch queued login event
        RecordLoginEvent::dispatch(
            $user->id,
            $orgId,
            $request->ip(),
            $request->userAgent()
        );

        // ⭐ JWT should be issued AFTER updating stats
        $token = Auth::guard('api')->login($user);

        WebhookService::send(
            $orgId,
            'user.login',
            [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'timestamp' => now()->toISOString(),
            ]
        );



        return response()->json([
            'status'     => true,
            'token'      => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::guard('api')->factory()->getTTL() * 60,
            'user'       => $user,
        ]);
    }




    public function verifyEmailWithoutWebhock(Request $request, $id, $hash)
    {
        // Check signed URL manually
        if (! $request->hasValidSignature()) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid or expired verification link.'
            ], 403);
        }

        // Find user
        $user = User::find($id);

        if (! $user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found.'
            ], 404);
        }

        // Check hash correctness
        if (! hash_equals(sha1($user->getEmailForVerification()), $hash)) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid verification hash.'
            ], 403);
        }

        // Already verified?
        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'status' => true,
                'message' => 'Email is already verified.'
            ]);
        }

        // Mark email verified
        $user->markEmailAsVerified();

        return response()->json([
            'status' => true,
            'message' => 'Email verified successfully.'
        ]);
    }

    public function verifyEmail(Request $request, $id, $hash)
    {
        if (! $request->hasValidSignature()) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid or expired verification link.'
            ], 403);
        }

        $user = User::find($id);

        if (! $user) {
            return response()->json(['status' => false, 'message' => 'User not found'], 404);
        }

        if (! hash_equals(sha1($user->getEmailForVerification()), $hash)) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid verification hash.'
            ], 403);
        }

        // already verified
        if ($user->hasVerifiedEmail()) {
            return response()->json(['status' => true, 'message' => 'Already verified']);
        }

        // mark verified
        $user->markEmailAsVerified();

        // 🔥 FIRE WEBHOOK
        WebhookService::dispatch('user.verified', [
            'user_id' => $user->id,
            'email'   => $user->email,
            'verified_at' => now()->toISOString(),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Email verified successfully.'
        ]);
    }


    public function resendVerification(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email already verified'], 200);
        }

        $user->sendEmailVerificationNotification();

        return response()->json([
            'status' => true,
            'message' => 'Verification link sent.'
        ]);
    }


    public function setup2FA(Request $request)
    {
        $user = $request->user();
        $google2fa = new \PragmaRX\Google2FA\Google2FA();

        // Generate secret
        $secret = $google2fa->generateSecretKey();

        // Generate otpauth URL (Google Authenticator Format)
        $otpauth = $google2fa->getQRCodeUrl(
            'Orthoplex App', // App name
            $user->email,
            $secret
        );

        // Generate QR code using qrserver.com
        $qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($otpauth);

        // Save secret temporarily so user can verify OTP
        $user->two_factor_secret = $secret;
        $user->save();

        return response()->json([
            'secret'      => $secret,
            'qr_code_url' => $qrCodeUrl, // works in browser
            'otpauth_url' => $otpauth    // for apps supporting RAW import
        ]);
    }



    public function enable2FA(Request $request)
    {
        $request->validate(['otp' => 'required']);

        $user = $request->user();
        $google2fa = new Google2FA();

        if (! $google2fa->verifyKey($user->two_factor_secret ?? '', $request->otp)) {
            return response()->json(['message' => 'Invalid OTP'], 422);
        }

        // Generate backup codes
        $backupCodes = [];
        for ($i = 0; $i < 8; $i++) {
            $backupCodes[] = strtoupper(bin2hex(random_bytes(4)));
        }

        $user->update([
            'two_factor_enabled' => true,
            'two_factor_backup_codes' => json_encode($backupCodes)
        ]);

        return response()->json([
            'message' => '2FA enabled',
            'backup_codes' => $backupCodes
        ]);
    }
    public function disable2FA(Request $request)
    {
        $user = $request->user();

        $user->update([
            'two_factor_enabled' => false,
            'two_factor_secret' => null,
            'two_factor_backup_codes' => null,
        ]);

        return response()->json(['message' => '2FA disabled']);
    }

    public function sendMagicLinkOld(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return response()->json(['message' => 'No account found for this email.'], 404);
        }

        // generate magic link token
        $token = Str::random(64);

        $magic = MagicLink::create([
            'user_id' => $user->id,
            'token' => $token,
            'expires_at' => Carbon::now()->addMinutes(15),
        ]);

        // generate URL
        $url = url('/api/magic/consume/' . $token);

        // send email (Mailpit)
        Mail::raw("Click to sign in: $url", function ($m) use ($user) {
            $m->to($user->email)->subject('Your magic login link');
        });

        return response()->json([
            'message' => 'Magic login link sent to email.',
            'expires_in_minutes' => 15
        ]);
    }

    public function sendMagicLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return response()->json(['message' => 'No account found for this email.'], 404);
        }

        $token = Str::random(64);

        MagicLink::create([
            'user_id' => $user->id,
            'token' => $token,
            'expires_at' => now()->addMinutes(15),
        ]);

        $url = url('/api/magic/consume/' . $token);

        Mail::to($user->email)->send(new MagicLoginLinkMail($url));

        return response()->json([
            'message' => 'Magic login link sent to email.',
            'expires_in_minutes' => 15
        ]);
    }

    public function consumeMagicLink($token)
    {
        $magic = MagicLink::where('token', $token)->first();

        if (! $magic) {
            return response()->json(['message' => 'Invalid link'], 404);
        }

        // expired?
        if ($magic->expires_at->isPast()) {
            return response()->json(['message' => 'Magic link has expired'], 410);
        }

        // already used?
        if ($magic->consumed_at) {
            return response()->json(['message' => 'Magic link already used'], 409);
        }

        // mark used
        $magic->update([
            'consumed_at' => Carbon::now(),
        ]);

        $user = $magic->user;

        // issue JWT token
        $token = Auth::guard('api')->login($user);

        return response()->json([
            'status' => true,
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::guard('api')->factory()->getTTL() * 60,
            'user' => $user
        ]);
    }


    public function me()     { return response()->json(Auth::guard('api')->user()); }
    public function logout() { Auth::guard('api')->logout(); return response()->json(['message' => 'Logged out']); }
}
