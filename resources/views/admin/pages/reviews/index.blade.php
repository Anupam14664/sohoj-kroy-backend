@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Product Reviews</h4>
                    <a href="{{ route('admin.reviews.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create Review
                    </a>
                </div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <div class="d-flex justify-content-end mb-3 ">
                            <form action="{{ route('admin.reviews.index') }}" method="GET" class="form-inline d-flex" style=" background:none;border:none;margin:0">
                                <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search..." style="width: 250px;">
                                <button type="submit" class="btn btn-primary ml-2">
                                    <i class="fas fa-search"></i>
                                </button>
                                @if(request('search'))
                                    <a href="{{ route('admin.reviews.index') }}" class="btn btn-secondary ml-2" style="margin-left: 10px">X</a>
                                @endif
                            </form>
                        </div>

                        <table class="table table-striped table-hover table-head-bg-primary mt-4">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Product</th>
                                    <th>User</th>
                                    <th>Rating</th>
                                    <th>Review</th>
                                    <th>Images</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($reviews as $review)
                                    <tr>
                                        <td>{{ $review->product->name }}</td>
                                        <td>{{ $review->user->name }}</td>
                                        <td>
                                            @for ($i = 1; $i <= 5; $i++)
                                                @if ($i <= $review->rating)
                                                    <i class="fas fa-star text-warning"></i>
                                                @else
                                                    <i class="far fa-star text-warning"></i>
                                                @endif
                                            @endfor
                                        </td>
                                        <td>{{ Str::limit($review->description, 100) }}</td>
                                        <td>
                                            @foreach ($review->images as $image)
                                                <a href="{{ $image->image_path }}" target="_blank" class="mr-2">
                                                    <img src="{{ $image->image_path }}" width="50" class="img-thumbnail">
                                                </a>
                                            @endforeach
                                        </td>
                                        <td>
                                            @if ($review->is_approved)
                                                <span class="badge badge-success">Approved</span>
                                            @else
                                                <span class="badge badge-warning">Pending</span>
                                            @endif
                                        </td>
                                        <td>
                                            @include('admin.pages.reviews.partials.__actions')
                                        </td>
                                        {{-- <td class="d-flex">
                                            @if (!$review->is_approved)
                                                <form action="{{ route('admin.reviews.approve', $review) }}" method="POST" class="mr-2">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success" title="Approve">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            <form action="{{ route('admin.reviews.destroy', $review) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this review?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td> --}}
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">No reviews found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        @include('admin.modal.deletemodal')
                    </div>

                    <div class="d-flex justify-content-center mt-4">
                        {{ $reviews->appends(['search' => request('search')])->links('admin.layouts.partials.__pagination') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection