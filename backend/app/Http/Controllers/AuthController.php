<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    private $secretKey = "qQKPjndxljuYQi/POiXJa8O19nVO/vTf/DpXO541g=qQKPjndxljuYQi/POiXJa8O19nVO/vTf/DpXO541g=";

    public function register(Request $request)
    {
        $fields = $request->all();

        $errors = Validator::make($fields, [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'password' => 'required',
        ]);

        if ($errors->fails()) {
            return response($errors->errors()->all(), 422);
        }

        $user = User::create([
            'name' => $fields['name'],
            'email' => $fields['email'],
            'password' => bcrypt($fields['password']),
        ]);
        return response([
            'user' => $user,
            'message' => 'User registered successfully',
        ], 200);
    }

    public function login(Request $request)
    {
        $fields = $request->all();
        $errors = Validator::make($fields, [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($errors->fails()) {
            return response($errors->errors()->all(), 422);
        }

        $user = User::where('email', $fields['email'])->first();
        if (!$user || !password_verify($fields['password'], $user->password)) {
            return response(['message' => 'Invalid credentials'], 401);
        }
        $token = $user->createToken($this->secretKey)->plainTextToken;

        return response([
            'user' => $user,
            'token' => $token,
            'message' => 'User logged in successfully',
        ], 201);
    }

    public function logoutUser(Request $request)
    {
        // DB::table('personal_access_tokens')
        //     ->where('tokenable_id', $request->userId)
        //     ->delete();

        $access_token = $request->bearerToken();
        $token = PersonalAccessToken::findToken($access_token);

        if (!$token) {
            return response()->json(['message' => 'Invalid token'], 401);
        }

        $token->delete();

        return response(['message' => 'user logged out !'], 200);
    }

    public function userIsLoggedIn(Request $request)
    {
        return response(['success' => true], 200);
    }
}
