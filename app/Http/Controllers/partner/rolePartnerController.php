<?php

namespace App\Http\Controllers\partner;

use App\Http\Controllers\Controller;
use App\Models\partners;
use App\Models\partner_services;
use App\Models\departure_services;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class rolePartnerController extends Controller
{

	// Hiển thị danh sách dịch vụ mà tài khoản doi tác đang cung cấp (role partner)
	public function index()
	{
		$user = Auth::user();

		if (!$user || $user->role !== 'partner') {
			abort(403);
		}

		$partner = $user->partner;

		if ($partner) {
			$partner->load('services');
		}

		return view('admin.partner.index', [
			'partner' => $partner,
		]);
	}

	// Danh sách yêu cầu dịch vụ cho các lịch khởi hành liên quan đến đối tác
	public function requests()
	{
		$user = Auth::user();

		if (!$user || $user->role !== 'partner') {
			abort(403);
		}

		$partner = $user->partner;

		if (!$partner) {
			abort(403);
		}

		$requests = departure_services::with(['departure.tour', 'partnerService'])
			->whereHas('partnerService', function ($q) use ($partner) {
				$q->where('partner_id', $partner->id);
			})
			->orderByDesc('service_date')
			->orderByDesc('id')
			->get();

		return view('admin.partner.requests', [
			'partner' => $partner,
			'requests' => $requests,
		]);
	}

	public function create()
	{
		$user = Auth::user();

		if (!$user || $user->role !== 'partner') {
			abort(403);
		}

		$partner = $user->partner;

		if (!$partner) {
			abort(403);
		}

		return view('admin.partner.create_service', [
			'partner' => $partner,
		]);
	}

	public function store(Request $request)
	{
		$user = Auth::user();

		if (!$user || $user->role !== 'partner') {
			abort(403);
		}

		$partner = $user->partner;

		if (!$partner) {
			abort(403);
		}

		$data = $request->validate([
			'name' => 'required|string|max:255',
			'service_type' => 'required|string|max:50',
			'description' => 'nullable|string',
			'unit_price' => 'required|numeric|min:0',
			'status' => 'required|in:active,inactive',
		]);

		$data['partner_id'] = $partner->id;

		partner_services::create($data);

		return redirect()->route('admin.partner.services')
			->with('success', 'Thêm dịch vụ mới thành công.');
	}

	public function edit(partner_services $service)
	{
		$user = Auth::user();

		if (!$user || $user->role !== 'partner') {
			abort(403);
		}

		$partner = $user->partner;

		if (!$partner || $service->partner_id !== $partner->id) {
			abort(403);
		}

		return view('admin.partner.edit_service', [
			'partner' => $partner,
			'service' => $service,
		]);
	}

	public function update(Request $request, partner_services $service)
	{
		$user = Auth::user();

		if (!$user || $user->role !== 'partner') {
			abort(403);
		}

		$partner = $user->partner;

		if (!$partner || $service->partner_id !== $partner->id) {
			abort(403);
		}

		$data = $request->validate([
			'name' => 'required|string|max:255',
			'service_type' => 'required|string|max:50',
			'description' => 'nullable|string',
			'unit_price' => 'required|numeric|min:0',
			'status' => 'required|in:active,inactive',
		]);

		$service->update($data);

		return redirect()->route('admin.partner.services')
			->with('success', 'Cập nhật dịch vụ thành công.');
	}

	public function toggleStatus(partner_services $service)
	{
		$user = Auth::user();

		if (!$user || $user->role !== 'partner') {
			abort(403);
		}

		$partner = $user->partner;

		if (!$partner || $service->partner_id !== $partner->id) {
			abort(403);
		}

		$service->status = $service->status === 'active' ? 'inactive' : 'active';
		$service->save();

		return redirect()->route('admin.partner.services')
			->with('success', 'Cập nhật trạng thái dịch vụ thành công.');
	}

	// Đối tác xác nhận yêu cầu dịch vụ cho 1 lịch khởi hành
	public function confirmRequest(departure_services $departureService)
	{
		$user = Auth::user();

		if (!$user || $user->role !== 'partner') {
			abort(403);
		}

		$partner = $user->partner;

		if (!$partner || $departureService->partnerService->partner_id !== $partner->id) {
			abort(403);
		}

		// Chỉ cho phép xác nhận khi đang ở trạng thái pending
		if ($departureService->status !== 'pending') {
			return redirect()->route('admin.partner.requests.index')
				->with('error', 'Chỉ có thể xác nhận các yêu cầu đang chờ xử lý.');
		}

		$departureService->status = 'confirmed';
		$departureService->confirmed_at = now();
		$departureService->save();

		return redirect()->route('admin.partner.requests.index')
			->with('success', 'Bạn đã xác nhận yêu cầu dịch vụ thành công.');
	}

	// Đối tác từ chối yêu cầu dịch vụ cho 1 lịch khởi hành
	public function rejectRequest(departure_services $departureService)
	{
		$user = Auth::user();

		if (!$user || $user->role !== 'partner') {
			abort(403);
		}

		$partner = $user->partner;

		if (!$partner || $departureService->partnerService->partner_id !== $partner->id) {
			abort(403);
		}

		// Chỉ cho phép từ chối khi đang ở trạng thái pending
		if ($departureService->status !== 'pending') {
			return redirect()->route('admin.partner.requests.index')
				->with('error', 'Chỉ có thể từ chối các yêu cầu đang chờ xử lý.');
		}

		$departureService->status = 'cancelled';
		$departureService->confirmed_at = null;
		$departureService->save();

		return redirect()->route('admin.partner.requests.index')
			->with('success', 'Bạn đã từ chối yêu cầu dịch vụ này.');
	}

	// Đối tác duyệt yêu cầu hủy dịch vụ
	public function approveCancelRequest(departure_services $departureService)
	{
		$user = Auth::user();

		if (!$user || $user->role !== 'partner') {
			abort(403);
		}

		$partner = $user->partner;

		if (!$partner || $departureService->partnerService->partner_id !== $partner->id) {
			abort(403);
		}

		if ($departureService->status !== 'confirmed' || !$departureService->cancel_requested) {
			return redirect()->route('admin.partner.requests.index')
				->with('error', 'Chỉ có thể duyệt hủy cho các dịch vụ đã xác nhận và đang chờ hủy.');
		}

		$departureService->status = 'cancelled';
		$departureService->cancel_requested = false;
		$departureService->save();

		return redirect()->route('admin.partner.requests.index')
			->with('success', 'Bạn đã chấp nhận hủy dịch vụ này.');
	}

	// Đối tác từ chối yêu cầu hủy dịch vụ
	public function rejectCancelRequest(departure_services $departureService)
	{
		$user = Auth::user();

		if (!$user || $user->role !== 'partner') {
			abort(403);
		}

		$partner = $user->partner;

		if (!$partner || $departureService->partnerService->partner_id !== $partner->id) {
			abort(403);
		}

		if ($departureService->status !== 'confirmed' || !$departureService->cancel_requested) {
			return redirect()->route('admin.partner.requests.index')
				->with('error', 'Chỉ có thể từ chối hủy cho các dịch vụ đã xác nhận và đang chờ hủy.');
		}

		$departureService->cancel_requested = false;
		$departureService->save();

		return redirect()->route('admin.partner.requests.index')
			->with('success', 'Bạn đã từ chối yêu cầu hủy dịch vụ này.');
	}

}
