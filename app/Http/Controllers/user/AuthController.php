<?php

namespace App\Http\Controllers\user;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Requests\User\login;
use App\Http\Requests\User\store;
use Hash;
use Auth;
use DB;
use Mail;

class AuthController extends Controller
{
//_________________________________________________________________________________
    public function register(store $request)
    {
        $validatedData = $request->validated();
        $validatedData['password'] = Hash::make($validatedData['password']);
        $user = User::create($validatedData);
        return response()->json([
            'user' => $user,
            'message' => 'تم التسجيل بنجاح',
        ], 201);
    }
//______________________________________________________________________________________

    public function login(login $request)
    {
        $validatedData = $request->validated();
        $user = User::where('email', $request->input('email'))->first();
        if (!$user || !Hash::check($request->input('password'), $user->password)) {
            return response()->json(['message' => 'بيانات الاعتماد غير صحيحة'], 401);
        }
        $token = $user->createToken('Api token of ' . $user->name)->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token
        ]);
    }
//___________________________________________________________________________________________________
public function logout()
{
    if (!Auth::check()) {
        return response()->json(['message' => 'لم يتم تسجيل الدخول'], 401);
    }

    Auth::user()->currentAccessToken()->delete();
    return response()->json([
        'message' => 'تم تسجيل الخروج بنجاح'
    ]);
}
//_________________________________________________________________________________________________
    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $user = User::where('email', $request->email)->first();
    
        if (!$user) {
            return response()->json(['email' => 'المستخدم غير موجود'], 404);
        }
    
        $code = mt_rand(100000, 999999);
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => $code,
                'created_at' => now()
            ]
        );
    
        Mail::raw("رمز إعادة تعيين كلمة المرور الخاص بك هو: $code", function ($message) use ($user) {
            $message->to($user->email)
                ->subject('رمز إعادة تعيين كلمة المرور');
        });
        return response()->json(['message' => 'تم إرسال رمز إعادة تعيين كلمة المرور إلى بريدك الإلكتروني']);
    }
//_______________________________________________________________________________________________________________
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|numeric',
            'password' => 'required|string', //confirmed
        ]);
    
        $resetEntry = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->where('token', $request->code)
            ->first();
    
        if (!$resetEntry) {
            return response()->json([
                'message' => 'رمز غير صالح',
            ]);
        }
        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();
    
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();
        return response()->json([
            'message' => 'تم إعادة تعيين كلمة المرور بنجاح',
        ]);
    }
//_________________________________________________________________________________________________  

}
