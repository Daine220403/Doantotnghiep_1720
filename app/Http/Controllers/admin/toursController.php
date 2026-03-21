<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tours;
use App\Models\tour_images;
use App\Models\tour_itineraries;
use App\Models\tour_departures;
use App\Models\tour_policies;
use App\Models\bookings;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class toursController extends Controller
{
    public function index()
    {
        $tours = Tours::with([
            'images',
            'itineraries',
            'departures',
            'policies'
        ])->latest()->get();
        return view('admin.mana_tour.index', compact('tours'));
    }

    public function create()
    {
        return view('admin.mana_tour.create');
    }

    public function store(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:tours,slug',
            'tour_type' => 'required|in:domestic,international',
            'departure_location' => 'required|string|max:255',
            'destination_text' => 'required|string|max:255',
            'transport' => 'required|in:bus,plane,train,car',
            'base_price_from' => 'required|numeric|min:0',
            'status' => 'required|in:draft,published,hidden',

            'description' => 'nullable|string',

            'duration_days' => 'required|integer|min:1',
            'duration_nights' => 'required|integer|min:0|lte:duration_days',

            // policies
            'policies' => 'nullable|array',
            'policies.include' => 'nullable|array',
            'policies.include.*' => 'nullable|string|max:500',
            'policies.exclude' => 'nullable|array',
            'policies.exclude.*' => 'nullable|string|max:500',

            // itineraries
            'itineraries' => 'required|array|min:1',
            'itineraries.*.title' => 'required|string|max:255',
            'itineraries.*.content' => 'required|string',

            // departures
            'departures' => 'required|array|min:1',
            'departures.*.start_date' => 'required|date',
            'departures.*.end_date' => 'required|date|after_or_equal:departures.*.start_date',
            'departures.*.meeting_point' => 'required|string|max:255',
            'departures.*.capacity_total' => 'required|integer|min:1',
            'departures.*.capacity_booked' => 'nullable|integer|min:0',
            'departures.*.price_adult' => 'required|numeric|min:0',
            'departures.*.price_child' => 'nullable|numeric|min:0',
            'departures.*.status' => 'required|in:draft,open,closed,sold_out,cancelled,confirmed,completed',

            // images
            'images' => 'nullable|array',
            'images.*' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'title.required' => 'Vui lòng nhập tên tour.',
            'slug.required' => 'Vui lòng nhập slug.',
            'slug.unique' => 'Slug đã tồn tại.',
            'tour_type.required' => 'Vui lòng chọn loại tour.',
            'tour_type.in' => 'Loại tour không hợp lệ.',
            'departure_location.required' => 'Vui lòng nhập điểm khởi hành.',
            'destination_text.required' => 'Vui lòng nhập điểm đến.',
            'transport.required' => 'Vui lòng chọn phương tiện.',
            'transport.in' => 'Phương tiện không hợp lệ.',
            'base_price_from.required' => 'Vui lòng nhập giá tour.',
            'base_price_from.numeric' => 'Giá tour phải là số.',
            'status.required' => 'Vui lòng chọn trạng thái.',
            'status.in' => 'Trạng thái không hợp lệ.',
            'duration_days.required' => 'Vui lòng nhập số ngày.',
            'duration_days.integer' => 'Số ngày phải là số nguyên.',
            'duration_nights.required' => 'Vui lòng nhập số đêm.',
            'duration_nights.integer' => 'Số đêm phải là số nguyên.',
            'duration_nights.lte' => 'Số đêm không được lớn hơn số ngày.',

            'itineraries.required' => 'Vui lòng nhập lịch trình.',
            'itineraries.array' => 'Lịch trình không hợp lệ.',
            'itineraries.min' => 'Phải có ít nhất 1 ngày lịch trình.',
            'itineraries.*.title.required' => 'Vui lòng nhập tiêu đề ngày trong lịch trình.',
            'itineraries.*.content.required' => 'Vui lòng nhập nội dung lịch trình.',

            'departures.required' => 'Vui lòng nhập lịch khởi hành.',
            'departures.array' => 'Lịch khởi hành không hợp lệ.',
            'departures.min' => 'Phải có ít nhất 1 lịch khởi hành.',
            'departures.*.start_date.required' => 'Vui lòng chọn ngày bắt đầu.',
            'departures.*.start_date.date' => 'Ngày bắt đầu không hợp lệ.',
            'departures.*.end_date.required' => 'Vui lòng chọn ngày kết thúc.',
            'departures.*.end_date.date' => 'Ngày kết thúc không hợp lệ.',
            'departures.*.end_date.after_or_equal' => 'Ngày kết thúc phải lớn hơn hoặc bằng ngày bắt đầu.',
            'departures.*.meeting_point.required' => 'Vui lòng nhập điểm tập trung.',
            'departures.*.capacity_total.required' => 'Vui lòng nhập tổng số chỗ.',
            'departures.*.capacity_total.integer' => 'Tổng số chỗ phải là số nguyên.',
            'departures.*.capacity_total.min' => 'Tổng số chỗ phải lớn hơn 0.',
            'departures.*.capacity_booked.integer' => 'Số chỗ đã đặt phải là số nguyên.',
            'departures.*.capacity_booked.min' => 'Số chỗ đã đặt không được âm.',
            'departures.*.price_adult.required' => 'Vui lòng nhập giá người lớn.',
            'departures.*.price_adult.numeric' => 'Giá người lớn phải là số.',
            'departures.*.price_child.numeric' => 'Giá trẻ em phải là số.',
            'departures.*.status.required' => 'Vui lòng chọn trạng thái lịch khởi hành.',
            'departures.*.status.in' => 'Trạng thái lịch khởi hành không hợp lệ.',

            'images.array' => 'Danh sách ảnh không hợp lệ.',
            'images.*.image' => 'File tải lên phải là hình ảnh.',
            'images.*.mimes' => 'Ảnh phải có định dạng jpg, jpeg, png hoặc webp.',
            'images.*.max' => 'Mỗi ảnh không được vượt quá 2MB.',
        ]);
        // 1. Lưu tour
        $tour = Tours::create([
            'code' => 'T' . time(),
            'title' => $request->title,
            'slug' => $request->slug,
            'tour_type' => $request->tour_type,
            'description' => $request->description,
            'duration_days' => $request->duration_days,
            'duration_nights' => $request->duration_nights,
            'departure_location' => $request->departure_location,
            'destination_text' => $request->destination_text,
            'transport' => $request->transport,
            'base_price_from' => $request->base_price_from,
            'status' => $request->status,
            'created_by' => Auth::id(),

        ]);

        // 2. Policies
        if ($request->has('policies.include')) {
            $index = 0;
            foreach ($request->policies['include'] as $item) {
                if (!empty($item)) {
                    tour_policies::create([
                        'tour_id' => $tour->id,
                        'type' => 'include',
                        'content' => $item,
                        'sort_order' => $index++,
                    ]);
                }
            }
        }

        if ($request->has('policies.exclude')) {
            $index = 0;
            foreach ($request->policies['exclude'] as $item) {
                if (!empty($item)) {
                    tour_policies::create([
                        'tour_id' => $tour->id,
                        'type' => 'exclude',
                        'content' => $item,
                        'sort_order' => $index++,
                    ]);
                }
            }
        }

        // 3. Itineraries
        foreach ($request->itineraries as $day => $itinerary) {
            tour_itineraries::create([
                'tour_id' => $tour->id,
                'day_no' => $day,
                'title' => $itinerary['title'],
                'content' => $itinerary['content'],
            ]);
        }

        // 4. Departures
        foreach ($request->departures as $departure) {
            tour_departures::create([
                'tour_id' => $tour->id,
                'start_date' => $departure['start_date'],
                'end_date' => $departure['end_date'],
                'meeting_point' => $departure['meeting_point'],
                'capacity_total' => $departure['capacity_total'],
                'capacity_booked' => $departure['capacity_booked'] ?? 0,
                'price_adult' => $departure['price_adult'],
                'price_child' => $departure['price_child'] ?? 0,
                'status' => $departure['status'],
            ]);
        }

        // 5. Images
        if ($request->hasFile('images')) {

            foreach ($request->file('images') as $index => $image) {

                $path = $image->store('image/tour', 'public');

                tour_images::create([
                    'tour_id' => $tour->id,
                    'url' => $path,
                    'sort_order' => $index + 1,
                ]);
            }
        }
        return redirect()->route('admin.mana-tour.index')->with('success', 'Thêm tour thành công!');
    }

    public function update(Request $request, $id)
    {
        $tour = Tours::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:tours,slug,' . $tour->id,
            'tour_type' => 'required|in:domestic,international',
            'departure_location' => 'required|string|max:255',
            'destination_text' => 'required|string|max:255',
            'transport' => 'required|in:bus,plane,train,car',
            'base_price_from' => 'required|numeric|min:0',
            'status' => 'required|in:draft,published,hidden',

            'description' => 'nullable|string',

            'duration_days' => 'required|integer|min:1',
            'duration_nights' => 'required|integer|min:0|lte:duration_days',

            // policies
            'policies' => 'nullable|array',
            'policies.include' => 'nullable|array',
            'policies.include.*' => 'nullable|string|max:500',
            'policies.exclude' => 'nullable|array',
            'policies.exclude.*' => 'nullable|string|max:500',

            // itineraries
            'itineraries' => 'required|array|min:1',
            'itineraries.*.title' => 'required|string|max:255',
            'itineraries.*.content' => 'required|string',

            // departures
            'departures' => 'required|array|min:1',
            'departures.*.start_date' => 'required|date',
            'departures.*.end_date' => 'required|date|after_or_equal:departures.*.start_date',
            'departures.*.meeting_point' => 'required|string|max:255',
            'departures.*.capacity_total' => 'required|integer|min:1',
            'departures.*.capacity_booked' => 'nullable|integer|min:0',
            'departures.*.price_adult' => 'required|numeric|min:0',
            'departures.*.price_child' => 'nullable|numeric|min:0',
            'departures.*.status' => 'required|in:open,closed,full,cancelled',

            // images
            'images' => 'nullable|array',
            'images.*' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'title.required' => 'Vui lòng nhập tên tour.',
            'slug.required' => 'Vui lòng nhập slug.',
            'slug.unique' => 'Slug đã tồn tại.',
            'tour_type.required' => 'Vui lòng chọn loại tour.',
            'tour_type.in' => 'Loại tour không hợp lệ.',
            'departure_location.required' => 'Vui lòng nhập điểm khởi hành.',
            'destination_text.required' => 'Vui lòng nhập điểm đến.',
            'transport.required' => 'Vui lòng chọn phương tiện.',
            'transport.in' => 'Phương tiện không hợp lệ.',
            'base_price_from.required' => 'Vui lòng nhập giá tour.',
            'base_price_from.numeric' => 'Giá tour phải là số.',
            'status.required' => 'Vui lòng chọn trạng thái.',
            'status.in' => 'Trạng thái không hợp lệ.',
            'duration_days.required' => 'Vui lòng nhập số ngày.',
            'duration_days.integer' => 'Số ngày phải là số nguyên.',
            'duration_nights.required' => 'Vui lòng nhập số đêm.',
            'duration_nights.integer' => 'Số đêm phải là số nguyên.',
            'duration_nights.lte' => 'Số đêm không được lớn hơn số ngày.',

            'itineraries.required' => 'Vui lòng nhập lịch trình.',
            'itineraries.array' => 'Lịch trình không hợp lệ.',
            'itineraries.min' => 'Phải có ít nhất 1 ngày lịch trình.',
            'itineraries.*.title.required' => 'Vui lòng nhập tiêu đề ngày trong lịch trình.',
            'itineraries.*.content.required' => 'Vui lòng nhập nội dung lịch trình.',

            'departures.required' => 'Vui lòng nhập lịch khởi hành.',
            'departures.array' => 'Lịch khởi hành không hợp lệ.',
            'departures.min' => 'Phải có ít nhất 1 lịch khởi hành.',
            'departures.*.start_date.required' => 'Vui lòng chọn ngày bắt đầu.',
            'departures.*.start_date.date' => 'Ngày bắt đầu không hợp lệ.',
            'departures.*.end_date.required' => 'Vui lòng chọn ngày kết thúc.',
            'departures.*.end_date.date' => 'Ngày kết thúc không hợp lệ.',
            'departures.*.end_date.after_or_equal' => 'Ngày kết thúc phải lớn hơn hoặc bằng ngày bắt đầu.',
            'departures.*.meeting_point.required' => 'Vui lòng nhập điểm tập trung.',
            'departures.*.capacity_total.required' => 'Vui lòng nhập tổng số chỗ.',
            'departures.*.capacity_total.integer' => 'Tổng số chỗ phải là số nguyên.',
            'departures.*.capacity_total.min' => 'Tổng số chỗ phải lớn hơn 0.',
            'departures.*.capacity_booked.integer' => 'Số chỗ đã đặt phải là số nguyên.',
            'departures.*.capacity_booked.min' => 'Số chỗ đã đặt không được âm.',
            'departures.*.price_adult.required' => 'Vui lòng nhập giá người lớn.',
            'departures.*.price_adult.numeric' => 'Giá người lớn phải là số.',
            'departures.*.price_child.numeric' => 'Giá trẻ em phải là số.',
            'departures.*.status.required' => 'Vui lòng chọn trạng thái lịch khởi hành.',
            'departures.*.status.in' => 'Trạng thái lịch khởi hành không hợp lệ.',

            'images.array' => 'Danh sách ảnh không hợp lệ.',
            'images.*.image' => 'File tải lên phải là hình ảnh.',
            'images.*.mimes' => 'Ảnh phải có định dạng jpg, jpeg, png hoặc webp.',
            'images.*.max' => 'Mỗi ảnh không được vượt quá 2MB.',
        ]);
        // dd($request->all());
        // 1. Cập nhật tour
        $tour->update([
            'title' => $request->title,
            'slug' => $request->slug,
            'tour_type' => $request->tour_type,
            'description' => $request->description,
            'duration_days' => $request->duration_days,
            'duration_nights' => $request->duration_nights,
            'departure_location' => $request->departure_location,
            'destination_text' => $request->destination_text,
            'transport' => $request->transport,
            'base_price_from' => $request->base_price_from,
            'status' => $request->status,
        ]);

        // 2. Policies: xoá cũ và thêm lại theo dữ liệu mới
        tour_policies::where('tour_id', $tour->id)->delete();

        if ($request->has('policies.include')) {
            $index = 0;
            foreach ($request->policies['include'] as $item) {
                if (!empty($item)) {
                    tour_policies::create([
                        'tour_id' => $tour->id,
                        'type' => 'include',
                        'content' => $item,
                        'sort_order' => $index++,
                    ]);
                }
            }
        }

        if ($request->has('policies.exclude')) {
            $index = 0;
            foreach ($request->policies['exclude'] as $item) {
                if (!empty($item)) {
                    tour_policies::create([
                        'tour_id' => $tour->id,
                        'type' => 'exclude',
                        'content' => $item,
                        'sort_order' => $index++,
                    ]);
                }
            }
        }

        // 3. Itineraries: xoá cũ và thêm lại
        tour_itineraries::where('tour_id', $tour->id)->delete();

        foreach ($request->itineraries as $day => $itinerary) {
            tour_itineraries::create([
                'tour_id' => $tour->id,
                'day_no' => $day,
                'title' => $itinerary['title'],
                'content' => $itinerary['content'],
            ]);
        }

        // 4. Departures: cập nhật từng lịch, không xoá những lịch đã có booking
        $existingDepartures = tour_departures::where('tour_id', $tour->id)->get()->keyBy('id'); // lấy danh sách lịch khởi hành hiện có của tour, keyBy id để dễ truy cập

        $submittedDepartures = $request->departures;  // danh sách lịch khởi hành gửi từ form
        $submittedIds = collect($submittedDepartures)->pluck('id')->filter()->all(); // lấy danh sách id của các lịch khởi hành đã tồn tại (có id) từ form để so sánh với existingDepartures
        // dd($submittedDepartures,$submittedIds);
        // Cập nhật hoặc tạo mới các lịch khởi hành gửi từ form
        foreach ($submittedDepartures as $departure) {
            $departureId = $departure['id'] ?? null;

            if ($departureId && isset($existingDepartures[$departureId])) { // lịch đã tồn tại, cập nhật
                $model = $existingDepartures[$departureId];

                $model->update([
                    'start_date' => $departure['start_date'],
                    'end_date' => $departure['end_date'],
                    'meeting_point' => $departure['meeting_point'],
                    'capacity_total' => $departure['capacity_total'],
                    'capacity_booked' => $departure['capacity_booked'] ?? $model->capacity_booked,
                    'price_adult' => $departure['price_adult'],
                    'price_child' => $departure['price_child'] ?? 0,
                    'status' => $departure['status'],
                ]);
            } else {
                // lịch mới thêm
                tour_departures::create([
                    'tour_id' => $tour->id,
                    'start_date' => $departure['start_date'],
                    'end_date' => $departure['end_date'],
                    'meeting_point' => $departure['meeting_point'],
                    'capacity_total' => $departure['capacity_total'],
                    'capacity_booked' => $departure['capacity_booked'] ?? 0,
                    'price_adult' => $departure['price_adult'],
                    'price_child' => $departure['price_child'] ?? 0,
                    'status' => $departure['status'],
                ]);
            }
        }

        // Xoá những lịch không còn trong form và chưa có booking
        foreach ($existingDepartures as $existing) {
            if (!in_array($existing->id, $submittedIds)) {
                if ($existing->capacity_booked > 0) {
                    // đã có booking thì giữ lại, không xoá
                    continue;
                }
                $existing->delete();
            }
        }

        // 5. Images: nếu upload ảnh mới thì xoá ảnh cũ và lưu lại
        if ($request->hasFile('images')) {
            $oldImages = tour_images::where('tour_id', $tour->id)->get();
            foreach ($oldImages as $img) {
                if ($img->url && Storage::disk('public')->exists($img->url)) {
                    Storage::disk('public')->delete($img->url);
                }
                $img->delete();
            }

            foreach ($request->file('images') as $index => $image) {
                $path = $image->store('image/tour', 'public');

                tour_images::create([
                    'tour_id' => $tour->id,
                    'url' => $path,
                    'sort_order' => $index + 1,
                ]);
            }
        }

        return redirect()->route('admin.mana-tour.index')->with('success', 'Cập nhật tour thành công!');
    }

    public function destroy($id)
    {
        $tour = Tours::findOrFail($id);
        // kiểm tra nếu tour có lịch khởi hành đã được booking thì không cho xoá
        $hasBookedDepartures = tour_departures::where('tour_id', $tour->id)->where('capacity_booked', '>', 0)->exists();
        if ($hasBookedDepartures) {
            return redirect()->route('admin.mana-tour.index')->with('error', 'Không thể xóa tour vì có lịch khởi hành đã được đặt chỗ.');
        }
        // Xoá ảnh trong storage và bản ghi ảnh
        $images = tour_images::where('tour_id', $tour->id)->get();
        foreach ($images as $image) {
            if ($image->url && Storage::disk('public')->exists($image->url)) {
                Storage::disk('public')->delete($image->url);
            }
            $image->delete();
        }

        // Xoá các bản ghi liên quan
        tour_itineraries::where('tour_id', $tour->id)->delete();
        tour_departures::where('tour_id', $tour->id)->delete();
        tour_policies::where('tour_id', $tour->id)->delete();

        // Xoá tour
        $tour->delete();

        return redirect()->route('admin.mana-tour.index')->with('success', 'Xóa tour thành công!');
    }

    public function confirmDeparture(tour_departures $departure)
    {
        // kiểm tra lịch tối thiểu để chốt đoàn, ít nhất phải 1 nữa tổng số chỗ
        if ($departure->capacity_booked < ceil($departure->capacity_total / 2)) {
            return back()->with('error', 'Không thể chốt đoàn cho lịch khởi hành này vì chưa đủ số lượng khách tối thiểu.');
        }
        if (!in_array($departure->status, ['open', 'sold_out'])) {
            return back()->with('error', 'Chỉ có thể chốt đoàn với lịch đang mở hoặc sắp hết chỗ.');
        }

        if ($departure->start_date < now()->toDateString()) {
            return back()->with('error', 'Không thể chốt đoàn cho lịch đã quá hạn khởi hành.');
        }

        // kiểm tra đã thanh toán hết chưa
        $unpaidBookings = bookings::where('departure_id', $departure->id)
            ->whereNotIn('status', ['cancelled'])
            ->whereHas('order', function ($q) {
                $q->where('status', '!=', 'paid');
            })
            ->count();
        if ($unpaidBookings > 0) {
            return back()->with('error', 'Không thể chốt đoàn vì còn ' . $unpaidBookings . ' booking chưa thanh toán.');
        }
        // cập nhật trạng thái chốt đoàn cho lịch
        $departure->status = 'confirmed';
        $departure->save();

        // cập nhật trạng thái booking tương ứng sang confirmed (chỉ với booking chưa hủy/hoàn tất)
        bookings::where('departure_id', $departure->id)
            ->whereNotIn('status', ['cancelled', 'completed'])
            ->whereHas('order', function ($q) {
                $q->where('status', 'paid');
            })
            ->update(['status' => 'confirmed']);

        return back()->with('success', 'Đã chốt đoàn cho lịch khởi hành này.');
    }
}
