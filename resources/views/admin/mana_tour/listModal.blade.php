<style>
    .cke_notification {
        display: none !important;
    }
</style>
@foreach ($tours as $tour)
    @php
        $firstImage = $tour->images->first();
        $mainImage = $firstImage ? asset($firstImage->url) : 'https://via.placeholder.com/120x80';
        $includes = $tour->policies->where('type', 'include')->sortBy('sort_order');
        $excludes = $tour->policies->where('type', 'exclude')->sortBy('sort_order');
    @endphp

    {{-- ========================= MODAL XEM CHI TIẾT ========================= --}}
    <div class="modal fade" id="ModalXemChiTiet{{ $tour->id }}" tabindex="-1" role="dialog"
        aria-labelledby="ModalXemChiTietLabel{{ $tour->id }}" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="ModalXemChiTietLabel{{ $tour->id }}">
                        Chi tiết tour: <span class="text-primary">{{ $tour->title }}</span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">

                    <div class="row">
                        <div class="col-lg-5 mb-3">
                            <div class="border rounded p-2">
                                <div class="d-flex flex-wrap" style="gap:8px;">
                                    @forelse ($tour->images as $image)
                                        <img src="{{ asset('storage/' . $image->url) }}" alt="{{ $tour->title }}"
                                            style="width: 120px; height: 80px; object-fit: cover; border-radius: 6px;">
                                    @empty
                                        <img src="https://via.placeholder.com/120x80" alt="No image"
                                            style="width: 120px; height: 80px; object-fit: cover; border-radius: 6px;">
                                    @endforelse
                                </div>
                                <small class="text-muted d-block mt-2">
                                    Ảnh tour (tour_images)
                                </small>
                            </div>
                        </div>

                        <div class="col-lg-7 mb-3">
                            <div class="row">

                                <div class="col-md-6 mb-2">
                                    <div class="small text-muted">Mã tour (code)</div>
                                    <div class="font-weight-bold">{{ $tour->code }}</div>
                                </div>

                                <div class="col-md-6 mb-2">
                                    <div class="small text-muted">Slug</div>
                                    <div class="font-weight-bold">{{ $tour->slug }}</div>
                                </div>

                                <div class="col-md-6 mb-2">
                                    <div class="small text-muted">Loại tour</div>
                                    @if ($tour->tour_type == 'domestic')
                                        <span class="badge badge-info">Domestic / Trong nước</span>
                                    @else
                                        <span class="badge badge-primary">International / Quốc tế</span>
                                    @endif
                                </div>

                                <div class="col-md-6 mb-2">
                                    <div class="small text-muted">Điểm khởi hành</div>
                                    <div class="font-weight-bold">{{ $tour->departure_location }}</div>
                                </div>

                                <div class="col-md-6 mb-2">
                                    <div class="small text-muted">Điểm đến</div>
                                    <div class="font-weight-bold">{{ $tour->destination_text }}</div>
                                </div>

                                <div class="col-md-6 mb-2">
                                    <div class="small text-muted">Thời lượng</div>
                                    <div class="font-weight-bold">
                                        {{ $tour->duration_days }} ngày {{ $tour->duration_nights }} đêm
                                    </div>
                                </div>

                                <div class="col-md-6 mb-2">
                                    <div class="small text-muted">Giá từ</div>
                                    <div class="font-weight-bold text-danger">
                                        {{ number_format($tour->base_price_from, 0, ',', '.') }} đ
                                    </div>
                                </div>

                                <div class="col-md-6 mb-2">
                                    <div class="small text-muted">Trạng thái</div>
                                    @if ($tour->status == 'published')
                                        <span class="badge badge-success">published / Đang bán</span>
                                    @elseif($tour->status == 'draft')
                                        <span class="badge badge-secondary">draft / Nháp</span>
                                    @else
                                        <span class="badge badge-dark">hidden / Ẩn</span>
                                    @endif
                                </div>

                                <div class="col-md-6 mb-2">
                                    <div class="small text-muted">Ngày tạo</div>
                                    <div>{{ optional($tour->created_at)->format('d/m/Y H:i') }}</div>
                                </div>

                                <div class="col-md-6 mb-2">
                                    <div class="small text-muted">Ngày cập nhật</div>
                                    <div>{{ optional($tour->updated_at)->format('d/m/Y H:i') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="border rounded p-3 mb-3">
                        <h6 class="font-weight-bold mb-2">Mô tả chi tiết</h6>
                        <div class="text-muted" style="white-space: pre-line;">
                            {!! $tour->description !!}
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100">
                                <h6 class="font-weight-bold text-success">Bao gồm</h6>
                                <ul class="mb-0 pl-3">
                                    @forelse ($includes as $item)
                                        <li>{{ $item->content }}</li>
                                    @empty
                                        <li class="text-muted">Chưa có dữ liệu</li>
                                    @endforelse
                                </ul>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100">
                                <h6 class="font-weight-bold text-danger">Không bao gồm</h6>
                                <ul class="mb-0 pl-3">
                                    @forelse ($excludes as $item)
                                        <li>{{ $item->content }}</li>
                                    @empty
                                        <li class="text-muted">Chưa có dữ liệu</li>
                                    @endforelse
                                </ul>
                            </div>
                        </div>
                    </div>

                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="tab-itin-{{ $tour->id }}" data-toggle="tab"
                                href="#pane-itin-{{ $tour->id }}" role="tab">
                                Lịch trình
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tab-dep-{{ $tour->id }}" data-toggle="tab"
                                href="#pane-dep-{{ $tour->id }}" role="tab">
                                Lịch khởi hành
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content border border-top-0 rounded-bottom p-3">
                        <div class="tab-pane fade show active" id="pane-itin-{{ $tour->id }}" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th style="width:80px;">Ngày</th>
                                            <th>Tiêu đề</th>
                                            <th>Nội dung</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($tour->itineraries->sortBy('day_no') as $itinerary)
                                            <tr>
                                                <td class="text-center">{{ $itinerary->day_no }}</td>
                                                <td class="font-weight-bold">{{ $itinerary->title }}</td>
                                                <td class="text-muted">{{ $itinerary->content }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-center text-muted">Chưa có lịch trình
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="pane-dep-{{ $tour->id }}" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Ngày đi</th>
                                            <th>Ngày về</th>
                                            <th>Điểm tập trung</th>
                                            <th class="text-center">Tổng chỗ</th>
                                            <th class="text-center">Đã đặt</th>
                                            <th class="text-right">Giá NL</th>
                                            <th class="text-right">Giá TE</th>
                                            <th class="text-center">Trạng thái</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($tour->departures as $departure)
                                            <tr>
                                                <td>{{ \Carbon\Carbon::parse($departure->start_date)->format('d/m/Y') }}
                                                </td>
                                                <td>{{ \Carbon\Carbon::parse($departure->end_date)->format('d/m/Y') }}
                                                </td>
                                                <td>{{ $departure->meeting_point }}</td>
                                                <td class="text-center">{{ $departure->capacity_total }}</td>
                                                <td class="text-center">{{ $departure->capacity_booked }}</td>
                                                <td class="text-right">
                                                    {{ number_format($departure->price_adult, 0, ',', '.') }}</td>
                                                <td class="text-right">
                                                    {{ number_format($departure->price_child, 0, ',', '.') }}</td>
                                                <td class="text-center">
                                                    <span class="badge badge-success">{{ $departure->status }}</span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center text-muted">Chưa có lịch khởi
                                                    hành</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                </div>

            </div>
        </div>
    </div>

    {{-- ========================= MODAL SỬA ========================= --}}
    <div class="modal fade" id="ModalSua{{ $tour->id }}" tabindex="-1" role="dialog"
        aria-labelledby="ModalSuaLabel{{ $tour->id }}" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">

                <form action="{{ route('admin.mana-tour.update', $tour->id) }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="ModalSuaLabel{{ $tour->id }}">
                            Chỉnh sửa tour: {{ $tour->title }}
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <h6 class="font-weight-bold mb-3 text-primary">Thông tin chung</h6>

                        <div class="row">
                            <div class="col-lg-12 mb-3">
                                <div class="border rounded p-2">
                                    <div class="d-flex flex-wrap" style="gap:8px;">
                                        @forelse ($tour->images as $image)
                                            <img src="{{ asset('storage/' . $image->url) }}"
                                                alt="{{ $tour->title }}"
                                                style="width: 120px; height: 80px; object-fit: cover; border-radius: 6px;">
                                        @empty
                                            <img src="https://via.placeholder.com/120x80" alt="No image"
                                                style="width: 120px; height: 80px; object-fit: cover; border-radius: 6px;">
                                        @endforelse
                                    </div>
                                    <div class="mt-3">
                                        <input type="file" name="images[]" accept="image/*" multiple
                                            class="form-control">
                                    </div>

                                    <small class="text-muted d-block mt-2">
                                        Ảnh tour (tour_images)
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Mã tour (code)</label>
                                <input type="text" class="form-control" value="{{ $tour->code }}" disabled>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Tên tour</label>
                                <input type="text" name="title" class="form-control"
                                    value="{{ $tour->title }}">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Slug</label>
                                <input type="text" name="slug" class="form-control"
                                    value="{{ $tour->slug }}">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Loại tour</label>
                                <select name="tour_type" class="form-control">
                                    <option value="domestic" {{ $tour->tour_type == 'domestic' ? 'selected' : '' }}>
                                        domestic</option>
                                    <option value="international"
                                        {{ $tour->tour_type == 'international' ? 'selected' : '' }}>international
                                    </option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Điểm khởi hành</label>
                                <input type="text" name="departure_location" class="form-control"
                                    value="{{ $tour->departure_location }}">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Điểm đến</label>
                                <input type="text" name="destination_text" class="form-control"
                                    value="{{ $tour->destination_text }}">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Phương tiện</label>
                                <select name="transport" class="form-control">
                                    <option value="bus" {{ $tour->transport == 'bus' ? 'selected' : '' }}>
                                        Xe khách</option>
                                    <option value="plane" {{ $tour->transport == 'plane' ? 'selected' : '' }}>
                                        Máy bay</option>
                                    <option value="train" {{ $tour->transport == 'train' ? 'selected' : '' }}>
                                        Tàu hỏa</option>
                                    <option value="car" {{ $tour->transport == 'car' ? 'selected' : '' }}>
                                        Ô tô</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Giá từ</label>
                                <input type="number" name="base_price_from" class="form-control"
                                    value="{{ $tour->base_price_from }}">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Trạng thái</label>
                                <select name="status" class="form-control">
                                    <option value="published" {{ $tour->status == 'published' ? 'selected' : '' }}>
                                        published</option>
                                    <option value="draft" {{ $tour->status == 'draft' ? 'selected' : '' }}>draft
                                    </option>
                                    <option value="hidden" {{ $tour->status == 'hidden' ? 'selected' : '' }}>hidden
                                    </option>
                                </select>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label>Mô tả</label>
                                <textarea name="description" id="description_editor_{{ $tour->id }}" rows="4" class="form-control">{{ $tour->description }}</textarea>
                            </div>

                            <h6 class="col-md-12 font-weight-bold text-primary">Bao gồm / Không bao gồm</h6>

                            <div class="col-md-6 mb-3">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <label class="mb-0 font-weight-bold">Bao gồm</label>
                                    <button type="button" class="btn btn-sm btn-outline-success btn_add_include"
                                        data-tour="{{ $tour->id }}">
                                        <i class="fas fa-plus"></i> Thêm
                                    </button>
                                </div>

                                <div id="include_container_{{ $tour->id }}" class="border rounded p-2"
                                    style="min-height: 80px;">
                                    @forelse ($includes as $item)
                                        <div class="policy-row d-flex align-items-center mb-2">
                                            <input type="text" name="policies[include][]" class="form-control"
                                                value="{{ $item->content }}" placeholder="Nhập mục bao gồm...">
                                            <button type="button"
                                                class="btn btn-sm btn-outline-danger ml-2 btn_remove_policy">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    @empty
                                        <div class="policy-row d-flex align-items-center mb-2">
                                            <input type="text" name="policies[include][]" class="form-control"
                                                placeholder="Nhập mục bao gồm...">
                                            <button type="button"
                                                class="btn btn-sm btn-outline-danger ml-2 btn_remove_policy">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    @endforelse
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <label class="mb-0 font-weight-bold">Không bao gồm</label>
                                    <button type="button" class="btn btn-sm btn-outline-success btn_add_exclude"
                                        data-tour="{{ $tour->id }}">
                                        <i class="fas fa-plus"></i> Thêm
                                    </button>
                                </div>

                                <div id="exclude_container_{{ $tour->id }}" class="border rounded p-2"
                                    style="min-height: 80px;">
                                    @forelse ($excludes as $item)
                                        <div class="policy-row d-flex align-items-center mb-2">
                                            <input type="text" name="policies[exclude][]" class="form-control"
                                                value="{{ $item->content }}" placeholder="Nhập mục không bao gồm...">
                                            <button type="button"
                                                class="btn btn-sm btn-outline-danger ml-2 btn_remove_policy">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    @empty
                                        <div class="policy-row d-flex align-items-center mb-2">
                                            <input type="text" name="policies[exclude][]" class="form-control"
                                                placeholder="Nhập mục không bao gồm...">
                                            <button type="button"
                                                class="btn btn-sm btn-outline-danger ml-2 btn_remove_policy">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        <hr>
                        <h6 class="font-weight-bold mb-3 text-primary">Thời gian</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Số ngày</label>
                                <input type="number" min="1" id="duration_days_{{ $tour->id }}"
                                    name="duration_days" class="form-control" value="{{ $tour->duration_days }}">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Số đêm</label>
                                <input type="number" min="0" id="duration_nights_{{ $tour->id }}"
                                    name="duration_nights" class="form-control" value="{{ $tour->duration_nights }}"
                                    readonly>
                            </div>
                        </div>

                        <h6 class="font-weight-bold mb-3 text-primary">Lịch trình</h6>
                        <div id="itinerary_container_{{ $tour->id }}">
                            @foreach ($tour->itineraries->sortBy('day_no') as $itinerary)
                                <div class="border p-3 mb-3 rounded">
                                    <label class="font-weight-bold">Ngày {{ $itinerary->day_no }}</label>
                                    <input type="text" class="form-control mb-2"
                                        name="itineraries[{{ $itinerary->day_no }}][title]"
                                        value="{{ $itinerary->title }}"
                                        placeholder="Tiêu đề ngày {{ $itinerary->day_no }}">
                                    <textarea class="form-control" name="itineraries[{{ $itinerary->day_no }}][content]" rows="3"
                                        placeholder="Nội dung ngày {{ $itinerary->day_no }}...">{{ $itinerary->content }}</textarea>
                                </div>
                            @endforeach
                        </div>

                        <hr>
                        <h6 class="font-weight-bold mb-3 text-primary">Lịch khởi hành</h6>

                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <small class="text-muted">Quản lý các đợt khởi hành của tour</small>
                            <button type="button" class="btn btn-sm btn-outline-primary btn_add_departure"
                                data-tour="{{ $tour->id }}">
                                <i class="fas fa-plus"></i> Thêm lịch khởi hành
                            </button>
                        </div>

                        <div id="departure_container_{{ $tour->id }}">
                            @forelse ($tour->departures->sortBy('start_date') as $index => $departure)
                                <div class="border rounded p-3 mb-3 departure-item">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="mb-0 text-dark">
                                            Đợt khởi hành #{{ $index + 1 }}
                                        </h6>
                                        <button type="button"
                                            class="btn btn-sm btn-outline-danger btn_remove_departure">
                                            <i class="fas fa-times"></i> Xóa
                                        </button>
                                    </div>

                                    <input type="hidden" name="departures[{{ $index }}][id]"
                                        value="{{ $departure->id }}">

                                    <div class="row">
                                        <div class="col-md-3 mb-3">
                                            <label>Ngày đi</label>
                                            <input type="date" name="departures[{{ $index }}][start_date]"
                                                class="form-control" value="{{ $departure->start_date }}">
                                        </div>

                                        <div class="col-md-3 mb-3">
                                            <label>Ngày về</label>
                                            <input type="date" name="departures[{{ $index }}][end_date]"
                                                class="form-control" value="{{ $departure->end_date }}">
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label>Điểm tập trung</label>
                                            <input type="text"
                                                name="departures[{{ $index }}][meeting_point]"
                                                class="form-control" value="{{ $departure->meeting_point }}">
                                        </div>

                                        <div class="col-md-3 mb-3">
                                            <label>Tổng chỗ</label>
                                            <input type="number" min="1"
                                                name="departures[{{ $index }}][capacity_total]"
                                                class="form-control" value="{{ $departure->capacity_total }}">
                                        </div>

                                        <div class="col-md-3 mb-3">
                                            <label>Đã đặt</label>
                                            <input type="number" min="0"
                                                name="departures[{{ $index }}][capacity_booked]"
                                                class="form-control" value="{{ $departure->capacity_booked }}">
                                        </div>

                                        <div class="col-md-3 mb-3">
                                            <label>Giá người lớn</label>
                                            <input type="number" min="0"
                                                name="departures[{{ $index }}][price_adult]"
                                                class="form-control" value="{{ $departure->price_adult }}">
                                        </div>

                                        <div class="col-md-3 mb-3">
                                            <label>Giá trẻ em</label>
                                            <input type="number" min="0"
                                                name="departures[{{ $index }}][price_child]"
                                                class="form-control" value="{{ $departure->price_child }}">
                                        </div>

                                        <div class="col-md-12 mb-3">
                                            <label>Trạng thái</label>
                                            <select name="departures[{{ $index }}][status]"
                                                class="form-control">
                                                <option value="open"
                                                    {{ $departure->status == 'open' ? 'selected' : '' }}>open</option>
                                                <option value="closed"
                                                    {{ $departure->status == 'closed' ? 'selected' : '' }}>closed
                                                </option>
                                                <option value="full"
                                                    {{ $departure->status == 'full' ? 'selected' : '' }}>full</option>
                                                <option value="cancelled"
                                                    {{ $departure->status == 'cancelled' ? 'selected' : '' }}>cancelled
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-muted small mb-2">Chưa có lịch khởi hành. Nhấn "Thêm lịch khởi hành"
                                    để tạo mới.</div>
                            @endforelse
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Lưu thay đổi
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
@endforeach
<script>
    @foreach ($tours as $tour) // Lặp qua tất cả các tour trong danh sách $tours
        // Kiểm tra xem CKEditor đã được khởi tạo cho textarea có id 'description_editor_{id}' chưa
        if (!CKEDITOR.instances['description_editor_{{ $tour->id }}']) {

            // Nếu chưa có instance CKEditor thì tiến hành khởi tạo editor cho textarea đó
            CKEDITOR.replace('description_editor_{{ $tour->id }}');

        }
    @endforeach
    // CKEDITOR.replace('description_editor');
</script>
<script>
    (function() {
        function createDayBlock(day) {
            return `
                <div class="border p-3 mb-3 rounded itinerary-day-block">
                    <label class="font-weight-bold">Ngày ${day}</label>
                    <input type="text" class="form-control mb-2"
                        name="itineraries[${day}][title]"
                        placeholder="Tiêu đề ngày ${day}">
                    <textarea class="form-control"
                        name="itineraries[${day}][content]"
                        rows="3"
                        placeholder="Nội dung ngày ${day}..."></textarea>
                </div>
            `;
        }

        function createPolicyRow(type) {
            const name = type === 'include' ? 'policies[include][]' : 'policies[exclude][]';
            const placeholder = type === 'include' ?
                'Nhập mục bao gồm...' :
                'Nhập mục không bao gồm...';

            return `
                <div class="policy-row d-flex align-items-center mb-2">
                    <input type="text" name="${name}" class="form-control" placeholder="${placeholder}">
                    <button type="button" class="btn btn-sm btn-outline-danger ml-2 btn_remove_policy">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
        }

        function createDepartureBlock(index) {
            return `
                <div class="border rounded p-3 mb-3 departure-item">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0 text-dark">Đợt khởi hành #${index + 1}</h6>
                        <button type="button" class="btn btn-sm btn-outline-danger btn_remove_departure">
                            <i class="fas fa-times"></i> Xóa
                        </button>
                    </div>

                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label>Ngày đi</label>
                            <input type="date" name="departures[${index}][start_date]" class="form-control departure-start-date">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label>Ngày về</label>
                            <input type="date" name="departures[${index}][end_date]" class="form-control departure-end-date" readonly>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Điểm tập trung</label>
                            <input type="text" name="departures[${index}][meeting_point]" class="form-control"
                                placeholder="Nhập điểm tập trung">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label>Tổng chỗ</label>
                            <input type="number" min="1" name="departures[${index}][capacity_total]" class="form-control" value="0">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label>Đã đặt</label>
                            <input type="number" min="0" name="departures[${index}][capacity_booked]" class="form-control" value="0">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label>Giá người lớn</label>
                            <input type="number" min="0" name="departures[${index}][price_adult]" class="form-control" value="0">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label>Giá trẻ em</label>
                            <input type="number" min="0" name="departures[${index}][price_child]" class="form-control" value="0">
                        </div>

                        <div class="col-md-12 mb-3">
                            <label>Trạng thái</label>
                            <select name="departures[${index}][status]" class="form-control">
                                <option value="open">open</option>
                                <option value="closed">closed</option>
                                <option value="full">full</option>
                                <option value="cancelled">cancelled</option>
                            </select>
                        </div>
                    </div>
                </div>
            `;
        }

        function calculateEndDate(startValue, days) {
            if (!startValue || !days || days <= 0) return '';

            const startDate = new Date(startValue);
            if (isNaN(startDate.getTime())) return '';

            const endDate = new Date(startDate);
            endDate.setDate(endDate.getDate() + (days - 1));

            const year = endDate.getFullYear();
            const month = String(endDate.getMonth() + 1).padStart(2, '0');
            const day = String(endDate.getDate()).padStart(2, '0');

            return `${year}-${month}-${day}`;
        }

        function updateAllDepartureEndDates(tourId) {
            const daysInput = document.getElementById(`duration_days_${tourId}`);
            const days = parseInt(daysInput?.value || 0);

            const modal = document.getElementById(`ModalSua${tourId}`);
            if (!modal) return;

            modal.querySelectorAll('.departure-item').forEach(item => {
                const startInput = item.querySelector('.departure-start-date');
                const endInput = item.querySelector('.departure-end-date');

                if (startInput && endInput) {
                    endInput.value = calculateEndDate(startInput.value, days);
                }
            });
        }

        function reIndexDepartureBlocks(tourId) {
            const container = document.getElementById(`departure_container_${tourId}`);
            if (!container) return;

            const items = container.querySelectorAll('.departure-item');

            items.forEach((item, index) => {
                const title = item.querySelector('h6');
                if (title) title.textContent = `Đợt khởi hành #${index + 1}`;

                item.querySelectorAll('input, select, textarea').forEach(field => {
                    const name = field.getAttribute('name');
                    if (name) {
                        field.setAttribute(
                            'name',
                            name.replace(/departures\[\d+\]/, `departures[${index}]`)
                        );
                    }
                });
            });
        }

        document.querySelectorAll('.btn_add_include').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.tour;
                document.getElementById(`include_container_${id}`)
                    .insertAdjacentHTML('beforeend', createPolicyRow('include'));
            });
        });

        document.querySelectorAll('.btn_add_exclude').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.tour;
                document.getElementById(`exclude_container_${id}`)
                    .insertAdjacentHTML('beforeend', createPolicyRow('exclude'));
            });
        });

        document.querySelectorAll('.btn_add_departure').forEach(btn => {
            btn.addEventListener('click', function() {
                const tourId = this.dataset.tour;
                const container = document.getElementById(`departure_container_${tourId}`);
                const currentCount = container.querySelectorAll('.departure-item').length;

                container.insertAdjacentHTML('beforeend', createDepartureBlock(currentCount));
                updateAllDepartureEndDates(tourId);
            });
        });

        document.addEventListener('click', function(e) {
            const btnRemovePolicy = e.target.closest('.btn_remove_policy');
            if (btnRemovePolicy) {
                btnRemovePolicy.closest('.policy-row')?.remove();
                return;
            }

            const btnRemoveDeparture = e.target.closest('.btn_remove_departure');
            if (btnRemoveDeparture) {
                const departureItem = btnRemoveDeparture.closest('.departure-item');
                const modal = btnRemoveDeparture.closest('.modal');
                departureItem?.remove();

                if (modal) {
                    const tourId = modal.id.replace('ModalSua', '');
                    reIndexDepartureBlocks(tourId);
                    updateAllDepartureEndDates(tourId);
                }
                return;
            }
        });

        document.addEventListener('change', function(e) {
            if (e.target.matches('.departure-start-date')) {
                const modal = e.target.closest('.modal');
                if (modal) {
                    const tourId = modal.id.replace('ModalSua', '');
                    updateAllDepartureEndDates(tourId);
                }
            }
        });

        @foreach ($tours as $tour)
            (function(tourId) {
                const daysInput = document.getElementById(`duration_days_${tourId}`);
                const nightsInput = document.getElementById(`duration_nights_${tourId}`);
                const itineraryContainer = document.getElementById(`itinerary_container_${tourId}`);

                function renderDays() {
                    let days = parseInt(daysInput?.value) || 1;
                    let nights = days > 0 ? days - 1 : 0;

                    if (nightsInput) {
                        nightsInput.value = nights;
                    }

                    const currentValues = {};
                    itineraryContainer.querySelectorAll('.itinerary-day-block').forEach((block, index) => {
                        const day = index + 1;
                        currentValues[day] = {
                            title: block.querySelector('input')?.value || '',
                            content: block.querySelector('textarea')?.value || ''
                        };
                    });

                    itineraryContainer.innerHTML = '';
                    for (let i = 1; i <= days; i++) {
                        itineraryContainer.insertAdjacentHTML('beforeend', createDayBlock(i));
                        const lastBlock = itineraryContainer.lastElementChild;

                        if (currentValues[i]) {
                            lastBlock.querySelector('input').value = currentValues[i].title;
                            lastBlock.querySelector('textarea').value = currentValues[i].content;
                        }
                    }

                    updateAllDepartureEndDates(tourId);
                }

                daysInput?.addEventListener('input', renderDays);

                $(`#ModalSua${tourId}`).on('shown.bs.modal', function() {
                    renderDays();
                    updateAllDepartureEndDates(tourId);
                });
            })({{ $tour->id }});
        @endforeach
    })();
</script>
