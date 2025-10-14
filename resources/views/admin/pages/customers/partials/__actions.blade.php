<div class="btn-group btn-group-sm" role="group">
    <a href="{{ route('admin.orders.show', $order->id) }}"
       class="btn btn-info p-1 mx-1" title="View">
        <i class="fas fa-eye"></i>
    </a>

    <a href="{{ route('admin.orders.edit', $order->id) }}"
       class="btn btn-primary p-1 mx-1" title="Edit">
        <i class="fas fa-edit bg-none"></i>
    </a>

    {{-- <form width="0px"  action="{{ route('admin.orders.destroy', $order->id) }}"
          method="POST" class="d-inline  m-0 p-0 border-none bg-none" style="width: 0px; height:0px;">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger p-0 py-1 px-2 border-none"
                title="Delete" onclick="return confirm('Are you sure?')">
            <i class="fas fa-trash"></i>
        </button>
    </form> --}}
</div>