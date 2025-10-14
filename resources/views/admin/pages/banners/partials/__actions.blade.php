<div class="btn-group btn-group-sm" role="group">
    {{-- <a href="{{ route('admin.categories.show', $category->id) }}"
       class="btn btn-info p-1 mx-1" title="View">
        <i class="fas fa-eye"></i>
    </a> --}}

    <a href="{{ route('admin.banners.edit', $banner->id) }}"
       class="btn btn-primary p-1 mx-1" title="Edit">
        <i class="fas fa-edit bg-none"></i>
    </a>

    {{-- <form width="0px"  action="{{ route('admin.banners.destroy', $banner->id) }}"
          method="POST" class="d-inline  m-0 p-0 border-none bg-none" style="width: 0px; height:0px;">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger p-0 py-1 px-2 border-none"
                title="Delete" onclick="return confirm('Are you sure?')">
            <i class="fas fa-trash"></i>
        </button>
    </form> --}}
    <button type="button" class="btn btn-danger p-0 py-1 px-2 border-none delete-btn"
        title="Delete"
        data-action="{{ route('admin.banners.destroy', $banner->id) }}"
        data-bs-toggle="modal"
        data-bs-target="#deleteConfirmModal">
    <i class="fas fa-trash"></i>
    </button>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const deleteButtons = document.querySelectorAll('.delete-btn');
        const deleteForm = document.getElementById('deleteForm');

        deleteButtons.forEach(button => {
            button.addEventListener('click', function () {
                let action = this.getAttribute('data-action');
                deleteForm.setAttribute('action', action);
            });
        });
    });
</script>
