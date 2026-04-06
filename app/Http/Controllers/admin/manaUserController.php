<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\partners;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class manaUserController extends Controller
{
    protected array $allowedRoles = [
        'admin',
        'tour_manager',
        'staff_manager',
        'staff',
        'tour_guide',
        'partner',
    ];

    public function index(Request $request)
    {
        $role = $request->get('role');

        $query = User::query()->whereIn('role', $this->allowedRoles);
        if ($role && in_array($role, $this->allowedRoles, true)) {
            $query->where('role', $role);
        }

        $users = $query->orderByDesc('created_at')->paginate(15)->withQueryString();

        return view('admin.mana_user.index', [
            'users' => $users,
            'roles' => $this->allowedRoles,
            'currentRole' => $role,
        ]);
    }

    public function show(User $user)
    {
        abort_unless(in_array($user->role, $this->allowedRoles, true), 404);

        $user->load('partner', 'department');

        return view('admin.mana_user.show', [
            'user' => $user,
        ]);
    }

    public function create()
    {
        $partners = partners::orderBy('name')->get();

        $departments = Department::orderBy('name')->get();

        return view('admin.mana_user.create', [
            'roles' => $this->allowedRoles,
            'partners' => $partners,
            'departments' => $departments,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|in:' . implode(',', $this->allowedRoles),
            'department_id' => 'nullable|exists:departments,id',
            'partner_id' => 'nullable|exists:partners,id',
        ]);

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'role' => $validated['role'],
            'status' => 'active',
            'password' => Hash::make($validated['password']),
        ];

        if (!empty($validated['department_id'])) {
            $data['department_id'] = $validated['department_id'];
        }

        if ($validated['role'] === 'partner') {
            $data['partner_id'] = $validated['partner_id'] ?? null;
        }

        $user = User::create($data);

        return redirect()->route('admin.mana-user.show', $user)->with('success', 'Tạo tài khoản mới thành công.');
    }

    public function toggleStatus(User $user)
    {
        abort_unless(in_array($user->role, $this->allowedRoles, true), 404);

        $user->status = $user->status === 'active' ? 'inactive' : 'active';
        $user->save();

        return redirect()->back()->with('success', 'Cập nhật trạng thái tài khoản thành công.');
    }
}
