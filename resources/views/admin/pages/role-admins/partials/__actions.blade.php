<div class="btn-group btn-group-sm" role="group" style="gap: 5px">
    <div>
        @if (!$review->is_approved)
        <form action="{{ route('admin.reviews.approve', $review) }}" method="POST"
            class="d-inline  m-0 p-0 border-none bg-none" style="width: 0px; height:0px;">
                @csrf
                <button type="submit" class="btn btn-sm btn-success" title="Approve">
                    <i class="fas fa-check"></i>
                </button>
            </form>
        @endif
    </div>


    <div>
        {{-- <form width="0px"  action="{{ route('admin.reviews.destroy', $review) }}"
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
                data-action="{{ route('admin.reviews.destroy', $review) }}"
                data-bs-toggle="modal"
                data-bs-target="#deleteConfirmModal">
            <i class="fas fa-trash"></i>
        </button>
    </div>

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

