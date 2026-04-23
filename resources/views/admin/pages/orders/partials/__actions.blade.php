<div class="btn-group btn-group-sm" role="group">
    <a href="{{ route('admin.orders.show', $order->id) }}"
       class="btn btn-info p-1 mx-1" title="View">
        <i class="fas fa-eye"></i>
    </a>

    <a href="{{ route('admin.orders.edit', $order->id) }}"
       class="btn btn-primary p-1 mx-1" title="Edit">
        <i class="fas fa-edit bg-none"></i>
    </a>
    @if($order->status !== 'incomplete')
        <a href="{{ route('admin.orders.download', $order) }}"
        class="btn btn-secondary p-1 mx-1" title="Download PDF">
        <i class="fas fa-file-pdf"></i>
        </a>
    @endif
        @if($order->status == 'cancelled')
        <form action="{{ route('admin.orders.destroy', ['order' => $order->id]) }}"
            method="POST"
            class="d-inline m-0 p-0">
            @csrf
            @method('DELETE')

            <button type="submit"
                    class="btn btn-danger btn-sm p-1"
                    onclick="return confirm('Are you sure?')"
                    title="Delete">
                <i class="fas fa-trash"></i>
            </button>
        </form>
        @endif

</div>
