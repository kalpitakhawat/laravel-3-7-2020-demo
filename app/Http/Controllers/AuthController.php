<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Response;
use Auth;
use Carbon\Carbon;

class AuthController extends Controller
{

    /**
     * create user
     *
     * @param  mixed $request
     * @return mixed $response
     */
    public function create(Request $request)
    {
        // Request Validation
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|confirmed',
            'phone' =>  'required|digits:10',
            'profile_pic' => 'required|image',
            'type' => 'required|in:1,2',
        ]);
        if ($validator->fails()) {
            return Response::json([
                "message" => "Invalid Parameters",
                "errors" => $validator->messages()
            ], 422);
        };

        // User Object Creation
        $userObject = $request->all();
        $userObject['password'] = bcrypt($request->password);

        // File Save to folder
        $file = $request->file('profile_pic');
        $extension = $file->getClientOriginalExtension();
        $filename = time() . '.' . $extension;
        $file->move(public_path() . '/uploads/', $filename);

        $userObject['profile_pic'] =  '/uploads/' . $filename;
        unset($userObject['password_confirmation']);

        // Save User Object to DB
        $user = new User($userObject);
        $user->save();
        return Response::json([
            'message' => 'Successfully created user!'
        ], 200);
    }


    /**
     * login
     *
     * @param  mixed $request
     * @return  mixed $response
     */
    public function login(Request $request)
    {
        // Request Validation
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);
        if ($validator->fails()) {
            return Response::json([
                "message" => "Invalid Parameters",
                "errors" => $validator->messages()
            ], 422);
        };

        // Login Attempt
        $credentials = request(['email', 'password']);
        if (!Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        // Issue Token
        $user = Auth::user();
        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->token;

        $token->save();
        return response()->json([
            'access_token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => Carbon::parse(
                $tokenResult->token->expires_at
            )->toDateTimeString()
        ]);
    }

    /**
     * Logout user (Revoke the token)
     *
     * @return [string] message
     */
    public function logout(Request $request)
    {
        Auth::user()->token()->revoke();
        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }
}
