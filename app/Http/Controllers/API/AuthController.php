<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\RateLimiter;

class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/register",
     *     tags={"Authentication"},
     *     summary="Register a new user",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="username", type="string", example="john_doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="password", type="string", example="password"),
     *             @OA\Property(property="password_confirmation", type="string", example="password")
     *         )
     *     ),
     *     @OA\Response(response=201, description="User created successfully"),
     *     @OA\Response(response=400, description="Invalid data"),
     *     @OA\Response(response=500, description="Internal server error")
     * )
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'string|max:255',
            'email' => 'required|email|unique:users,email|max:255',
            'password' => 'required|string|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'message' => __('messages.data_invalid'),
                'errors' => $validator->errors(),
            ], 400);
        }

        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        $defaultRole = Role::firstOrCreate(['name' => 'user']);
        $user->assignRole($defaultRole);
        $this->sendVerificationEmail($user);
        return response()->json([
            'status' => 201,
            'message' =>  __('messages.request_successful'),
            'data' => ['user' => $user],
        ], 201);
    }

    /**
     * @OA\Post(
     *     path="/api/login",
     *     tags={"Authentication"},
     *     summary="Login a user",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="password", type="string", example="password")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Login successful"),
     *     @OA\Response(response=401, description="Invalid credentials")
     * )
     */
    public function login(Request $request)
    {
        $request->validate([
            "email" => "required|email|max:255",
            "password" => "required",
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'status' => 401,
                'message' => __('messages.invalid_credentials'),
            ], 401);
        }

        $user = Auth::user();

        $token = $user->createToken('token-name', $user->getRoleNames()->toArray())->plainTextToken;

        return response()->json([
            'status' => 200,
            'message' => __('messages.login_successful'),
            'data' => ['token' => $token, 'user' => $user],
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/logout",
     *     tags={"Authentication"},
     *     summary="Logout the authenticated user",
     *     @OA\Response(response=200, description="Logout successful"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'status' => 200,
            'message' => __('messages.logout_successful'),
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/password/email",
     *     tags={"Authentication"},
     *     summary="Send password reset link",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Reset link sent"),
     *     @OA\Response(response=400, description="Unable to send reset link")
     * )
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $response = Password::sendResetLink($request->only('email'), function ($user, $token) {
            $url = env('FRONTEND_URL') . '/reset-password?token=' . $token . '&email=' . urlencode($user->email);
            Mail::to($user->email)->send(new \App\Mail\ResetPasswordMail($url));
        });

        return $response == Password::RESET_LINK_SENT
            ? response()->json(['status' => __('messages.reset_link_sent')], 200)
            : response()->json(['email' => __('messages.unable_to_send_reset_link')], 400);
    }



    private function sendVerificationEmail($user)
    {

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        Mail::to($user->email)->send(new \App\Mail\VerifyEmail($verificationUrl));
    }

    public function verifyEmail(Request $request, $id, $hash)
    {
        
        $rules = [
            'id' => 'required|exists:users,id',
            'hash' => 'required|string',
        ];
    
        Validator::make(['id' => $id, 'hash' => $hash], $rules);
    
        $user = User::find($id);
    
        if (!hash_equals(sha1($user->email), $hash)) {
            Log::warning('Invalid verification link for user ID: ' . $id);
            return redirect()->away(env('FRONTEND_URL'))->withErrors([
                'status' => 400,
                'errors' => ['email' => __('messages.email_verification_link_invalid')]
            ]);
        }
    
        $expires = $request->query('expires');
        if (now()->timestamp > $expires) {
            Log::warning('Verification URL has expired for user ID: ' . $id);
            return redirect()->away(env('FRONTEND_URL'))->withErrors([
                'status' => 400,
                'errors' => ['email' => __('messages.verification_link_expired')]
            ]);
        }
    
        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
            Log::info('Email verified for user ID: ' . $user->id);
        } else {
            Log::info('User already verified, ID: ' . $user->id);
        }
    
        return redirect()->away(env('FRONTEND_URL'));
    }
    



    public function resendVerficationEmail(Request $request)
    {

        
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);
        

        $user = User::where('email', $request->email)->first();

        $executed = RateLimiter::attempt(
            'send-message:'.$user->id,
            $perMinute = 3,
            function () use ($user) {
                $this->sendVerificationEmail($user);
            }
        );

         
        if (! $executed) {
          return response()->json([
            'status' => 400,
            'message' => __('messages.too_may_request')
          ], 400);
        }


        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'status' => 400,
                'message' => __('messages.email_alread_verified')
            ], 400);
        }
        
        
        return response()->json([
            'status' => 200,
            'message' => __('messages.verification_link_sent'),
        ], 200);
    }


    /**
     * @OA\Post(
     *     path="/api/password/reset",
     *     tags={"Authentication"},
     *     summary="Reset user password",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="password", type="string", example="password"),
     *             @OA\Property(property="password_confirmation", type="string", example="password"),
     *             @OA\Property(property="token", type="string", example="reset_token")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Password reset successful"),
     *     @OA\Response(response=400, description="Unable to reset password")
     * )
     */
    public function resetPassword(Request $request)
    {
        Log::info($request->all());
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|confirmed',
            'token' => 'required|string',
        ]);

        $response = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->password = bcrypt($password);
                $user->save();
                event(new PasswordReset($user));
            }
        );

        return $response == Password::PASSWORD_RESET
            ? response()->json(['status' => __('messages.password_reset_successful')], 200)
            : response()->json(['email' => __('messages.unable_to_reset_password')], 400);
    }

    /**
     * @OA\Post(
     *     path="/api/authorization",
     *     tags={"Authentication"},
     *     summary="Check user authorization for roles",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="roles", type="array", @OA\Items(type="string"))
     *         )
     *     ),
     *     @OA\Response(response=200, description="Authorization check successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="authorized", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function autorisation(Request $request)
    {
        $roles = $request->get('roles', []);
        $hasPermission = false;

        foreach ($roles as $role) {
            if ($request->user()->tokenCan($role)) {
                $hasPermission = true;
                break; // No need to check further if one role is valid
            }
        }

        return response()->json([
            "data" => ['authorized' => $hasPermission]
        ]);
    }
}
