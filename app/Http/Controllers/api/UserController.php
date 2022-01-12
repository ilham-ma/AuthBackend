<?php

namespace App\Http\Controllers\api;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function register(Request $request){
        try{
            $request->validate([
                "name" => ["required", "max:255"],
                "phone_number" => ["required"],
                "email" => ["required", "email", "unique:users"],
                "password" => ["required","min:5"]
            ]);

            $createUser = User::create([
                "name" => $request->name,
                "phone_number" => $request->phone_number,
                "email" => $request->email,
                "password" => Hash::make($request->password)
            ]);

            return response()->json([
                "message" => "Registration success",
                "status" => $createUser
            ],200);
        }catch (Exception $error){
            return response()->json([
                "message" => "Registration Failed",
                "error" => $error
            ], 500);
        }
    }

    public function login(Request $request){
        try {
            $request->validate([
                "email" => ["required", "email"],
                "password" => ["required"]
            ]);

            $credentials = request(["email", "password"]);
            if(!Auth::attempt($credentials)){
                return response()->json([
                    "message" => "Authentication Failed",
                    "status" => false
                ], 500);
            }

            
            $data = User::where('email', $request->email)->first();
            $token = $data->createToken('authToken')->plainTextToken;
            return response()->json([
                "Token" => "Bearer ".$token,
                "Data" => $data,
                "status" => true
            ],200);
        } catch (Exception $error) {
            return response()->json([
                "message" => "Authentication Failed",
                "error" => $error
            ], 500);
        }
    }

    public function logout(Request $request){
        try {
            $logout = $request->user()->currentAccessToken()->delete();
            
            return response()->json([
                "message" => "Token revoked",
                "status" => $logout
            ],200);
        } catch (Exception $error) {
            return response()->json([
                "message" => "Token Failed to revoked",
                "error" => $error
            ], 500);
        }
    }

    public function getProfile(Request $request){
        return response()->json([
            "data" => $request->user()
        ],200);
    }

    public function updatePassword(Request $request){
        try {
            $request->validate([
                "old_password" => ["required"],
                "new_password" => ["required"],
                "confirm_password" => ["required"]
            ]);

            $id = $request->user()->id;
            $user = User::find($id);
            if(!Hash::check($request->old_password, $user->password)){
                return response()->json([
                    "message" => "password is not correct",
                    "status" => false
                ], 500);
            }

            if($request->new_password != $request->confirm_password){
                return response()->json([
                    "message" => "password doesnt match",
                    "status" => false
                ], 500);
            }

            $updatePassword = $user->update([
                "password" => Hash::make($request->new_password)
            ]);

            if($updatePassword){
                $request->user()->currentAccessToken()->delete();
                return response()->json([
                    "message" => "Password successfully to update",
                    "status" => $updatePassword
                ],200);
            }

        } catch (Exception $error) {
            return response()->json([
                "message" => "Password failed to update",
                "error" => $error
            ], 500);
        }
    }

    public function updateProfile(Request $request){
        try {
            $request->validate([
                "name" => ["required", "max:255"],
                "phone_number" => ["required", "numeric"],
                "email" => ["required", "email",],
            ]);

            $user_id = $request->user()->id;
            $updateUser = User::find($user_id)->update([
                "name" => $request->name,
                "phone_number" => $request->phone_number,
                "email" => $request->email,
            ]);

            return response()->json([
                "message" => "Profile successfully to update",
                "status" => $updateUser
            ],200);

        } catch (Exception $error) {
            return response()->json([
                "message" => "Profile failed to update",
                "error" => $error
            ], 500);
        }
    }

    public function updatePhoto(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'file' => ['required', 'image', 'max:4096']
            ]);
            
            if($validator->fails()){
                return response()->json([
                    "message" => "Update photo fails",
                    "status" => false
                ], 500);
            }
            
            if($request->file('image')){
                $id = $request->user()->id;
                $updatePhoto = User::find($id)->update([
                    "profile_photo_url" => $request->file('image')->store('avatars')
                ]);
                
                return response()->json([
                    "message" => "Photo profile successfully to update",
                    "status" => $updatePhoto
                ],200);
            }
        } catch(Exception $error){
            return response()->json([
                "message" => "Update photo fails",
                "error" => $error
            ], 500);
        }
    }
}
