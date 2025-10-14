@extends('admin.layouts.app')

@section('content')
<div class="container">

    <h2>General Settings</h2>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form class="mt-4" style="width: 100%;" action="{{ route('admin.general.settings.update') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="row">
            <!-- Sidebar Navigation -->
            <div class="col-md-4 mb-4 mt-4">
                <div class="list-group mt-3" id="settingsTab" role="tablist">
                    <a class="list-group-item list-group-item-action active" id="app-tab" data-bs-toggle="tab" href="#app-settings" role="tab">App Settings</a>
                    <a class="list-group-item list-group-item-action" id="social-tab" data-bs-toggle="tab" href="#social-links" role="tab">Social Links</a>
                    <a class="list-group-item list-group-item-action" id="gtm-tab" data-bs-toggle="tab" href="#gtm" role="tab">Google Tag Manager</a>
                    <a class="list-group-item list-group-item-action" id="domain-tab" data-bs-toggle="tab" href="#domain-verification" role="tab">Domain Verification</a>
                    <a class="list-group-item list-group-item-action" id="custom-tab" data-bs-toggle="tab" href="#custom-scripts" role="tab">Custom CSS/JS</a>
                </div>
            </div>

            <!-- Tab Content -->
            <div class="col-md-8">
                <div class="tab-content" id="settingsTabContent">
                    <!-- App Settings -->
                    <div class="tab-pane fade show active" id="app-settings" role="tabpanel">
                        <h4>App Settings</h4>
                        <div class="mb-3">
                            <label for="app_name">App Name</label>
                            <input type="text" id="app_name" name="app_name" value="{{ old('app_name', $generalSettings->app_name) }}" required class="form-control">
                        </div>
                        <div class="mb-3">
                            <label for="favicon">Favicon (200x200px)</label>
                            <input type="file" id="favicon" name="favicon" class="form-control">
                            @if($generalSettings->favicon)
                                <img src="{{ asset('storage/' . str_replace('public/', '', $generalSettings->favicon)) }}" alt="Current Favicon" style="max-width: 32px;">
                            @endif
                        </div>
                        <div class="mb-3">
                            <label for="logo">Logo (300x45px)</label>
                            <input type="file" id="logo" name="logo" class="form-control">
                            @if($generalSettings->logo)
                                <img src="{{ asset('storage/' . str_replace('public/', '', $generalSettings->logo)) }}" alt="Current Logo" style="max-width: 200px;">
                            @endif
                        </div>
                        <div class="mb-3">
                            <label for="footer_content">Footer Content</label>
                            <textarea name="footer_content" class="form-control" rows="4">{{ old('footer_content', $generalSettings->footer_content) }}</textarea>
                        </div>
                    </div>

                    <!-- Social Links -->
                    <div class="tab-pane fade" id="social-links" role="tabpanel">
                        <h4>Social Links</h4>
                        @foreach (['facebook', 'twitter', 'instagram', 'youtube', 'linkedin', 'tiktok', 'messenger', 'whatsapp',] as $platform)
                            <div class="form-group">
                                <label for="{{ $platform }}_url">{{ ucfirst($platform) }} URL</label>
                                <input type="url" class="form-control" id="{{ $platform }}_url" name="{{ $platform }}_url"
                                       value="{{ old("{$platform}_url", $generalSettings["{$platform}_url"] ?? '') }}"
                                       placeholder="https://www.{{ $platform }}.com/">
                            </div>
                        @endforeach
                        <div class="mt-4">
                            <div class="form-group mb-3">
                                <label for="contact_number_1">Primary Contact Number</label>
                                <input type="text" class="form-control" id="contact_number_1" name="contact_number_1"
                                       value="{{ old('contact_number_1', $generalSettings->contact_number_1 ?? '') }}"
                                       placeholder="+1 (123) 456-7890">
                            </div>
                            <div class="form-group mb-3">
                                <label for="contact_number_2">Secondary Contact Number</label>
                                <input type="text" class="form-control" id="contact_number_2" name="contact_number_2"
                                       value="{{ old('contact_number_2', $generalSettings->contact_number_2 ?? '') }}"
                                       placeholder="+1 (987) 654-3210">
                            </div>
                        </div>
                    </div>

                    <!-- Google Tag Manager -->
                    <div class="tab-pane fade" id="gtm" role="tabpanel">
                        <h4>Google Tag Manager</h4>
                        <div class="form-group">
                            <label>Google Tag Manager Code</label>
                            <textarea name="google_tag_manager" class="form-control" rows="3">{{ old('google_tag_manager', $generalSettings->google_tag_manager) }}</textarea>
                        </div>
                    </div>

                    <!-- Domain Verification -->
                    <div class="tab-pane fade" id="domain-verification" role="tabpanel">
                        <h4>Domain Verification</h4>
                        <div class="form-group">
                            <label>Domain Verification Code</label>
                            <textarea name="domain_verification" class="form-control" rows="3">{{ old('domain_verification', $generalSettings->domain_verification) }}</textarea>
                        </div>
                    </div>

                    <!-- Custom CSS/JS -->
                    <div class="tab-pane fade" id="custom-scripts" role="tabpanel">
                        <h4>Custom CSS/JS</h4>
                        <div class="form-group">
                            <label>Head Data</label>
                            <textarea name="header_scripts" class="form-control" rows="4">{{ old('header_scripts', $generalSettings->header_scripts) }}</textarea>
                        </div>
                        <div class="form-group">
                            <label>Body Data</label>
                            <textarea name="footer_scripts" class="form-control" rows="4">{{ old('footer_scripts', $generalSettings->footer_scripts) }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
