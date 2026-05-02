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
        <button class="btn btn-danger btn-sm delete-btn"
                data-url="{{ route('admin.orders.destroy', $order->id) }}">
            <i class="fas fa-trash"></i>
        </button>
        @endif

</div>
