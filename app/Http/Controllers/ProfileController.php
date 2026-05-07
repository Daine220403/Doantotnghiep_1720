<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Http\Requests\PasswordUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    /**
     * Show the user's profile form (frontend).
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        // dd($request->validated());
        $request->user()->update($request->validated());

        return back()->with('status', 'profile-updated');
    }

    /**
     * Update the user's password.
     */
    // PasswordUpdateRequest sẽ tự động xác thực mật khẩu hiện tại và mật khẩu mới
    // RedirectResponse sẽ trả về trang trước đó với thông báo trạng thái
    public function updatePassword(PasswordUpdateRequest $request): RedirectResponse
    
    {
        $user = $request->user();

        $user->update([
            'password' => Hash::make($request->validated('password')),
        ]);

        return back()->with('status', 'password-updated');
    }

    /**
     * Show admin profile page using admin layout.
     */
    public function editAdmin(Request $request): View
    {
        return view('admin.profile.edit', [
            'user' => $request->user(),
        ]);
    }
}
