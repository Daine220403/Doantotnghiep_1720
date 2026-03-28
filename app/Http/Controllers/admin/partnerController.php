<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\partners;
use App\Models\partner_services;
use Illuminate\Http\Request;

class partnerController extends Controller
{
    public function index()
    {
        $partners = partners::withCount('services')
            ->orderBy('name')
            ->get();

        return view('admin.mana_partner.index', compact('partners'));
    }

    public function create()
    {
        return view('admin.mana_partner.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:hotel,transport,restaurant,attraction,other',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:150',
            'address' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive,locked',
        ]);

        partners::create($data);

        return redirect()->route('admin.mana-partner.index')
            ->with('success', 'Thêm đối tác thành công.');
    }

    public function edit(partners $partner)
    {
        return view('admin.mana_partner.create', compact('partner'));
    }

    public function update(Request $request, partners $partner)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:hotel,transport,restaurant,attraction,other',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:150',
            'address' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive,locked',
        ]);

        $partner->update($data);

        return redirect()->route('admin.mana-partner.index')
            ->with('success', 'Cập nhật đối tác thành công.');
    }

    public function destroy(partners $partner)
    {
        $partner->delete();

        return redirect()->route('admin.mana-partner.index')
            ->with('success', 'Xóa đối tác thành công.');
    }

    public function services(partners $partner)
    {
        $partner->load('services');

        return view('admin.mana_partner.services', compact('partner'));
    }

    public function storeService(Request $request, partners $partner)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'service_type' => 'required|string|max:50',
            'description' => 'nullable|string',
            'unit_price' => 'required|numeric|min:0',
            'status' => 'required|in:active,inactive',
        ]);

        $data['partner_id'] = $partner->id;

        partner_services::create($data);

        return redirect()->route('admin.mana-partner.services', $partner->id)
            ->with('success', 'Thêm dịch vụ cho đối tác thành công.');
    }

    public function destroyService(partner_services $service)
    {
        $partnerId = $service->partner_id;
        $service->delete();

        return redirect()->route('admin.mana-partner.services', $partnerId)
            ->with('success', 'Xóa dịch vụ đối tác thành công.');
    }
}
