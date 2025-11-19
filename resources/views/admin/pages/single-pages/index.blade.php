@extends('admin.layouts.app')

@section('content')
<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">All Landing Pages</h4>
        <a class="btn btn-primary btn-sm" href="{{ route('admin.pages.create') }}">+ Create New Page</a>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <table class="table table-striped table-hover table-head-bg-primary mt-4">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Page Name</th>
                        <th>Page Url</th>
                        <th>Status</th>
                        <th>Last Updated</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pages as $page)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $page->name }}</td>
                            <td>
                                @php
                                    $fullUrl = config('app.main_domain') . '/landing/' . $page->slug;

                                    $shortUrl = substr($fullUrl, 0, 15) . '....' . substr($fullUrl, -20);
                                @endphp

                            <span>{{ $shortUrl }}</span>
                                <button class="btn btn-sm copy-btn" data-url="{{ $fullUrl }}" title="Copy URL" style="border:none;">
                                    <i class="fa fa-copy"></i>
                                </button>
                            </td>


                            <td>
                                @if($page->status == 1)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif

                            </td>
                            <td>{{ $page->updated_at->format('d M, Y h:i A') }}</td>
                            <td>
                              @include('admin.pages.single-pages.partials.__actions')
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-3">No pages found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer">
            {{ $pages->links() }}
        </div>
    </div>

</div>
@endsection

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.copy-btn').forEach(button => {
        button.addEventListener('click', function () {
            const url = this.getAttribute('data-url');
            const icon = this.querySelector('i');

            // Copy to clipboard
            navigator.clipboard.writeText(url);

            // Change icon to check
            icon.classList.remove('fa-copy');
            icon.classList.add('fa-check');
            icon.style.color = "green";

            // After 1 sec restore icon
            setTimeout(() => {
                icon.classList.remove('fa-check');
                icon.classList.add('fa-copy');
                icon.style.color = "";
            }, 1000);
        });
    });
});
</script>

