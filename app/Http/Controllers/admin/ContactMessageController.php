<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContactMessageController extends Controller
{
    public function index(Request $request)
    {
        if (!in_array(Auth::user()->role, ['admin', 'staff'], true)) {
            return redirect()->route('admin.index')
                ->with('info', 'Bạn không có quyền truy cập danh sách liên hệ.');
        }

        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:new,processing,resolved'],
            'preferred_contact' => ['nullable', 'in:phone,email,zalo'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $messagesQuery = ContactMessage::query();

        $keyword = trim((string) ($validated['q'] ?? ''));
        if ($keyword !== '') {
            $messagesQuery->where(function ($query) use ($keyword) {
                $query->where('name', 'like', '%' . $keyword . '%')
                    ->orWhere('email', 'like', '%' . $keyword . '%')
                    ->orWhere('phone', 'like', '%' . $keyword . '%')
                    ->orWhere('subject', 'like', '%' . $keyword . '%')
                    ->orWhere('message', 'like', '%' . $keyword . '%');
            });
        }

        if (!empty($validated['status'])) {
            $messagesQuery->where('status', $validated['status']);
        }

        if (!empty($validated['preferred_contact'])) {
            $messagesQuery->where('preferred_contact', $validated['preferred_contact']);
        }

        if (!empty($validated['date_from'])) {
            $messagesQuery->whereDate('created_at', '>=', $validated['date_from']);
        }

        if (!empty($validated['date_to'])) {
            $messagesQuery->whereDate('created_at', '<=', $validated['date_to']);
        }

        $messages = $messagesQuery
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total' => ContactMessage::count(),
            'new' => ContactMessage::where('status', 'new')->count(),
            'processing' => ContactMessage::where('status', 'processing')->count(),
            'resolved' => ContactMessage::where('status', 'resolved')->count(),
        ];

        return view('admin.staff_contact.index', compact('messages', 'stats'));
    }

    public function markResolved(ContactMessage $contact)
    {
        if (!in_array(Auth::user()->role, ['admin', 'staff'], true)) {
            return redirect()->route('admin.index')
                ->with('info', 'Bạn không có quyền cập nhật trạng thái liên hệ.');
        }

        if ($contact->status === 'resolved') {
            return redirect()->route('admin.staff-contacts.index')
                ->with('info', 'Liên hệ này đã ở trạng thái đã xử lý.');
        }

        $contact->update([
            'status' => 'resolved',
        ]);

        return redirect()->back()->with('success', 'Đã cập nhật liên hệ sang trạng thái đã xử lý.');
    }
}
