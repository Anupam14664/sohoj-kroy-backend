		<!-- Sidebar -->
		<div class="sidebar" data-background-color="dark">
			<div class="sidebar-logo">
				<!-- Logo Header -->
				<div class="logo-header" data-background-color="dark">

					<a href="{{route('admin.dashboard')}}" class="logo">
                        @if($generalSettings->logo)
                        <img src="{{ asset('storage/' . str_replace('public/', '', $generalSettings->logo)) }}" alt="{{ $generalSettings->app_name }}" class="navbar-brand" height="50">
                    @else
                        <h1>{{ $generalSettings->app_name ?? 'App Name' }}</h1>
                    @endif
					</a>
					<div class="nav-toggle">
						<button class="btn btn-toggle toggle-sidebar">
							<i class="gg-menu-right"></i>
						</button>
						<button class="btn btn-toggle sidenav-toggler">
							<i class="gg-menu-left"></i>
						</button>
					</div>
					<button class="topbar-toggler more">
						<i class="gg-more-vertical-alt"></i>
					</button>

				</div>
				<!-- End Logo Header -->
			</div>
			<div class="sidebar-wrapper scrollbar scrollbar-inner">
				<div class="sidebar-content">
					<ul class="nav nav-secondary">
						<li class="nav-item active">
							<a href="{{route('admin.dashboard')}}">
								<i class="fas fa-home"></i>
								<p>Dashboard</p>
							</a>
						</li>
                        <li class="nav-item">
							<a  data-bs-toggle="collapse" href="#orders">
								<i class="fas fa-shopping-cart"></i>
								<p>Orders</p>
								<span class="caret"></span>
							</a>
							<div class="collapse" id="orders">
								<ul class="nav nav-collapse">
									<li>
										<a href="{{route('admin.orders.index')}}">
											<span class="sub-item">All Orders</span>
										</a>
									</li>
									<li class="nav-item">
                                        <a href="{{ route('admin.orders.index', ['status' => 'pending']) }}" class="nav-link">
                                            <span class="sub-item">Pending </span>
                                            <span class="badge badge-warning float-right">
                                                {{ App\Models\Order::where('status', 'pending')->count() }}
                                            </span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('admin.orders.index', ['status' => 'hold']) }}" class="nav-link">
                                            <span class="sub-item">Hold</span>
                                            <span class="badge badge-warning float-right">
                                                {{ App\Models\Order::where('status', 'hold')->count() }}
                                            </span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('admin.orders.index', ['status' => 'processing']) }}" class="nav-link">
                                            <span class="sub-item">Order Confirmed</span>
                                            <span class="badge badge-info float-right">
                                                {{ App\Models\Order::where('status', 'processing')->count() }}
                                            </span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('admin.orders.index', ['status' => 'shipped']) }}" class="nav-link">
                                            <span class="sub-item">Ready To Shipped</span>
                                            <span class="badge badge-primary float-right">
                                                {{ App\Models\Order::where('status', 'shipped')->count() }}
                                            </span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('admin.orders.index', ['status' => 'courier_delivered']) }}" class="nav-link">
                                            <span class="sub-item">Courier Delivered</span>
                                            <span class="badge badge-warning float-right">
                                                {{ App\Models\Order::where('status', 'courier_delivered')->count() }}
                                            </span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('admin.orders.index', ['status' => 'delivered']) }}" class="nav-link">
                                            <span class="sub-item">Delivered Orders</span>
                                            <span class="badge badge-success float-right">
                                                {{ App\Models\Order::where('status', 'delivered')->count() }}
                                            </span>
                                        </a>
                                    </li>
                                    {{-- <li class="nav-item">
                                        <a href="{{ route('admin.orders.incomplete') }}" class="nav-link">
                                            <span class="sub-item">Incomplete Orders</span>
                                            <span class="badge badge-secondary float-right">
                                                {{ App\Models\Order::where('status', 'incomplete')->count() }}
                                            </span>
                                        </a>
                                    </li> --}}
                                    <li class="nav-item">
                                        <a href="{{ route('admin.orders.index', ['status' => 'cancelled']) }}" class="nav-link">
                                            <span class="sub-item">Cancelled Orders</span>
                                            <span class="badge badge-danger float-right">
                                                {{ App\Models\Order::where('status', 'cancelled')->count() }}
                                            </span>
                                        </a>
                                    </li>

								</ul>
							</div>
						</li>
                        <li class="nav-item">
							<a href={{route('admin.orders.incomplete')}}>
								<i class="fas fa-exclamation-circle"></i>
                                <span class="sub-item">Incomplete Orders</span>
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
                        <li class="nav-item">
							<a href={{route('admin.customers.index')}}>
								<i class="fas fa-user-tag"></i>
								<p>Customers</p>
							</a>
						</li>
                        <li class="nav-item">
							<a href={{route('admin.customers.blocked')}}>
								<i class="fas fa-user-lock"></i>
								<p>Block Customers</p>
							</a>
						</li>
                        <li class="nav-item">
							<a data-bs-toggle="collapse" href="#products">
								<i class="fas fa-box-open"></i>
								<p>Products</p>
								<span class="caret"></span>
							</a>
							<div class="collapse" id="products">
								<ul class="nav nav-collapse">
                                    <li>
										<a href="{{route('admin.products.index')}}">
											<span class="sub-item">All Products</span>
										</a>
									</li>
									<li>
										<a href="{{route('admin.products.create')}}">
											<span class="sub-item">Add Products</span>
										</a>
									</li>
								</ul>
							</div>
						</li>
                        <li class="nav-item">
                            <a data-bs-toggle="collapse" href="#variantSettings" aria-expanded="false">
                                <i class="fas fa-sliders-h"></i>
                                <p>Variant Settings</p>
                                <span class="caret"></span>
                            </a>
                            <div class="collapse" id="variantSettings">
                                <ul class="nav nav-collapse">

                                    {{-- Categories --}}
                                    <li>
                                        <a data-bs-toggle="collapse" href="#categoriesMenu" aria-expanded="false">
                                            <span class="sub-item fw-bold">Categories</span>
                                            <span class="caret"></span>
                                        </a>
                                        <div class="collapse" id="categoriesMenu">
                                            <ul class="nav nav-collapse">
                                                <li>
                                                    <a href="{{ route('admin.categories.index') }}">
                                                        <span class="sub-item">All Categories</span>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="{{ route('admin.categories.create') }}">
                                                        <span class="sub-item">Create Category</span>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </li>

                                    {{-- Colors --}}
                                    <li>
                                        <a data-bs-toggle="collapse" href="#colorsMenu" aria-expanded="false">
                                            <span class="sub-item fw-bold">Colors</span>
                                            <span class="caret"></span>
                                        </a>
                                        <div class="collapse" id="colorsMenu">
                                            <ul class="nav nav-collapse">
                                                <li>
                                                    <a href="{{ route('admin.colors.index') }}">
                                                        <span class="sub-item">All Colors</span>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="{{ route('admin.colors.create') }}">
                                                        <span class="sub-item">Color Management</span>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </li>

                                    {{-- Sizes --}}
                                    <li>
                                        <a data-bs-toggle="collapse" href="#sizesMenu" aria-expanded="false">
                                            <span class="sub-item fw-bold">Sizes</span>
                                            <span class="caret"></span>
                                        </a>
                                        <div class="collapse" id="sizesMenu">
                                            <ul class="nav nav-collapse" style="margin-left:20px;">
                                                <li>
                                                    <a href="{{ route('admin.sizes.index') }}">
                                                        <span class="sub-item">All Sizes</span>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="{{ route('admin.sizes.create') }}">
                                                        <span class="sub-item">Add Size</span>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </li>

                                    {{-- Coupons --}}
                                    <li>
                                        <a data-bs-toggle="collapse" href="#couponsMenu" aria-expanded="false">
                                            <span class="sub-item fw-bold">Coupons</span>
                                            <span class="caret"></span>
                                        </a>
                                        <div class="collapse" id="couponsMenu">
                                            <ul class="nav nav-collapse">
                                                <li>
                                                    <a href="{{ route('admin.coupons.index') }}">
                                                        <span class="sub-item">All Coupons</span>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="{{ route('admin.coupons.create') }}">
                                                        <span class="sub-item">Add Coupon</span>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </li>

                                </ul>
                            </div>
                        </li>
                        <li class="nav-item">
                            <a data-bs-toggle="collapse" href="#frontendSettings" aria-expanded="false">
                                <i class="fas fa-cogs"></i>
                                <p>Frontend Settings</p>
                                <span class="caret"></span>
                            </a>
                            <div class="collapse" id="frontendSettings">
                                <ul class="nav nav-collapse">

                                    {{-- Home Section --}}
                                    <li>
                                        <a href="{{ route('admin.homepage-sections.index') }}">
                                            <span class="sub-item">Home Section</span>
                                        </a>
                                    </li>

                                    {{-- Banners --}}
                                    <li>
                                        <a data-bs-toggle="collapse" href="#bannersMenu" aria-expanded="false">
                                            <span class="sub-item fw-bold">Banners</span>
                                            <span class="caret"></span>
                                        </a>
                                        <div class="collapse" id="bannersMenu">
                                            <ul class="nav nav-collapse">
                                                <li>
                                                    <a href="{{ route('admin.banners.index') }}">
                                                        <span class="sub-item">Banners All</span>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="{{ route('admin.banners.create') }}">
                                                        <span class="sub-item">Create Banners</span>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </li>

                                </ul>
                            </div>
                        </li>
                        <li class="nav-item">
							<a data-bs-toggle="collapse" href="#delivery-options">
								<i class="fas fa-shipping-fast"></i>
								<p>Delivery Options</p>
								<span class="caret"></span>
							</a>
							<div class="collapse" id="delivery-options">
								<ul class="nav nav-collapse">
                                    <li>
										<a href="{{route('admin.delivery-options.index')}}">
											<span class="sub-item">All Options</span>
										</a>
									</li>
									<li>
										<a href="{{route('admin.delivery-options.create')}}">
											<span class="sub-item">Add Delivery Options</span>
										</a>
									</li>
								</ul>
							</div>
						</li>

                        <li class="nav-item">
							<a data-bs-toggle="collapse" href="#couriers">
								<i class="fas fa-dolly"></i>
								<p>Couriers Management</p>
								<span class="caret"></span>
							</a>
							<div class="collapse" id="couriers">
								<ul class="nav nav-collapse">
                                    <li>
										<a href="{{route('admin.couriers.index')}}">
											<span class="sub-item">Couriers List</span>
										</a>
									</li>
									<li>
										<a href="{{route('admin.couriers.create')}}">
											<span class="sub-item">Add Courier</span>
										</a>
									</li>
								</ul>
							</div>
						</li>
                        <li class="nav-item">
							<a data-bs-toggle="collapse" href="#reviews">
								<i class="fas fa-comments"></i>
								<p>Reviews</p>
								<span class="caret"></span>
							</a>
							<div class="collapse" id="reviews">
								<ul class="nav nav-collapse">
                                    <li>
										<a href="{{route('admin.reviews.index')}}">
											<span class="sub-item">All Reviews</span>
										</a>
									</li>
                                    <li>
										<a href="{{route('admin.reviews.create')}}">
											<span class="sub-item">Create Review</span>
										</a>
									</li>

								</ul>
							</div>
						</li>
                        <li class="nav-item">
							<a href="{{route('admin.general.settings')}}">
								<i class="fas fa-cog"></i>
								<p>General Settings</p>
							</a>
							{{-- <div class="collapse" id="settings">
								<ul class="nav nav-collapse">
                                    <li>
										<a href="{{route('admin.general.settings')}}">
											<span class="sub-item">General Settings</span>
										</a>
									</li>

								</ul>
							</div> --}}
						</li>
					</ul>
				</div>
			</div>
		</div>
		<!-- End Sidebar -->
