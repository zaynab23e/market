<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

//_______________________________________________________________________________________________________
class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:admins',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $admin = Admin::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $admin->createToken('Admin Token')->plainTextToken;

        return response()->json([
            'message' => 'تم تسجيل المدير بنجاح!',
            'admin' => $admin,
            'token' => $token
        ], 201);
    }
//_______________________________________________________________________________________________________
public function login(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'password' => 'required|string|min:6',
    ]);
    
    $admin = Admin::where('email', $request->email)->first();

    if (!$admin || !Hash::check($request->password, $admin->password)) {
        return response()->json(['message' => 'بيانات الاعتماد غير صحيحة'], 401);
    }
    
    $token = $admin->createToken('Admin Token')->plainTextToken;
    
    return response()->json([
        'admin' => $admin,
        'token' => $token
    ]);
}

//_______________________________________________________________________________________________________
public function logout()
{
    if (!Auth::guard('admin')->check()) {
        return response()->json(['message' => 'لم يتم تسجيل الدخول'], 401);
    }
    
    Auth::guard('admin')->user()->tokens()->delete();
    
    return response()->json([
        'message' => 'تم تسجيل الخروج بنجاح'
    ]);
}
//_______________________________________________________________________________________________________

}
