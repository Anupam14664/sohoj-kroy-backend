@extends('admin.layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Review Details</h3>
                        <div class="card-tools">
                            <a href="{{ route('admin.reviews.index') }}" class="btn btn-secondary">Back to List</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h4>Review Information</h4>
                                <table class="table table-bordered">
                                    <tr>
                                        <th>ID</th>
                                        <td>{{ $review->id }}</td>
                                    </tr>
                                    <tr>
                                        <th>Product</th>
                                        <td>{{ $review->product->name }}</td>
                                    </tr>
                                    <tr>
                                        <th>Reviewer</th>
                                        <td>
                                            @if($review->user_id)
                                                {{ $review->user->name }} (Registered User)
                                            @else
                                                {{ $review->guest_name }} (Guest)
                                                @if($review->guest_email)
                                                    <br>{{ $review->guest_email }}
                                                @endif
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Rating</th>
                                        <td>
                                            @for($i = 1; $i <= 5; $i++)
                                                @if($i <= $review->rating)
                                                    <i class="fas fa-star text-warning"></i>
                                                @else
                                                    <i class="far fa-star text-warning"></i>
                                                @endif
                                            @endfor
                                            ({{ $review->rating }}/5)
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Status</th>
                                        <td>
                                            @if($review->is_approved)
                                                <span class="badge badge-success">Approved</span>
                                            @else
                                                <span class="badge badge-warning">Pending</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Date</th>
                                        <td>{{ $review->created_at->format('M d, Y H:i') }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h4>Review Content</h4>
                                <div class="border p-3">
                                    {{ $review->comment ?? 'No comment provided' }}
                                </div>

                                @if($review->images->count() > 0)
                                    <h4 class="mt-4">Review Images</h4>
                                    <div class="row">
                                        @foreach($review->images as $image)
                                            <div class="col-md-4 mb-3">
                                                <img src="{{ asset('storage/' . $image->path) }}" alt="Review image" class="img-thumbnail">
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="mt-4">
                            @if(!$review->is_approved)
                                <a href="{{ route('admin.reviews.approve', $review) }}" class="btn btn-success">
                                    <i class="fas fa-check"></i> Approve Review
                                </a>
                            @endif
                            <a href="{{ route('admin.reviews.edit', $review) }}" class="btn btn-primary">
                                <i class="fas fa-edit"></i> Edit Review
                            </a>
                            <form action="{{ route('admin.reviews.destroy', $review) }}" method="POST" style="display:inline-block;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure?')">
                                    <i class="fas fa-trash"></i> Delete Review
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection