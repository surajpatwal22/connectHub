<?php

namespace App\Http\Controllers;

use App\Models\Otp;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;


class UserController extends Controller
{
    //
    public function signup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors(),
                'status' => 400,
                'success' => false
            ]);
        } else {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'is_admin' => 0
            ]);
            if ($user) {
                return response()->json([
                    'message' => 'created successfully',
                    'status' => 201,
                    'success' => true
                ]);
            } else {
                return response()->json([
                    'message' => 'something went wrong',
                    'status' => 400,
                    'success' => false
                ]);
            }
        }
    }

    public function signin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:6',
            'device_id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors(),
                'status' => 400,
                'success' => false
            ]);
        } else {
            $user = User::where('email', $request->email)->first();
            if ($user) {
                $check = Auth::attempt(['email' => $request->email, 'password' => $request->password]);
                if ($check == 1) {
                    $token = $user->createToken('Personal Access Token')->plainTextToken;
                    $user->device_id = $request->device_id;
                    $user->save();
                    return response()->json([

                        'message' => 'Login successfull',
                        'token' => $token,
                        'status' => 200,
                        'success' => true,
                        'user' => $user
                    ], 200);
                } else {
                    return response()->json([
                        'message' => 'credentials not matched',
                        'status' => 400,
                        'success' => false
                    ], 400);
                }
            } else {
                return response()->json([
                    'message' => 'Email Not found',
                    'status' => 404,
                    'success' => false
                ]);
            }
        }
    }

    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors(),
                'status' => 400,
                'success' => false
            ]);
        } else {
            $user = User::where('email', $request->email)->first();
            if ($user) {
                $otp = rand(1000, 9999);

                Otp::where('user_id',$user->id)->delete();

                $create = Otp::create([
                    'otp' => $otp,
                    'user_id' => $user->id,
                    'status' => 1
                ]);

                if ($create) {
                    try {
                        $sendmail = Mail::send('mail.verificationMail', ['otp' => $otp, 'name' => $user->name], function ($m) use ($user) {
                            $m->to($user->email, $user->name)->subject("Verification otp");
                        });
                        return response()->json([
                            'message' => 'Otp sent to mail ,valid upto 5 minutes',
                            'status' => 200,
                            'success' => true
                        ]);
                    } catch (Exception $e) {
                        return $e;
                    }
                } else {
                    return response()->json([
                        'message' => 'something went wrong',
                        'status' => 400,
                        'success' => false
                    ]);
                }
            } else {
                return response()->json([
                    'message' => 'Email Not found',
                    'status' => 404,
                    'success' => false
                ]);
            }
        }
    }

    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'otp' => 'required',
            'device_id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors(),
                'status' => 400,
                'success' => false
            ]);
        } else {
            $user = User::where('email', $request->email)->first();
            if ($user) {

                $expiryTime = Carbon::now()->subMinutes(5);
                Otp::where('created_at', '<=', $expiryTime)->delete(); 

                $check = Otp::where(['user_id' => $user->id, 'otp' => $request->otp])->first();
                if ($check) {
                    $token = $user->createToken('Personal Access Token')->plainTextToken;
                    $user->device_id = $request->device_id;
                    $user->save();

                    return response()->json([

                        'message' => 'otp verified',
                        'token' => $token,
                        'status' => 200,
                        'success' => true
                    ]);
                } else {
                    return response()->json([
                        'message' => 'credentials not matched or otp expired',
                        'status' => 400,
                        'success' => false
                    ]);
                }
            } else {
                return response()->json([
                    'message' => 'Email Not found',
                    'status' => 404,
                    'success' => false
                ]);
            }
        }
    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required',
            'confirm_password' => 'required|same:password'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors(),
                'status' => 400,
                'success' => false
            ]);
        } else {
            $user = User::find(Auth::user()->id);
            if ($user) {
                $user->password = Hash::make($request->password);
                $user->save();
                return response()->json([
                    'message' => 'password updated successfully',
                    'status' => 200,
                    'success' => true
                ]);
            } else {
                return response()->json([
                    'message' => 'User Token found',
                    'status' => 404,
                    'success' => false
                ]);
            }
        }
    }

    public function getProfile()
    {
        return response()->json([
            'user' => Auth::user(),
            'status' => 200,
            'success' => true
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = User::find(Auth::user()->id);
        if ($user) {
            $validator = Validator::make($request->all(), [
                'email' => 'email',
                'contact' => 'min:10|max:10'
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'error' => $validator->errors(),
                    'status' => 400,
                    'success' => false
                ]);
            } else {
                if ($request->file) {
                    try {
                        $file = $request->file('file');
                        $imageName = time() . '.' . $file->extension();
                        $imagePath = public_path() . '/user_profile';

                        $file->move($imagePath, $imageName);

                        $user->profile = '/user_profile/' . $imageName;
                    } catch (Exception $e) {
                        return $e;
                    }
                }

                if ($request->email) {
                    $user->email = $request->email;
                }
                if ($request->contact) {
                    $user->contact = $request->contact;
                }
                if ($request->name) {
                    $user->name = $request->name;
                }
                if ($request->bio) {
                    $user->bio = $request->bio;
                }
                $user->save();

                return response()->json([
                    'message' => 'updated successfully',
                    'status' => 200,
                    'success' => true
                ]);
            }
        } else {
            return response()->json([
                'message' => 'user not found',
                'status' => 404,
                'success' => false
            ]);
        }
    }

    public function logout()
    {
        $user = Auth::user();
        $user->tokens->each(function ($token, $key) {
            $token->delete();
        });
        return response()->json([
            'message' => 'Successfully logged out',
            'status' => 200,
            'success' => true
        ]);
    }

}