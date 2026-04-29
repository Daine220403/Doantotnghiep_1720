@extends('admin.layout.app')

@section('content')
    <script src="{{ asset('ckeditor/ckeditor.js') }}"></script>

    <div class="container-fluid">
        <h3 class="mb-4">Thêm bài viết mới</h3>

        <form action="{{ route('admin.posts.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="card mb-4">
                <div class="card-header font-weight-bold">
                    Thông tin bài viết
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="title">Tiêu đề <span class="text-danger">*</span></label>
                            <input type="text" id="title" name="title" class="form-control @error('title') is-invalid @enderror" 
                                value="{{ old('title') }}" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="category">Danh mục <span class="text-danger">*</span></label>
                            <input type="text" id="category" name="category" class="form-control @error('category') is-invalid @enderror" 
                                value="{{ old('category') }}" placeholder="VD: Du lịch, Văn hóa,..." required>
                            @error('category')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-12 mb-3">
                            <label for="description">Mô tả ngắn <span class="text-danger">*</span></label>
                            <textarea id="description" name="description" class="form-control @error('description') is-invalid @enderror" 
                                rows="3" placeholder="Nhập mô tả ngắn cho bài viết..." required>{{ old('description') }}</textarea>
                            <small class="form-text text-muted">Tối đa 500 ký tự</small>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-12 mb-3">
                            <label for="content">Nội dung <span class="text-danger">*</span></label>
                            <textarea id="content" name="content" class="form-control @error('content') is-invalid @enderror" 
                                rows="8" required>{{ old('content') }}</textarea>
                            <script>
                                CKEDITOR.replace('content', {
                                    filebrowserUploadUrl: "{{ route('upload.image') }}",
                                    filebrowserUploadMethod: 'post',
                                });
                            </script>
                            @error('content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="image">Hình ảnh</label>
                            <input type="file" id="image" name="image" class="form-control @error('image') is-invalid @enderror" 
                                accept="image/*">
                            <small class="form-text text-muted">Định dạng: JPEG, PNG, JPG, GIF. Tối đa 5MB</small>
                            @error('image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Lưu bài viết
            </button>
            <a href="{{ route('admin.posts.index') }}" class="btn btn-secondary">Quay lại</a>
        </form>
    </div>
@endsection
