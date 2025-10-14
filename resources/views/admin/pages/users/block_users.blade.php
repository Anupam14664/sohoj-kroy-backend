@extends('admin.layouts.app')

@section('content')
<div class="card">
    <div class="card-header">
        <div class="card-title">Block Users</div>
    </div>
    <div class="card-body table-responsive">
        <table class="table table-striped table-hover table-head-bg-primary mt-4">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Date</th>
                    <th scope="col">Name</th>
                    <th scope="col">Username</th>
                    <th scope="col">Email</th>
                    <th scope="col">Wallet Balance</th>
                    <th scope="col">Reward Tokens</th>
                    <th scope="col">Referred By</th>
                    <th scope="col">Status</th>
                    <th scope="col">Is Block</th>
                    <th scope="col">Wallet Status</th>
                    <th scope="col">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $index => $user)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $user->created_at->format('Y-m-d H:i') }}</td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->username }}</td>
                    <td>{{ $user->email }}</td>
                    <td>${{ $user->wallet->wallet_balance ?? 0 }}</td>
                    <td>{{ $user->wallet->reward_point ?? 0 }}</td>
                    <td>{{ $user->referredBy ? $user->referredBy->username : 'N/A' }}</td>
                    <td>
                        <span class="badge {{ $user->is_active ? 'bg-success' : 'bg-danger' }}">
                            {{ $user->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td>
                        <span class="badge {{ $user->is_block ? 'bg-danger' : 'bg-success' }}">
                            {{ $user->is_block ? 'Blocked' : 'Unblock' }}
                        </span>
                    </td>
                    <td>
                        <span class="badge {{ $user->wallet && $user->wallet->is_active ? 'bg-success' : 'bg-danger' }}">
                            {{ $user->wallet && $user->wallet->is_active ? 'Active' : 'Freezed' }}
                        </span>
                    </td>
                    <td>
                        <button type="button" class="btn updateStatusBtn"
                            data-id="{{ $user->id }}"
                            data-name="{{ $user->name }}"
                            data-email="{{ $user->email }}"
                            data-block="{{ $user->is_block }}"
                            data-wallet="{{ $user->wallet ? $user->wallet->is_active : 0 }}"
                            data-toggle="modal" data-target="#actionModal">
                            <i class="fas fa-edit"></i>
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        {{ $users->links('admin.layouts.partials.__pagination') }}
    </div>
</div>

@include('admin.modal.userblockmodal')

@endsection
