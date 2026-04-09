@extends('admin.layout.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0 text-gray-800">Thêm tài khoản mới</h1>
            <a href="{{ route('admin.mana-user.index') }}" class="btn btn-sm btn-secondary">Quay lại danh sách</a>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Thông tin tài khoản</h6>
            </div>
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('admin.mana-user.store') }}" method="POST">
                    @csrf
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Họ tên</label>
                            <input type="text" name="name" value="{{ old('name') }}" class="form-control" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Email đăng nhập</label>
                            <input type="email" name="email" value="{{ old('email') }}" class="form-control" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label>Số điện thoại</label>
                            <input type="text" name="phone" value="{{ old('phone') }}" class="form-control">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Phòng ban (nếu là nhân viên)</label>
                            <select name="department_id" id="department-select" class="form-control">
                                <option value="">-- Chọn phòng ban --</option>
                                @foreach ($departments as $department)
                                    @php
                                        $deptRolesMap = [
                                            'Phòng Quản trị hệ thống' => 'admin',
                                            'Phòng Điều hành tour' => 'tour_manager,tour_guide',
                                            'Phòng Chăm sóc khách hàng' => 'staff,staff_manager',
                                            'Phòng Đối tác dịch vụ' => 'partner',
                                        ];
                                        $mappedRoles = $deptRolesMap[$department->name] ?? '';
                                    @endphp
                                    <option value="{{ $department->id }}"
                                            data-roles="{{ $mappedRoles }}"
                                            {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                        {{ $department->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Vai trò</label>
                            <select name="role" id="user-role" class="form-control" required>
                                <option value="">-- Chọn vai trò --</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role }}" {{ old('role') === $role ? 'selected' : '' }}>
                                        {{ $role }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div id="partner-select-wrapper" class="form-group d-none">
                        <label>Đối tác liên kết (cho tài khoản đối tác)</label>
                        <select name="partner_id" class="form-control">
                            <option value="">-- Chọn đối tác --</option>
                            @foreach ($partners as $partner)
                                <option value="{{ $partner->id }}" {{ old('partner_id') == $partner->id ? 'selected' : '' }}>
                                    {{ $partner->name }} ({{ $partner->type }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Mật khẩu</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Nhập lại mật khẩu</label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Lưu tài khoản</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var roleSelect = document.getElementById('user-role');
            var partnerWrapper = document.getElementById('partner-select-wrapper');
            var departmentSelect = document.getElementById('department-select');

            var allRoleOptions = roleSelect
                ? Array.prototype.map.call(roleSelect.options, function (opt) {
                    return opt.cloneNode(true);
                })
                : [];

            function togglePartner() {
                if (roleSelect && partnerWrapper) {
                    if (roleSelect.value === 'partner') {
                        partnerWrapper.classList.remove('d-none');
                    } else {
                        partnerWrapper.classList.add('d-none');
                    }
                }
            }

            function applyRoleFilterByDepartment() {
                if (!departmentSelect || !roleSelect || !allRoleOptions.length) {
                    togglePartner();
                    return;
                }

                var selectedOption = departmentSelect.options[departmentSelect.selectedIndex];
                var rolesStr = selectedOption ? (selectedOption.getAttribute('data-roles') || '') : '';
                var allowedRoles = rolesStr ? rolesStr.split(',') : [];

                var placeholder = allRoleOptions[0].cloneNode(true);
                roleSelect.innerHTML = '';
                roleSelect.appendChild(placeholder);

                var toAppend;
                if (allowedRoles.length > 0) {
                    toAppend = allRoleOptions.filter(function (opt, index) {
                        if (index === 0) return false; // bỏ placeholder
                        return allowedRoles.indexOf(opt.value) !== -1;
                    });
                } else {
                    // Không chọn phòng ban hoặc phòng không map: hiển thị tất cả vai trò còn lại
                    toAppend = allRoleOptions.slice(1);
                }

                toAppend.forEach(function (opt) {
                    roleSelect.appendChild(opt.cloneNode(true));
                });

                if (allowedRoles.length > 0) {
                    roleSelect.value = allowedRoles[0];
                } else {
                    roleSelect.value = '';
                }

                togglePartner();
            }

            if (departmentSelect && roleSelect) {
                departmentSelect.addEventListener('change', applyRoleFilterByDepartment);
                // Áp dụng filter lần đầu (trường hợp old('department_id'))
                applyRoleFilterByDepartment();
            }

            if (roleSelect) {
                roleSelect.addEventListener('change', togglePartner);
                togglePartner();
            }
        });
    </script>
@endsection
