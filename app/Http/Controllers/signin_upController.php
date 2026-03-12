<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

use Flasher\Laravel\Facade\Flasher;

class signin_upController extends Controller
{
    public function signin()
    {
        return view('signin');
    }
    public function signinStore(Request $request)
    {
        // dd($request->all());
        // Validate dữ liệu
        $request->validate(
            [
                'email' => 'required|email',
                'password' => 'required|string|min:6',
            ],
            [
                'email.required' => 'Vui lòng nhập email.',
                'email.email' => 'Định dạng email không hợp lệ.',
                'password.required' => 'Vui lòng nhập mật khẩu.',
                'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự.',
            ]
        );
        // Lấy thông tin login
        $credentials = [
            'email' => $request->input('email'),
            'password' => $request->input('password'),
            'role' => 'customer', // Chỉ cho phép đăng nhập với vai trò 'customer'
            'status' => 'active' // Chỉ cho phép đăng nhập nếu tài khoản không bị khóa
        ];
        // dd($credentials);
        Auth::attempt($credentials);
        if (Auth::check()) {
            // Đăng nhập thành công
            
            return redirect()->route('home')->with('success', 'Đăng nhập thành công!');
        } else {
            // Đăng nhập thất bại
            return back()->withErrors(['email' => 'Sai email hoặc mật khẩu.']);
        }
    }
    public function logout(Request $request)
    {
        $roles = Auth::user()->role; 
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        if($roles === 'customer')
        {
            return redirect()->route('home')->with('success', 'Đăng xuất thành công!');
        }
        else
        {
            return redirect()->route('login')->with('success', 'Đăng xuất thành công!');
        }
        
    }

    public function signup()
    {
        return view('signup');
    }

    public function signupStore(Request $request)
    {
        // Validate dữ liệu
        $request->validate([
            'name' => 'required|string|max:255|regex:/^[^\d]+$/',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'name.required' => 'Vui lòng nhập họ và tên.',
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Định dạng email không hợp lệ.',
            'email.unique' => 'Email đã được sử dụng.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự.',
            'password.confirmed' => 'Mật khẩu xác nhận không khớp.',
            'name.regex' => 'Họ và tên không được chứa số.',
        ]);

        // Tạo tài khoản người dùng mới
        $user = new User();
        $user->name = $request->input('name');
        $user->email = $request->input('email');
        $user->password = Hash::make($request->input('password'));
        $user->role = 'customer'; // Mặc định vai trò là 'customer'
        $user->status = 'active'; // Mặc định trạng thái là 'active'
        $user->save();
        return redirect()->route('signin')->with('success', 'Đăng ký tài khoản thành công! Vui lòng đăng nhập để tiếp tục.');
    }
}
