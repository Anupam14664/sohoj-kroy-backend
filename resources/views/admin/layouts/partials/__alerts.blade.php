<div class="mt-4">
    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    {{-- @else
    <div class="alert alert-danger">{{ session('error') }}</div> --}}
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
  {{ session('error') }}
  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
    <span aria-hidden="true">&times;</span>
  </button>
</div>
@endif

</div>