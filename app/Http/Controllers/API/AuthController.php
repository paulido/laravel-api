<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    protected $rules = [
        'username' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email|max:255',
        'password' => 'required|string|confirmed',
    ];

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), $this->rules);
        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'message' => 'Data invalid',
                'errors' => $validator->errors(),
            ], 400);
        }

        try {
            $user = User::create([
                'username' => $request->username,
                'email' => $request->email,
                'password' => bcrypt($request->password),
            ]);

            $defaultRole = Role::firstOrCreate(['name' => 'user']);
            $user->assignRole($defaultRole); // Assign role as a string if it's a string role name.

            return response()->json([
                'status' => 201,
                'message' => 'Request successful',
                'data' => ['user' => $user],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'An error occurred while creating the user.',
                'errors' => [$e->getMessage()],
            ], 500);
        }
    }

    public function login(Request $request)
    {
        $request->validate([
            "email" => "required|email|max:255",
            "password" => "required",
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'status' => 401,
                'message' => 'Invalid credentials',
                'errors' => ['The provided email or password is incorrect.']
            ], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('token-name')->plainTextToken; // No need to pass roles here
        return response()->json([
            'status' => 200,
            'message' => 'Successfully logged in',
            'data' => ['token' => $token],
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'status' => 200,
            'message' => 'Successfully logged out',
        ], 200);
    }

    public function sendResetLinkEmail(Request $request)
{
    $request->validate(['email' => 'required|email']);

    $response = Password::sendResetLink($request->only('email'), function ($user, $token) {
        // Generate the URL for the Angular app
        $url = env('APP_URL') . '/reset-password?token=' . $token . '&email=' . urlencode($user->email);
        
        // Send the email (use Laravel's built-in notification or customize it)
        // For example:
        Mail::to($user->email)->send(new \App\Mail\ResetPasswordMail($url));
    });

    return $response == Password::RESET_LINK_SENT
        ? response()->json(['status' => 'Reset link sent.'], 200)
        : response()->json(['email' => 'Unable to send reset link.'], 400);
}

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|confirmed',
            'token' => 'required',
        ]);

        $response = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->password = Hash::make($password);
                $user->save();
                event(new PasswordReset($user));
            }
        );

        return $response == Password::PASSWORD_RESET
            ? response()->json(['status' => 'Password reset successful.'], 200)
            : response()->json(['email' => 'Unable to reset password.'], 400);
    }
}
