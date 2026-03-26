@extends('admin.layout.app')

@section('content')
    <script src="{{ asset('ckeditor/ckeditor.js') }}"></script>

    <div class="container-fluid">
        <h3 class="mb-4">Thêm Tour Du Lịch</h3>

        <form action="{{ route('admin.mana-tour.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- ================= THÔNG TIN CHUNG ================= -->
            <div class="card mb-4">
                <div class="card-header font-weight-bold">
                    Thông tin chung
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Tên tour</label>
                            <input type="text" name="title" class="form-control">
                        </div>
                        @error('title')
                            <div class="col-md-12 mb-3">
                                <div class="alert alert-danger">{{ $message }}</div>
                            </div>
                        @enderror

                        <div class="col-md-6 mb-3">
                            <label>Slug</label>
                            <input type="text" name="slug" class="form-control" readonly>
                        </div>
                        @error('slug')
                            <div class="col-md-12 mb-3">
                                <div class="alert alert-danger">{{ $message }}</div>
                            </div>
                        @enderror

                        <div class="col-md-6 mb-3">
                            <label>Loại tour</label>
                            <select name="tour_type" class="form-control">
                                <option value="domestic">Trong nước</option>
                                <option value="international">Quốc tế</option>
                            </select>
                        </div>
                        @error('tour_type')
                            <div class="col-md-12 mb-3">
                                <div class="alert alert-danger">{{ $message }}</div>
                            </div>
                        @enderror

                        <div class="col-md-6 mb-3">
                            <label>Điểm khởi hành</label>
                            <input type="text" name="departure_location" class="form-control">
                        </div>
                        @error('departure_location')
                            <div class="col-md-12 mb-3">
                                <div class="alert alert-danger">{{ $message }}</div>
                            </div>
                        @enderror

                        <div class="col-md-6 mb-3">
                            <label>Điểm đến</label>
                            <input type="text" name="destination_text" class="form-control">
                        </div>
                        @error('destination_text')
                            <div class="col-md-12 mb-3">
                                <div class="alert alert-danger">{{ $message }}</div>
                            </div>
                        @enderror
                        <div class="col-md-6 mb-3">
                            <label>Phương tiện</label>
                            <select name="transport" id="transport" class="form-control">
                                <option value="bus">Xe khách</option>
                                <option value="plane">Máy bay</option>
                                <option value="train">Tàu hỏa</option>
                                <option value="car">Ô tô</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Giá từ</label>
                            <input type="number" name="base_price_from" class="form-control">
                        </div>
                        @error('base_price_from')
                            <div class="col-md-12 mb-3">
                                <div class="alert alert-danger">{{ $message }}</div>
                            </div>
                        @enderror

                        <div class="col-md-6 mb-3">
                            <label>Trạng thái</label>
                            <select name="status" class="form-control">
                                <option value="draft">Draft (Nháp)</option>
                                <option value="published">Published (Đang bán)</option>
                                <option value="hidden">Hidden (Ẩn)</option>
                            </select>
                        </div>
                        @error('status')
                            <div class="col-md-12 mb-3">
                                <div class="alert alert-danger">{{ $message }}</div>
                            </div>
                        @enderror

                        <div class="col-md-12 mb-3">
                            <label>Mô tả</label>
                            <textarea name="description" id="description_editor" rows="4" class="form-control"></textarea>
                        </div>
                        @error('description')
                            <div class="col-md-12 mb-3">
                                <div class="alert alert-danger">{{ $message }}</div>
                            </div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- ================= BAO GỒM / KHÔNG BAO GỒM ================= -->
            <div class="card mb-4">
                <div class="card-header font-weight-bold">
                    Bao gồm / Không bao gồm
                </div>

                <div class="card-body">
                    <div class="row">
                        <!-- Bao gồm -->
                        <div class="col-md-6">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <label class="mb-0 font-weight-bold">Bao gồm</label>
                                <button type="button" class="btn btn-sm btn-success" id="btn_add_include">
                                    + Thêm
                                </button>
                            </div>

                            <div id="include_container" class="border rounded p-2"></div>
                        </div>

                        <!-- Không bao gồm -->
                        <div class="col-md-6">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <label class="mb-0 font-weight-bold">Không bao gồm</label>
                                <button type="button" class="btn btn-sm btn-success" id="btn_add_exclude">
                                    + Thêm
                                </button>
                            </div>

                            <div id="exclude_container" class="border rounded p-2"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ================= ẢNH TOUR ================= -->
            <div class="card mb-4">
                <div class="card-header font-weight-bold">
                    Ảnh tour
                </div>

                <div class="card-body">
                    <input type="file" name="images[]" accept="image/*" multiple class="form-control">
                </div>
            </div>

            <!-- ================= THỜI LƯỢNG ================= -->
            <div class="card mb-4">
                <div class="card-header font-weight-bold">
                    Thời lượng tour
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <label>Số ngày</label>
                            <input type="number" id="duration_days" name="duration_days" class="form-control"
                                min="1" value="1">
                        </div>

                        <div class="col-md-3">
                            <label>Số đêm (tự tính)</label>
                            <input type="number" id="duration_nights" name="duration_nights" class="form-control"
                                readonly value="0">
                            <small class="text-muted">Tự động = số ngày - 1</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ================= LỊCH TRÌNH ================= -->
            <div class="card mb-4">
                <div class="card-header font-weight-bold">
                    Lịch trình
                </div>

                <div class="card-body">
                    <div id="itinerary_container"></div>
                </div>
            </div>

            <!-- ================= LỊCH KHỞI HÀNH ================= -->
            <div class="card mb-4">
                <div class="card-header font-weight-bold">
                    Lịch khởi hành
                </div>

                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <small class="text-muted">Có thể tạo nhiều đợt khởi hành cho 1 tour</small>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="btn_add_departure">
                            <i class="fas fa-plus"></i> Thêm lịch khởi hành
                        </button>
                    </div>

                    <div id="departure_container"></div>
                </div>
            </div>

            <button class="btn btn-primary">
                <i class="fas fa-save"></i> Lưu tour
            </button>
        </form>
    </div>
    
    <script>
        CKEDITOR.replace('description_editor');

        // =========================
        // A. DOM
        // =========================
        const daysInput = document.getElementById('duration_days');
        const nightsInput = document.getElementById('duration_nights');
        const itineraryContainer = document.getElementById('itinerary_container');

        const includeContainer = document.getElementById('include_container');
        const excludeContainer = document.getElementById('exclude_container');

        const btnAddInclude = document.getElementById('btn_add_include');
        const btnAddExclude = document.getElementById('btn_add_exclude');

        const departureContainer = document.getElementById('departure_container');
        const btnAddDeparture = document.getElementById('btn_add_departure');

        const titleInput = document.querySelector('input[name="title"]');
        const slugInput = document.querySelector('input[name="slug"]');

        // =========================
        // B. HÀM DÙNG CHUNG
        // =========================
        function slugify(str) {
            return str
                .toLowerCase()
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .replace(/đ/g, 'd')
                .replace(/[^a-z0-9\s-]/g, '')
                .trim()
                .replace(/\s+/g, '-');
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

        // =========================
        // C. LỊCH TRÌNH
        // =========================
        function createDayBlock(day, title = '', content = '') {
            return `
                <div class="border p-3 mb-3 rounded itinerary-day-block">
                    <label class="font-weight-bold">Ngày ${day}</label>

                    <input type="text"
                           name="itineraries[${day}][title]"
                           class="form-control mb-2"
                           placeholder="Tiêu đề ngày ${day}"
                           value="${title}">

                    <textarea name="itineraries[${day}][content]"
                              class="form-control"
                              placeholder="Nội dung ngày ${day}..."
                              rows="3">${content}</textarea>
                </div>
            `;
        }

        function syncNightsAndRenderItinerary() {
            let days = parseInt(daysInput.value) || 1;

            if (days < 1) days = 1;
            daysInput.value = days;

            const nights = Math.max(days - 1, 0);
            nightsInput.value = nights;

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
                const title = currentValues[i]?.title || '';
                const content = currentValues[i]?.content || '';
                itineraryContainer.insertAdjacentHTML('beforeend', createDayBlock(i, title, content));
            }

            updateAllDepartureEndDates();
        }

        // =========================
        // D. BAO GỒM / KHÔNG BAO GỒM
        // =========================
        function createPolicyRow(type) {
            const name = type === 'include' ? 'policies[include][]' : 'policies[exclude][]';
            const placeholder = type === 'include' ?
                'Nhập mục bao gồm...' :
                'Nhập mục không bao gồm...';

            return `
                <div class="d-flex mb-2 policy-row">
                    <input type="text" name="${name}" class="form-control" placeholder="${placeholder}">
                    <button type="button" class="btn btn-danger ml-2 btn_remove_policy">X</button>
                </div>
            `;
        }

        // =========================
        // E. LỊCH KHỞI HÀNH NHIỀU KHỐI
        // =========================
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
                            <input type="date" name="departures[${index}][start_date]"
                                class="form-control departure-start-date">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label>Ngày về</label>
                            <input type="date" name="departures[${index}][end_date]"
                                class="form-control departure-end-date" readonly>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Điểm tập trung</label>
                            <input type="text" name="departures[${index}][meeting_point]"
                                class="form-control" placeholder="Nhập điểm tập trung">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label>Tổng chỗ</label>
                            <input type="number" min="1" name="departures[${index}][capacity_total]"
                                class="form-control" value="0">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label>Đã đặt</label>
                            <input type="number" min="0" name="departures[${index}][capacity_booked]"
                                class="form-control" value="0">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label>Giá người lớn</label>
                            <input type="number" min="0" name="departures[${index}][price_adult]"
                                class="form-control" value="0">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label>Giá trẻ em</label>
                            <input type="number" min="0" name="departures[${index}][price_child]"
                                class="form-control" value="0">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label>Giá trẻ nhỏ</label>
                            <input type="number" min="0" name="departures[${index}][price_infant]"
                                class="form-control" value="0">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label>Giá em bé</label>
                            <input type="number" min="0" name="departures[${index}][price_youth]"
                                class="form-control" value="0">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label>Phụ thu phòng đơn</label>
                            <input type="number" min="0" name="departures[${index}][single_room_surcharge]"
                                class="form-control" value="0">
                        </div>

                        <div class="col-md-12 mb-3 mt-2">
                            <label>Trạng thái</label>
                            <select name="departures[${index}][status]" class="form-control">
                                <option value="draft">draft</option>
                                <option value="open">open</option>
                                <option value="closed">closed</option>
                                <option value="sold_out">sold_out</option>
                                <option value="cancelled">cancelled</option>
                                <option value="confirmed">confirmed</option>
                                <option value="completed">completed</option>
                            </select>
                        </div>
                    </div>
                </div>
            `;
        }

        function updateAllDepartureEndDates() {
            const days = parseInt(daysInput.value || 0);

            departureContainer.querySelectorAll('.departure-item').forEach(item => {
                const startInput = item.querySelector('.departure-start-date');
                const endInput = item.querySelector('.departure-end-date');

                if (startInput && endInput) {
                    endInput.value = calculateEndDate(startInput.value, days);
                }
            });
        }

        function reIndexDepartureBlocks() {
            const items = departureContainer.querySelectorAll('.departure-item');

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

        // =========================
        // F. EVENTS
        // =========================
        daysInput.addEventListener('input', syncNightsAndRenderItinerary);

        btnAddInclude.addEventListener('click', function() {
            includeContainer.insertAdjacentHTML('beforeend', createPolicyRow('include'));
        });

        btnAddExclude.addEventListener('click', function() {
            excludeContainer.insertAdjacentHTML('beforeend', createPolicyRow('exclude'));
        });

        btnAddDeparture.addEventListener('click', function() {
            const currentCount = departureContainer.querySelectorAll('.departure-item').length;
            departureContainer.insertAdjacentHTML('beforeend', createDepartureBlock(currentCount));
            updateAllDepartureEndDates();
        });

        document.addEventListener('click', function(e) {
            const btnRemovePolicy = e.target.closest('.btn_remove_policy');
            if (btnRemovePolicy) {
                btnRemovePolicy.closest('.policy-row')?.remove();
                return;
            }

            const btnRemoveDeparture = e.target.closest('.btn_remove_departure');
            if (btnRemoveDeparture) {
                btnRemoveDeparture.closest('.departure-item')?.remove();
                reIndexDepartureBlocks();
                updateAllDepartureEndDates();
                return;
            }
        });

        document.addEventListener('change', function(e) {
            if (e.target.matches('.departure-start-date')) {
                updateAllDepartureEndDates();
            }
        });

        titleInput.addEventListener('input', function() {
            slugInput.value = slugify(this.value);
        });

        // =========================
        // G. KHỞI TẠO BAN ĐẦU
        // =========================
        syncNightsAndRenderItinerary();

        if (includeContainer.children.length === 0) {
            includeContainer.insertAdjacentHTML('beforeend', createPolicyRow('include'));
        }

        if (excludeContainer.children.length === 0) {
            excludeContainer.insertAdjacentHTML('beforeend', createPolicyRow('exclude'));
        }

        if (departureContainer.children.length === 0) {
            departureContainer.insertAdjacentHTML('beforeend', createDepartureBlock(0));
        }

        updateAllDepartureEndDates();
    </script>
@endsection
