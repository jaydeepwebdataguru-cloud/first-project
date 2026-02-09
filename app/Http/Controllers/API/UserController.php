<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(){
        try{
            $users = User::all();
            return response()->json([
                'status' => true,
                'message' => 'Users fetched successfully',
                'data' => $users
            ], 200);

        }catch(\Exception $e){
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong: ' . $e->getMessage()
            ], 500);
        }

    }

    public function show($id){
        try{
            $user = User::findorFail($id);
            return response()->json([
                'status'=> true,
                'message' => 'User fetched successfully',
                'data' => $user
            ]);

        }catch(\Exception $e){
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request){
        try{
            $validator = Validator::make($request->all(),[
                'name' => 'required|string|max:15',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:6',
            ]);

            if($validator->fails()){
                return response()->json([
                    'status' => false,
                    'message' => 'Validation errors',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
            ]);

            return response()->json([
                'status' => true,
                'message' => 'User Created Successfully',
                'data' => $user
            ], 201);

        }catch(\Exception $e){
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id){
        try{
            $user = User::findOrFail($id);
            if(!$user){
                return response()->json([
                    'status' => false,
                    'message' => 'User not found'
                ], 404);
            }

            $validator = Validator::make($request->all(),[
                'name' => 'required|string|max:15',
                'email' => 'required|email|unique:users,email,'.($id),
            ]);

            if($validator->fails()){
                return response()->json([
                    'status' => false,
                    'message' => 'Validation errors',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user->update([
                'name' => $request->name,
                'email' => $request->email,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'User Updated Successfully',
                'data' => $user
            ], 200);
            
        }catch(\Exception $e){
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong: ' . $e->getMessage()
            ], 500);
        }
    }

    public function delete($id){
        try{
            User::findOrFail($id)->delete();
            return response()->json([
                'status' => true,
                'message' => 'User Deleted Successfully',
            ], 200);

        }catch(\Exception $e){
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong: ' . $e->getMessage()
            ], 500);
        }
    }

    public function login(Request $request){
        try{
            $validator = Validator::make($request->all(),[
                'email' => 'required|email',
                'password' => 'required|string|min:6',
            ]);

            if($validator->fails()){
                return response()->json([
                    'status' => false,
                    'message' => 'Validation errors',
                    'errors' => $validator->errors()
                ], 422);
            }

            // $user = User::where('email', $request->email)->first();
            // if(!$user || !Hash::check($request->password, $user->password)){
            //     return response()->json([
            //         'status' => false,
            //         'message' => 'Invalid credentials'
            //     ], 401);
            // }
            
            if(!Auth::attempt($request->only('email', 'password'))){
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid credentials'
                ], 401);
            }

            $user = Auth::user();

            // force delete existing tokens
            $user->tokens()->delete();
            $token = $user->createToken('auth_token')->plainTextToken;
            
            return response()->json([
                'status' => true,
                'token' => $token,
                'type' => 'Bearer',
            ], 200);

        }catch(\Exception $e){
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong: ' . $e->getMessage()
            ], 500);
        }
    }

    public function logout(Request $request){
        try{
            //only delete current token (multiple device support)
            $request->user()->currentAccessToken()->delete();
            return response()->json([
                'status' => true,
                'message' => 'Logged out successfully'
            ], 200);

        }catch(\Exception $e){
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong: ' . $e->getMessage()
            ], 500);
        }
    }

    public function me(Request $request){
        try{
            return response()->json([
                'status' => true,
                'data' => $request->user()
            ], 200);

        }catch(\Exception $e){
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong: ' . $e->getMessage()
            ], 500);
        }
    }

    public function register(Request $request){
        try{
            $validator = Validator::make($request->all(),[
                'name' => 'required|string|max:15',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:6',
            ]);

            if($validator->fails()){
                return response()->json([
                    'status' => false,
                    'message' => 'Validation errors',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;
            return response()->json([
                'status' => true,
                'user' => $user,
                'token' => $token,
                'type' => 'Bearer'
            ], 201);

        }catch(\Exception $e){
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong: ' . $e->getMessage()
            ], 500);
        }
    }
}
