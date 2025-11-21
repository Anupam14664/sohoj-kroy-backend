<!-- Sidebar -->
<div class="sidebar" data-background-color="dark">
	<div class="sidebar-logo">
		<div class="logo-header" data-background-color="dark">
			<a href="{{route('admin.dashboard')}}" class="logo">
				@if($generalSettings->logo)
					<img src="{{ asset('storage/' . str_replace('public/', '', $generalSettings->logo)) }}" alt="{{ $generalSettings->app_name }}" class="navbar-brand" height="50">
				@else
					<h1>{{ $generalSettings->app_name ?? 'App Name' }}</h1>
				@endif
			</a>
			<div class="nav-toggle">
				<button class="btn btn-toggle toggle-sidebar"><i class="gg-menu-right"></i></button>
				<button class="btn btn-toggle sidenav-toggler"><i class="gg-menu-left"></i></button>
			</div>
			<button class="topbar-toggler more"><i class="gg-more-vertical-alt"></i></button>
		</div>
	</div>

	<div class="sidebar-wrapper scrollbar scrollbar-inner">
		<div class="sidebar-content">
			<ul class="nav nav-secondary">

				{{-- Dashboard --}}
				@can('view dashboard')
				<li class="nav-item active">
					<a href="{{route('admin.dashboard')}}">
						<i class="fas fa-home"></i>
						<p>Dashboard</p>
					</a>
				</li>
				@endcan

				{{-- Orders --}}
				@can('manage orders')
				<li class="nav-item">
					<a data-bs-toggle="collapse" href="#orders">
						<i class="fas fa-shopping-cart"></i>
						<p>Orders</p>
						<span class="caret"></span>
					</a>
					<div class="collapse" id="orders">
						<ul class="nav nav-collapse">
							<li><a href="{{route('admin.orders.index')}}"><span class="sub-item">All Orders</span></a></li>
							<li><a href="{{ route('admin.orders.index', ['status' => 'pending']) }}"><span class="sub-item">Pending</span>
                                <span class="badge badge-warning float-right">{{ App\Models\Order::where('status', 'pending')->count() }}</span>
                            </a></li>
							<li><a href="{{ route('admin.orders.index', ['status' => 'hold']) }}"><span class="sub-item">Hold</span>
                            <span class="badge badge-warning float-right">
                                                {{ App\Models\Order::where('status', 'hold')->count() }}
                                            </span>
                            </a></li>
							<li><a href="{{ route('admin.orders.index', ['status' => 'processing']) }}"><span class="sub-item">Order Confirmed</span>
                                <span class="badge badge-info float-right">
                                                {{ App\Models\Order::where('status', 'processing')->count() }}
                                            </span>
                            </a></li>
							<li><a href="{{ route('admin.orders.index', ['status' => 'shipped']) }}"><span class="sub-item">Ready To Shipped</span>
                            <span class="badge badge-primary float-right">
                                                {{ App\Models\Order::where('status', 'shipped')->count() }}
                                            </span>
                            </a></li>
							<li><a href="{{ route('admin.orders.index', ['status' => 'courier_delivered']) }}"><span class="sub-item">Courier Delivered</span>

                            <span class="badge badge-warning float-right">
                                                {{ App\Models\Order::where('status', 'courier_delivered')->count() }}
                                            </span>
                            </a></li>
							<li><a href="{{ route('admin.orders.index', ['status' => 'delivered']) }}"><span class="sub-item">Delivered Orders</span>
                            <span class="badge badge-success float-right">
                                                {{ App\Models\Order::where('status', 'delivered')->count() }}
                                            </span>
                            </a></li>
							<li><a href="{{ route('admin.orders.index', ['status' => 'cancelled']) }}"><span class="sub-item">Cancelled Orders</span>

                            <span class="badge badge-danger float-right">
                                                {{ App\Models\Order::where('status', 'cancelled')->count() }}
                                            </span>
                            </a></li>
						</ul>
					</div>
				</li>

				<li class="nav-item">
					<a href="{{route('admin.orders.incomplete')}}">
						<i class="fas fa-exclamation-circle"></i>
						<p>Incomplete Orders</p>
                        <span class="badge badge-secondary float-right">
                                    {{ App\Models\Order::where('status', 'incomplete')->count() }}
                                </span>
					</a>
				</li>

				<li class="nav-item">
					<a href="{{route('admin.orders.shipped')}}">
						<i class="fas fa-truck"></i>
						<p>Courier Orders</p>
					</a>
				</li>
				@endcan

				{{-- Customers --}}
				@can('manage customers')
				<li class="nav-item">
					<a href="{{route('admin.customers.index')}}">
						<i class="fas fa-user-tag"></i>
						<p>Customers</p>
					</a>
				</li>
				<li class="nav-item">
					<a href="{{route('admin.customers.blocked')}}">
						<i class="fas fa-user-lock"></i>
						<p>Blocked Customers</p>
					</a>
				</li>
				@endcan

				{{-- Products --}}
				@can('manage products')
				<li class="nav-item">
					<a data-bs-toggle="collapse" href="#products">
						<i class="fas fa-box-open"></i>
						<p>Products</p>
						<span class="caret"></span>
					</a>
					<div class="collapse" id="products">
						<ul class="nav nav-collapse">
							<li><a href="{{route('admin.products.index')}}"><span class="sub-item">All Products</span></a></li>
							<li><a href="{{route('admin.products.create')}}"><span class="sub-item">Add Products</span></a></li>
						</ul>
					</div>
				</li>
				@endcan

				{{-- Variant Settings --}}
				@can('manage variants')
				<li class="nav-item">
					<a data-bs-toggle="collapse" href="#variantSettings">
						<i class="fas fa-sliders-h"></i>
						<p>Variant Settings</p>
						<span class="caret"></span>
					</a>
					<div class="collapse" id="variantSettings">
						<ul class="nav nav-collapse">
							<li><a href="{{ route('admin.categories.index') }}"><span class="sub-item">Categories</span></a></li>
							<li><a href="{{ route('admin.colors.index') }}"><span class="sub-item">Colors</span></a></li>
							<li><a href="{{ route('admin.sizes.index') }}"><span class="sub-item">Sizes</span></a></li>
							<li><a href="{{ route('admin.coupons.index') }}"><span class="sub-item">Coupons</span></a></li>
						</ul>
					</div>
				</li>
				@endcan

				{{-- Frontend --}}
				@can('manage frontend')
				<li class="nav-item">
					<a data-bs-toggle="collapse" href="#frontendSettings">
						<i class="fas fa-cogs"></i>
						<p>Frontend Settings</p>
						<span class="caret"></span>
					</a>
					<div class="collapse" id="frontendSettings">
						<ul class="nav nav-collapse">
							<li><a href="{{ route('admin.homepage-sections.index') }}"><span class="sub-item">Home Section</span></a></li>
							<li><a href="{{ route('admin.banners.index') }}"><span class="sub-item">Banners</span></a></li>
						</ul>
					</div>
				</li>
				@endcan

				{{-- Delivery --}}
				@can('manage delivery')
				<li class="nav-item">
					<a data-bs-toggle="collapse" href="#delivery-options">
						<i class="fas fa-shipping-fast"></i>
						<p>Delivery Options</p>
						<span class="caret"></span>
					</a>
					<div class="collapse" id="delivery-options">
						<ul class="nav nav-collapse">
							<li><a href="{{route('admin.delivery-options.index')}}"><span class="sub-item">All Options</span></a></li>
							<li><a href="{{route('admin.delivery-options.create')}}"><span class="sub-item">Add Delivery Options</span></a></li>
						</ul>
					</div>
				</li>
				@endcan

				{{-- Couriers --}}
				@can('manage couriers')
				<li class="nav-item">
					<a data-bs-toggle="collapse" href="#couriers">
						<i class="fas fa-dolly"></i>
						<p>Couriers Management</p>
						<span class="caret"></span>
					</a>
					<div class="collapse" id="couriers">
						<ul class="nav nav-collapse">
							<li><a href="{{route('admin.couriers.index')}}"><span class="sub-item">Couriers List</span></a></li>
							<li><a href="{{route('admin.couriers.create')}}"><span class="sub-item">Add Courier</span></a></li>
						</ul>
					</div>
				</li>
				@endcan

                {{-- Pages --}}
                @can('manage pages')
                <li class="nav-item">
                    <a data-bs-toggle="collapse" href="#pages">
                        <i class="fas fa-file-alt"></i>
                        <p>Landing Pages</p>
                        <span class="caret"></span>
                    </a>
                    <div class="collapse" id="pages">
                        <ul class="nav nav-collapse">
                            <li><a href="{{ route('admin.pages.index') }}"><span class="sub-item">All Pages</span></a></li>
                            <li><a href="{{ route('admin.pages.create') }}"><span class="sub-item">Add Page</span></a></li>
                        </ul>
                    </div>
                </li>
                @endcan


				{{-- Reviews --}}
				@can('manage reviews')
				<li class="nav-item">
					<a data-bs-toggle="collapse" href="#reviews">
						<i class="fas fa-comments"></i>
						<p>Reviews</p>
						<span class="caret"></span>
					</a>
					<div class="collapse" id="reviews">
						<ul class="nav nav-collapse">
							<li><a href="{{route('admin.reviews.index')}}"><span class="sub-item">All Reviews</span></a></li>
							<li><a href="{{route('admin.reviews.create')}}"><span class="sub-item">Create Review</span></a></li>
						</ul>
					</div>
				</li>
				@endcan

				{{-- Settings (Super Admin Only) --}}

				@role('Super Admin')
                @can('manage settings')
				<li class="nav-item">
					<a href="{{route('admin.general.settings')}}">
						<i class="fas fa-cog"></i>
						<p>General Settings</p>
					</a>
				</li>
                 @endcan
                 {{-- @can('manage roles')
				<li class="nav-item">
					<a href="{{ route('admin.roles.index') }}">
						<i class="fas fa-user-shield"></i>
						<p>Roles & Permissions</p>
					</a>
				</li>
                @endcan
                 @can('manage admins')
				<li class="nav-item">
					<a href="{{ route('admin.admins.index') }}">
						<i class="fas fa-users-cog"></i>
						<p>Admin Management</p>
					</a>
				</li>
                @endcan --}}

                @can('manage downloadDB')
                <li class="nav-item mt-4 pt-4">
                    <a href="{{ route('admin.download.database') }}" class="btn btn-success">
                        <i class="fa fa-download"></i>
                        <span class="text-dark">Download Database</span>
                    </a>
                </li>
                @endcan
				@endrole
			</ul>
		</div>
	</div>
</div>
<!-- End Sidebar -->
