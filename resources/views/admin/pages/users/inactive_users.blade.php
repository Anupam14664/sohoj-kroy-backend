@extends('admin.layouts.app')

@section('content')
<div class="card">
    <div class="card-header">
        <div class="card-title">Inactive Users</div>
    </div>
    <div class="card-body table-responsive">
        <table class="table table-striped table-hover table-head-bg-primary mt-4">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Name</th>
                    <th scope="col">Username</th>
                    <th scope="col">Email</th>
                    <th scope="col">Wallet Balance</th>
                    <th scope="col">Reward Tokens</th>
                    <th scope="col">Referred By</th>
                    <th scope="col">Status</th>
                    <th scope="col">Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $index => $user)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->username }}</td>
                    <td>{{ $user->email }}</td>
                    <td>${{ $user->wallet->wallet_balance ?? 0 }}</td>
                    <td>{{ $user->wallet->reward_point ?? 0 }}</td>
                    <td>{{ $user->referredBy ? $user->referredBy->username : 'N/A' }}</td>
                    <td>
                        <span class="badge
                        @if ($user->is_active)
                            bg-success
                        @else
                            bg-danger
                        @endif
                    ">
                        {{ $user->is_active ? 'Active' : 'Inactive' }}
                    </span>


                    </td>
                    <td>{{ $user->created_at->format('Y-m-d H:i') }}</td>
                </tr>
            @endforeach
            {{ $users->links('admin.layouts.partials.__pagination') }}
            </tbody>
        </table>
    </div>
</div>
@endsection