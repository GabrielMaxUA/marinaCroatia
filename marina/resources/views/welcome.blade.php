@extends('layouts.app')

@section('title', 'Marina Croatia - Luxury Croatian Accommodations')

@section('content')
<!-- Main Title Section -->
<section class="main-title" id="main-title-section" 
    @if(isset($backgroundImage) && $backgroundImage)
    style="background-image: url('{{ Storage::url($backgroundImage) }}'); background-size: cover; background-position: center; background-repeat: no-repeat;"
    @endif
>
    <div class="title-overlay" style="opacity: {{ ($overlayOpacity ?? 50) / 100 }};"></div>
    <div class="title-content">
        @auth
            @if(auth()->user()->isAdmin())
                <button class="edit-btn" onclick="editMainContent()">✏️ Edit Content & Background</button>
            @endif
        @endauth
        <h1 id="main-heading">{{ $mainHeading ?? 'Luxury Croatian Accommodations' }}</h1>
        <p id="main-description">{{ $mainDescription ?? 'We are a premium travel agency specializing in exclusive accommodations along the Croatian coast.' }}</p>
    </div>
</section>

@auth
    @if(auth()->user()->isAdmin())
        <!-- Admin Filters Section -->
        <section class="container">
            <div class="admin-filters">
                <div class="filters-card">
                    <div class="filter-header">
                        <h3>🏖️ Location Management</h3>
                        <button class="btn btn-primary" onclick="openModal('location-modal')">+ Add New Location</button>
                    </div>
                    <form method="GET" class="filters-form">
                        <div class="filter-group">
                            <label for="location-filter">Filter by Location:</label>
                            <select id="location-filter" name="location_id" onchange="this.form.submit()">
                                <option value="">All Locations</option>
                                @foreach($allLocations as $location)
                                    <option value="{{ $location->id }}" {{ request('location_id') == $location->id ? 'selected' : '' }}>
                                        {{ $location->name }} ({{ $location->houses->count() }} houses)
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="filter-actions">
                            <a href="{{ route('home') }}" class="btn btn-secondary">Clear Filter</a>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    @endif
@endauth

<!-- Success/Error Messages -->
@if(session('success'))
    <div class="container">
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    </div>
@endif

@if(session('error'))
    <div class="container">
        <div class="alert alert-error">
            {{ session('error') }}
        </div>
    </div>
@endif

<!-- Locations Grid -->
<section class="container">
    <div class="locations-grid">
        @if($locations && $locations->count() > 0)
            @foreach($locations as $location)
            <div class="location-card" data-location-id="{{ $location->id }}" onclick="viewLocation({{ $location->id }})">
                @auth
                    @if(auth()->user()->isAdmin())
                        <div class="admin-controls">
                            <button class="edit-btn" onclick="event.stopPropagation(); editLocation({{ $location->id }}, '{{ $location->name }}', '{{ $location->description }}')" title="Edit Location">✏️</button>
                            <button class="delete-btn" onclick="event.stopPropagation(); deleteLocation({{ $location->id }})" title="Delete Location">×</button>
                        </div>
                    @endif
                @endauth
                <div class="location-image">
                    @if($location->primaryImage)
                        <img src="{{ $location->primaryImage->full_url }}" alt="{{ $location->name }}" loading="lazy">
                        @if($location->media->count() > 1)
                            <div class="media-count-badge">{{ $location->media->count() }}</div>
                        @endif
                        @if($location->media->count() > 0)
                            <div class="location-badge" onclick="event.stopPropagation(); viewLocationGallery({{ $location->id }})">
                                <span>View Gallery</span>
                            </div>
                        @endif
                    @else
                        <div class="placeholder-image">
                            <span>{{ $location->name }}</span>
                        </div>
                    @endif
                </div>
                <div class="location-info">
                    <h3>{{ $location->name }}</h3>
                    @if($location->description)
                        <p>{{ Str::limit($location->description, 60) }}</p>
                    @endif
                    <div class="location-stats">
                        <small>{{ $location->houses->count() }} Properties</small>
                    </div>
                </div>
            </div>
            @endforeach
            
            @auth
                @if(auth()->user()->isAdmin())
                    <div class="location-card add-new" onclick="openModal('location-modal')">
                        <div class="add-new-content">
                            <div class="add-icon">+</div>
                            <h3>Add New Location</h3>
                        </div>
                    </div>
                @endif
            @endauth
        @else
            <div class="empty-state">
                <h3>No Locations Available</h3>
                <p>Check back later for available accommodations.</p>
                @auth
                    @if(auth()->user()->isAdmin())
                        <button class="btn btn-primary" onclick="openModal('location-modal')">Add First Location</button>
                    @endif
                @endauth
            </div>
        @endif
    </div>
</section>

<!-- Pagination for Admin -->
@auth
    @if(auth()->user()->isAdmin() && method_exists($locations, 'hasPages') && $locations->hasPages())
        <section class="container">
            <div class="pagination-wrapper">
                {{ $locations->appends(request()->query())->links() }}
            </div>
        </section>
    @endif
@endauth

@auth
    @if(auth()->user()->isAdmin())
        <!-- Content Edit Modal -->
        <div id="content-edit-modal" class="modal">
            <div class="modal-content large">
                <div class="modal-header">
                    <h3>Edit Main Content & Background</h3>
                    <button class="modal-close" onclick="closeModal('content-edit-modal')">×</button>
                </div>
                <form id="content-edit-form" onsubmit="saveMainContent(event)" enctype="multipart/form-data">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit-main-heading">Main Heading:</label>
                            <input type="text" id="edit-main-heading" name="main_heading" required maxlength="200">
                        </div>
                        <div class="form-group">
                            <label for="overlay-opacity">Overlay Opacity:</label>
                            <input type="range" id="overlay-opacity" name="overlay_opacity" min="0" max="100" value="{{ $overlayOpacity ?? 50 }}" onchange="updateOpacityPreview(this.value)">
                            <span id="opacity-value">{{ $overlayOpacity ?? 50 }}%</span>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit-main-description">Description:</label>
                        <textarea id="edit-main-description" name="main_description" rows="4" required maxlength="1000"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="background-image">Background Image:</label>
                        <input type="file" id="background-image" name="background_image" accept="image/jpeg,image/png,image/gif,image/webp" onchange="previewBackgroundImage(this)">
                        <div class="upload-info">
                            <small class="text-muted">
                                • Max size: 10MB • Formats: JPEG, PNG, GIF, WebP<br>
                                • Images will be automatically optimized and converted to WebP format<br>
                                • Recommended size: 1920x1080px for best quality
                            </small>
                        </div>
                        @if(isset($backgroundImage) && $backgroundImage)
                            <div id="current-background" class="current-image-preview">
                                <p>Current background:</p>
                                <img src="{{ Storage::url($backgroundImage) }}" alt="Current background" style="max-width: 200px; max-height: 100px; object-fit: cover; border-radius: 4px;">
                            </div>
                        @endif
                        <div id="image-preview" class="image-preview" style="display: none;">
                            <img id="preview-img" src="" alt="Preview" style="max-width: 200px; max-height: 100px; object-fit: cover; border-radius: 4px;">
                            <button type="button" onclick="removeImagePreview()" class="remove-preview btn btn-secondary">Remove Preview</button>
                        </div>
                        <div id="upload-progress" style="display: none;">
                            <div class="progress-bar">
                                <div class="progress-fill"></div>
                            </div>
                            <p class="progress-text">Uploading and optimizing image...</p>
                        </div>
                    </div>
                    
                    @if(isset($backgroundImage) && $backgroundImage)
                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" id="remove-background" name="remove_background" onchange="toggleRemoveBackground(this)">
                            <span class="checkmark"></span>
                            Remove current background image
                        </label>
                    </div>
                    @endif
                    
                    <div class="modal-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeModal('content-edit-modal')">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Location Modal -->
        <div id="location-modal" class="modal">
            <div class="modal-content large">
                <div class="modal-header">
                    <h3 id="location-modal-title">Add Location</h3>
                    <button class="modal-close" onclick="closeModal('location-modal')">×</button>
                </div>
                <form id="location-form" onsubmit="saveLocation(event)" enctype="multipart/form-data">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="location-name">Location Name:</label>
                            <input type="text" id="location-name" name="name" required maxlength="100">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="location-description">Description:</label>
                        <textarea id="location-description" name="description" rows="3" maxlength="500"></textarea>
                    </div>
                    
                    <!-- Media Upload Section -->
                    <div class="form-group">
                        <label class="media-upload-label">
                            📷 Location Photos & Videos
                            <span class="media-upload-info">Upload multiple images and videos to showcase this location</span>
                        </label>
                        
                        <!-- Drag & Drop Upload Area -->
                        <div class="media-upload-zone" id="media-upload-zone" ondrop="handleDrop(event)" ondragover="handleDragOver(event)" ondragleave="handleDragLeave(event)">
                            <div class="upload-zone-content">
                                <div class="upload-icon">📁</div>
                                <p class="upload-text">Drag & drop images/videos here</p>
                                <p class="upload-subtext">or</p>
                                <button type="button" class="btn btn-secondary" onclick="document.getElementById('media-files').click()">Choose Files</button>
                                <input type="file" id="media-files" name="media_files[]" multiple accept="image/*,video/*" style="display: none;" onchange="handleFileSelect(event)">
                            </div>
                        </div>
                        
                        <div class="upload-limits">
                            <small class="text-muted">
                                • Images: JPEG, PNG, GIF, WebP (max 20MB each) - <strong>First 3 images optimized</strong><br>
                                • Videos: MP4, MOV, AVI, WMV (max 20MB each)<br>
                                • Maximum 10 files per upload • Large images stored as-is for faster processing
                            </small>
                        </div>
                    </div>
                    
                    <!-- Media Previews -->
                    <div id="media-previews-container" class="media-previews" style="display: none;">
                        <h4>Selected Media</h4>
                        <div id="media-previews" class="media-grid"></div>
                        <div class="primary-selection-info">
                            <small class="text-muted">💡 Click the star (⭐) on any image to set it as the primary preview image for this location</small>
                        </div>
                    </div>
                    
                    <!-- Existing Media (for edit mode) -->
                    <div id="existing-media-container" class="existing-media" style="display: none;">
                        <h4>Current Media</h4>
                        <div id="existing-media" class="media-grid"></div>
                    </div>
                    
                    <div class="modal-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeModal('location-modal')">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <span id="location-submit-text">Save Location</span>
                            <span id="location-submit-loading" style="display: none;">
                                <span class="loading"></span> Processing...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endauth

@push('styles')
<style>
    .main-title {
        position: relative;
        overflow: hidden;
    }
    
    .title-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        pointer-events: none;
    }
    
    .title-content {
        position: relative;
        z-index: 2;
    }
    
    .admin-filters {
        margin: 2rem 0;
    }
    
    .filters-card {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .filter-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid #f3f4f6;
    }
    
    .filter-header h3 {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1f2937;
        margin: 0;
    }
    
    .filters-form {
        display: flex;
        gap: 1rem;
        align-items: end;
        flex-wrap: wrap;
    }
    
    .filter-group {
        flex: 1;
        min-width: 200px;
    }
    
    .filter-group label {
        display: block;
        font-size: 0.875rem;
        font-weight: 500;
        color: #374151;
        margin-bottom: 0.5rem;
    }
    
    .filter-group input,
    .filter-group select {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 0.875rem;
        background: white;
        cursor: pointer;
    }
    
    .filter-group select:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
    }
    
    .filter-actions {
        display: flex;
        gap: 0.5rem;
    }
    
    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        margin-bottom: 1rem;
    }
    
    .pagination-wrapper {
        display: flex;
        justify-content: center;
        margin: 2rem 0;
    }
    
    .checkbox-label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        cursor: pointer;
    }
    
    .checkbox-label input[type="checkbox"] {
        width: auto;
        margin: 0;
    }
    
    .current-image-preview p {
        font-size: 0.875rem;
        color: #6b7280;
        margin: 0.5rem 0;
    }
    
    .remove-preview {
        margin-top: 0.5rem;
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }
    
    .upload-info {
        margin-top: 0.5rem;
    }
    
    .upload-info small {
        color: #6b7280;
        font-size: 0.75rem;
        line-height: 1.4;
    }
    
    .progress-bar {
        width: 100%;
        height: 6px;
        background: #e5e7eb;
        border-radius: 3px;
        overflow: hidden;
        margin: 0.5rem 0;
    }
    
    .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, #3b82f6, #1d4ed8);
        width: 0%;
        transition: width 0.3s ease;
        animation: progressAnimation 2s infinite;
    }
    
    @keyframes progressAnimation {
        0% { width: 0%; }
        50% { width: 70%; }
        100% { width: 100%; }
    }
    
    .progress-text {
        font-size: 0.875rem;
        color: #6b7280;
        text-align: center;
        margin: 0;
    }
    
    /* Media Upload Styles */
    .media-upload-label {
        display: block;
        font-size: 1rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.5rem;
    }
    
    .media-upload-info {
        display: block;
        font-size: 0.875rem;
        font-weight: 400;
        color: #6b7280;
        margin-top: 0.25rem;
    }
    
    .media-upload-zone {
        border: 2px dashed #d1d5db;
        border-radius: 12px;
        padding: 2rem;
        text-align: center;
        background: #f9fafb;
        transition: all 0.3s ease;
        cursor: pointer;
        margin: 1rem 0;
    }
    
    .media-upload-zone:hover,
    .media-upload-zone.drag-over {
        border-color: #3b82f6;
        background: #eff6ff;
    }
    
    .upload-zone-content {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.5rem;
    }
    
    .upload-icon {
        font-size: 3rem;
        color: #9ca3af;
    }
    
    .upload-text {
        font-size: 1.125rem;
        font-weight: 600;
        color: #374151;
        margin: 0;
    }
    
    .upload-subtext {
        font-size: 0.875rem;
        color: #6b7280;
        margin: 0;
    }
    
    .upload-limits {
        margin-top: 0.5rem;
    }
    
    .media-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
        gap: 1rem;
        margin: 1rem 0;
    }
    
    .media-preview {
        position: relative;
        border-radius: 8px;
        overflow: hidden;
        background: #f3f4f6;
        border: 2px solid transparent;
        transition: all 0.2s;
    }
    
    .media-preview:hover {
        border-color: #3b82f6;
        transform: translateY(-2px);
    }
    
    .media-preview.primary {
        border-color: #f59e0b;
        box-shadow: 0 0 0 2px rgba(245, 158, 11, 0.2);
    }
    
    .media-preview img,
    .media-preview video {
        width: 100%;
        height: 120px;
        object-fit: cover;
        display: block;
    }
    
    .media-preview-controls {
        position: absolute;
        top: 4px;
        right: 4px;
        display: flex;
        gap: 4px;
    }
    
    /* Existing Media Styles */
    .existing-media {
        margin-bottom: 1.5rem;
        padding: 1rem;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        background: #f9fafb;
    }
    
    .existing-media h4 {
        margin-top: 0;
        margin-bottom: 1rem;
        color: #374151;
        font-size: 1rem;
        font-weight: 600;
    }
    
    .existing-media-item .media-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        opacity: 0;
        transition: opacity 0.2s;
    }
    
    .existing-media-item:hover .media-overlay {
        opacity: 1;
    }
    
    .btn-remove-existing,
    .btn-set-primary {
        background: rgba(255, 255, 255, 0.9);
        border: none;
        border-radius: 50%;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 0.875rem;
        font-weight: 600;
        transition: all 0.2s;
    }
    
    .btn-remove-existing {
        color: #dc2626;
    }
    
    .btn-remove-existing:hover {
        background: #fca5a5;
        color: white;
    }
    
    .btn-set-primary {
        color: #f59e0b;
    }
    
    .btn-set-primary:hover {
        background: #fbbf24;
        color: white;
    }
    
    .btn-set-primary.primary-selected {
        background: #f59e0b;
        color: white;
        box-shadow: 0 0 0 2px rgba(245, 158, 11, 0.3);
    }
    
    .existing-media-title {
        width: 100%;
        padding: 0.25rem 0.5rem;
        border: 1px solid #d1d5db;
        border-radius: 4px;
        font-size: 0.75rem;
        margin-top: 0.5rem;
    }
    
    .existing-media-title:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 1px rgba(59, 130, 246, 0.3);
    }
    
    .media-control-btn {
        background: rgba(0, 0, 0, 0.7);
        color: white;
        border: none;
        width: 24px;
        height: 24px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
    }
    
    .media-control-btn:hover {
        background: rgba(0, 0, 0, 0.9);
        transform: scale(1.1);
    }
    
    .media-control-btn.primary {
        background: rgba(245, 158, 11, 0.9);
    }
    
    .media-control-btn.primary:hover {
        background: rgba(245, 158, 11, 1);
    }
    
    .media-control-btn.remove {
        background: rgba(239, 68, 68, 0.9);
    }
    
    .media-control-btn.remove:hover {
        background: rgba(239, 68, 68, 1);
    }
    
    .media-preview-title {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background: linear-gradient(transparent, rgba(0, 0, 0, 0.7));
        color: white;
        padding: 1rem 0.5rem 0.5rem;
        font-size: 0.75rem;
        line-height: 1.2;
    }
    
    .media-preview-title input {
        background: transparent;
        border: none;
        color: white;
        width: 100%;
        font-size: 0.75rem;
        outline: none;
    }
    
    .media-preview-title input::placeholder {
        color: rgba(255, 255, 255, 0.7);
    }
    
    .media-type-badge {
        position: absolute;
        top: 4px;
        left: 4px;
        background: rgba(0, 0, 0, 0.7);
        color: white;
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 10px;
        font-weight: 600;
        text-transform: uppercase;
    }
    
    .media-type-badge.video {
        background: rgba(239, 68, 68, 0.9);
    }
    
    .primary-selection-info {
        text-align: center;
        margin: 1rem 0;
        padding: 0.75rem;
        background: #fef3c7;
        border-radius: 6px;
        border: 1px solid #fcd34d;
    }

    .locations-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 2rem;
        padding: 2rem 0;
    }

    .location-card {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        cursor: pointer;
        transition: transform 0.3s, box-shadow 0.3s;
        position: relative;
        height: auto;
        width: 300px;
        margin: 0 auto;
    }

    .location-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.15);
    }

    .location-card.add-new {
        border: 2px dashed #cbd5e0;
        background: #f8fafc;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .location-card.add-new:hover {
        border-color: #3b82f6;
        background: #eff6ff;
    }

    .add-new-content {
        text-align: center;
        color: #6b7280;
    }

    .add-new-content:hover {
        color: #3b82f6;
    }

    .add-icon {
        font-size: 3rem;
        margin-bottom: 1rem;
    }

    .admin-controls {
        position: absolute;
        top: 8px;
        right: 8px;
        z-index: 10;
        display: flex;
        gap: 4px;
    }

    .admin-controls .edit-btn,
    .admin-controls .delete-btn {
        background: rgba(255,255,255,0.9);
        border: none;
        width: 32px;
        height: 32px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
        position: static;
    }

    .admin-controls .edit-btn:hover {
        background: #f59e0b;
        color: white;
    }

    .admin-controls .delete-btn {
        color: #ef4444;
        font-weight: bold;
    }

    .admin-controls .delete-btn:hover {
        background: #ef4444;
        color: white;
    }

    .location-image {
        height: 200px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        position: relative;
        overflow: hidden;
    }
    
    .location-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }
    
    .location-card:hover .location-image img {
        transform: scale(1.05);
    }
    
    .media-count-badge {
        position: absolute;
        bottom: 8px;
        right: 8px;
        background: rgba(0, 0, 0, 0.8);
        color: white;
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 600;
        backdrop-filter: blur(4px);
    }
    
    .location-badge {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background: rgba(0, 0, 0, 0.7);
        color: white;
        padding: 0.75rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        backdrop-filter: blur(4px);
        border-top: 2px solid transparent;
    }
    
    .location-badge:hover {
        background: rgba(107, 114, 128, 0.9);
        border-top: 2px solid rgba(107, 114, 128, 1);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(107, 114, 128, 0.3);
    }
    
    .location-badge span {
        font-size: 0.875rem;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .location-badge:hover span {
        text-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
        letter-spacing: 0.5px;
    }

    .placeholder-image {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.5rem;
        font-weight: bold;
        text-align: center;
    }

    .location-info {
        padding: 1.5rem;
        height: auto;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .location-info h3 {
        font-size: 1.25rem;
        font-weight: bold;
        color: #1f2937;
        margin: 0 0 0.5rem 0;
    }

    .location-info p {
        color: #6b7280;
        font-size: 0.875rem;
        margin: 0;
        line-height: 1.4;
    }

    .location-stats {
        margin-top: auto;
        padding-top: 0.5rem;
    }

    .location-stats small {
        color: #9ca3af;
        font-weight: 500;
    }

    .empty-state {
        grid-column: 1 / -1;
        text-align: center;
        padding: 4rem 2rem;
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .empty-state h3 {
        color: #6b7280;
        margin-bottom: 1rem;
    }

    .empty-state p {
        color: #9ca3af;
        margin-bottom: 2rem;
    }

    @media (max-width: 768px) {
        .locations-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        
        .location-card {
            width: 100%;
            max-width: 300px;
        }
        
        .filters-form {
            flex-direction: column;
            align-items: stretch;
        }
        
        .filter-group {
            min-width: unset;
        }
        
        .filter-header {
            flex-direction: column;
            gap: 1rem;
            align-items: stretch;
        }
        
        .form-row {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@push('scripts')
<script>
let editingLocation = null;
let selectedFiles = [];
let primaryMediaIndex = null;

function viewLocation(locationId) {
    window.location.href = `/locations/${locationId}`;
}

function viewLocationGallery(locationId) {
    fetch(`/locations/${locationId}/gallery`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showLightbox(data.location_name + ' Gallery', data.media);
            } else {
                showLightbox('Location Gallery', []);
            }
        })
        .catch(error => {
            console.error('Error loading gallery:', error);
            showLightbox('Location Gallery', []);
        });
}

function loadExistingMedia(locationId) {
    console.log('Loading existing media for location:', locationId);
    
    // We need to fetch detailed media info including IDs, so let's use a more detailed endpoint
    fetch(`{{ url('admin/locations') }}/${locationId}`)
        .then(response => {
            console.log('Response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Media data received:', data);
            
            const existingMediaContainer = document.getElementById('existing-media-container');
            const existingMediaGrid = document.getElementById('existing-media');
            
            if (data.success && data.location && data.location.media && data.location.media.length > 0) {
                console.log('Found', data.location.media.length, 'media items');
                existingMediaContainer.style.display = 'block';
                existingMediaGrid.innerHTML = '';
                
                data.location.media.forEach((media, index) => {
                    console.log('Creating media item:', media);
                    const mediaItem = createExistingMediaItem(media, index, locationId);
                    existingMediaGrid.appendChild(mediaItem);
                });
            } else {
                console.log('No media found or request failed');
                existingMediaContainer.style.display = 'none';
                existingMediaGrid.innerHTML = '';
            }
        })
        .catch(error => {
            console.error('Error loading existing media:', error);
            document.getElementById('existing-media-container').style.display = 'none';
        });
}

function createExistingMediaItem(media, index, locationId) {
    console.log('Creating media item for:', media);
    
    const mediaItem = document.createElement('div');
    mediaItem.className = 'media-item existing-media-item';
    mediaItem.dataset.index = index;
    mediaItem.dataset.mediaId = media.id;
    mediaItem.dataset.locationId = locationId;
    
    let mediaContent = '';
    // Try multiple possible URL fields
    let mediaUrl = media.full_url || media.media_url || media.url;
    console.log('Media URL:', mediaUrl);
    
    // Ensure the URL starts with /storage/ or /
    if (mediaUrl && !mediaUrl.startsWith('http') && !mediaUrl.startsWith('/')) {
        mediaUrl = '/storage/' + mediaUrl;
    }
    
    if (media.media_type === 'image' || media.type === 'image') {
        mediaContent = `<img src="${mediaUrl}" alt="Media ${index + 1}" loading="lazy" onerror="console.error('Failed to load image:', '${mediaUrl}')" />`;
    } else if (media.media_type === 'video' || media.type === 'video') {
        mediaContent = `<video src="${mediaUrl}" preload="metadata" onerror="console.error('Failed to load video:', '${mediaUrl}')"></video>`;
    }
    
    const primaryClass = media.is_primary ? 'primary-selected' : '';
    const primaryText = media.is_primary ? '⭐' : '⭐';
    
    mediaItem.innerHTML = `
        <div class="media-preview ${media.is_primary ? 'primary' : ''}">
            ${mediaContent}
            <div class="media-overlay">
                <button type="button" class="btn-remove-existing" onclick="removeExistingMedia(${media.id}, ${locationId})" title="Remove">×</button>
                <button type="button" class="btn-set-primary ${primaryClass}" onclick="setExistingAsPrimary(${media.id}, ${locationId})" title="Set as Primary">${primaryText}</button>
            </div>
        </div>
        <div class="media-title">
            <input type="text" placeholder="Media title (optional)" class="existing-media-title" value="${media.media_title || ''}" />
        </div>
    `;
    
    return mediaItem;
}

function removeExistingMedia(mediaId, locationId) {
    if (confirm('Are you sure you want to remove this media? This action cannot be undone.')) {
        const mediaItem = document.querySelector(`[data-media-id="${mediaId}"]`);
        
        // Show loading state
        if (mediaItem) {
            const overlay = mediaItem.querySelector('.media-overlay');
            if (overlay) {
                overlay.innerHTML = '<div style="color: white; font-weight: bold;">Deleting...</div>';
            }
        }
        
        fetch(`{{ url('admin/locations') }}/${locationId}/media/${mediaId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': window.Laravel.csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove the media item from DOM
                if (mediaItem) {
                    mediaItem.remove();
                }
                showAlert(data.message || 'Media deleted successfully', 'success');
                
                // Check if there are no more existing media items
                const remainingItems = document.querySelectorAll('.existing-media-item');
                if (remainingItems.length === 0) {
                    document.getElementById('existing-media-container').style.display = 'none';
                }
            } else {
                showAlert(data.message || 'Failed to delete media', 'error');
                // Restore the overlay
                if (mediaItem) {
                    const overlay = mediaItem.querySelector('.media-overlay');
                    if (overlay) {
                        overlay.innerHTML = `
                            <button type="button" class="btn-remove-existing" onclick="removeExistingMedia(${mediaId}, ${locationId})" title="Remove">×</button>
                            <button type="button" class="btn-set-primary" onclick="setExistingAsPrimary(${mediaId}, ${locationId})" title="Set as Primary">⭐</button>
                        `;
                    }
                }
            }
        })
        .catch(error => {
            console.error('Error deleting media:', error);
            showAlert('An error occurred while deleting media', 'error');
            // Restore the overlay
            if (mediaItem) {
                const overlay = mediaItem.querySelector('.media-overlay');
                if (overlay) {
                    overlay.innerHTML = `
                        <button type="button" class="btn-remove-existing" onclick="removeExistingMedia(${mediaId}, ${locationId})" title="Remove">×</button>
                        <button type="button" class="btn-set-primary" onclick="setExistingAsPrimary(${mediaId}, ${locationId})" title="Set as Primary">⭐</button>
                    `;
                }
            }
        });
    }
}

function setExistingAsPrimary(mediaId, locationId) {
    fetch(`{{ url('admin/locations') }}/${locationId}/media/${mediaId}/primary`, {
        method: 'PUT',
        headers: {
            'X-CSRF-TOKEN': window.Laravel.csrfToken,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove primary status from all existing media items
            document.querySelectorAll('.existing-media-item .btn-set-primary').forEach(btn => {
                btn.classList.remove('primary-selected');
            });
            document.querySelectorAll('.existing-media-item .media-preview').forEach(preview => {
                preview.classList.remove('primary');
            });
            
            // Set this item as primary
            const mediaItem = document.querySelector(`[data-media-id="${mediaId}"]`);
            if (mediaItem) {
                const primaryBtn = mediaItem.querySelector('.btn-set-primary');
                const mediaPreview = mediaItem.querySelector('.media-preview');
                
                if (primaryBtn) {
                    primaryBtn.classList.add('primary-selected');
                }
                if (mediaPreview) {
                    mediaPreview.classList.add('primary');
                }
            }
            
            showAlert(data.message || 'Primary media updated successfully', 'success');
        } else {
            showAlert(data.message || 'Failed to update primary media', 'error');
        }
    })
    .catch(error => {
        console.error('Error setting primary media:', error);
        showAlert('An error occurred while updating primary media', 'error');
    });
}

function updateLocationCard(location) {
    console.log('Updating location card for location:', location);
    console.log('Looking for card with ID:', location.id);
    
    // Debug: show all existing cards
    const allCards = document.querySelectorAll('.location-card:not(.add-new)');
    console.log('All location cards found:', allCards.length);
    allCards.forEach((card, index) => {
        console.log(`Card ${index}:`, {
            dataLocationId: card.dataset.locationId,
            onclick: card.getAttribute('onclick'),
            element: card
        });
    });
    
    // Try multiple ways to find the existing card
    let existingCard = document.querySelector(`[data-location-id="${location.id}"]`);
    console.log('Found by data-location-id:', existingCard);
    
    if (!existingCard) {
        existingCard = document.querySelector(`[onclick="viewLocation(${location.id})"]`);
        console.log('Found by onclick attribute:', existingCard);
    }
    
    if (!existingCard) {
        // Find by checking all location cards manually
        for (let card of allCards) {
            const onclick = card.getAttribute('onclick');
            const dataId = card.dataset.locationId;
            console.log(`Checking card - dataId: ${dataId}, onclick: ${onclick}`);
            
            if (dataId == location.id || (onclick && onclick.includes(`viewLocation(${location.id})`))) {
                existingCard = card;
                console.log('Found card by manual check:', existingCard);
                break;
            }
        }
    }
    
    if (existingCard) {
        console.log('Updating existing card:', existingCard);
        const newCard = createLocationCardElement(location);
        existingCard.replaceWith(newCard);
        console.log('Successfully replaced card');
    } else {
        console.error('Could not find existing card to update for location ID:', location.id);
        // As fallback, try to add it as new (this shouldn't happen but prevents duplication)
        console.log('Adding as new card instead');
        addNewLocationCard(location);
    }
}

function addNewLocationCard(location) {
    const locationsGrid = document.querySelector('.locations-grid');
    const addNewCard = document.querySelector('.location-card.add-new');
    
    if (locationsGrid && addNewCard) {
        const newCard = createLocationCardElement(location);
        locationsGrid.insertBefore(newCard, addNewCard);
    }
}

function createLocationCardElement(location) {
    const cardDiv = document.createElement('div');
    cardDiv.className = 'location-card';
    cardDiv.dataset.locationId = location.id;
    cardDiv.onclick = () => viewLocation(location.id);
    
    let imageSection = '';
    if (location.primary_image_url) {
        const mediaCount = location.media_count || 0;
        const galleryButton = mediaCount > 0 ? `
            <div class="location-badge" onclick="event.stopPropagation(); viewLocationGallery(${location.id})">
                <span>View Gallery</span>
            </div>
        ` : '';
        const mediaBadge = mediaCount > 1 ? `<div class="media-count-badge">${mediaCount}</div>` : '';
        
        imageSection = `
            <img src="${location.primary_image_url}" alt="${location.name}" loading="lazy">
            ${mediaBadge}
            ${galleryButton}
        `;
    } else {
        imageSection = `
            <div class="placeholder-image">
                <span>${location.name}</span>
            </div>
        `;
    }
    
    const adminControls = `
        <div class="admin-controls">
            <button class="edit-btn" onclick="event.stopPropagation(); editLocation(${location.id}, ${JSON.stringify(location.name)}, ${JSON.stringify(location.description || '')})" title="Edit Location">✏️</button>
            <button class="delete-btn" onclick="event.stopPropagation(); deleteLocation(${location.id})" title="Delete Location">×</button>
        </div>
    `;
    
    cardDiv.innerHTML = `
        ${adminControls}
        <div class="location-image">
            ${imageSection}
        </div>
        <div class="location-info">
            <h3>${location.name}</h3>
            ${location.description ? `<p>${location.description.length > 60 ? location.description.substring(0, 60) + '...' : location.description}</p>` : ''}
            <div class="location-stats">
                <small>${location.houses_count || 0} Properties</small>
            </div>
        </div>
    `;
    
    return cardDiv;
}

// Media Upload Functions
function handleDragOver(e) {
    e.preventDefault();
    e.stopPropagation();
    document.getElementById('media-upload-zone').classList.add('drag-over');
}

function handleDragLeave(e) {
    e.preventDefault();
    e.stopPropagation();
    document.getElementById('media-upload-zone').classList.remove('drag-over');
}

function handleDrop(e) {
    e.preventDefault();
    e.stopPropagation();
    document.getElementById('media-upload-zone').classList.remove('drag-over');
    
    const files = Array.from(e.dataTransfer.files);
    addMediaFiles(files);
}

function handleFileSelect(e) {
    const files = Array.from(e.target.files);
    addMediaFiles(files);
}

function addMediaFiles(files) {
    // Check total file limit
    if (selectedFiles.length + files.length > 10) {
        showAlert(`Maximum 10 files allowed. You can add ${10 - selectedFiles.length} more files.`, 'error');
        return;
    }

    for (let file of files) {
        if (validateMediaFile(file)) {
            selectedFiles.push(file);
        }
    }
    updateMediaPreviews();
}

function validateMediaFile(file) {
    const maxFileSize = 20 * 1024 * 1024; // 20MB for both images and videos
    
    const imageTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    const videoTypes = ['video/mp4', 'video/mov', 'video/avi', 'video/wmv'];
    
    if (imageTypes.includes(file.type) || videoTypes.includes(file.type)) {
        if (file.size > maxFileSize) {
            showAlert(`File "${file.name}" is too large. Maximum size is 20MB.`, 'error');
            return false;
        }
    } else {
        showAlert(`File "${file.name}" is not supported. Please use images (JPEG, PNG, GIF, WebP) or videos (MP4, MOV, AVI, WMV).`, 'error');
        return false;
    }
    
    return true;
}

function updateMediaPreviews() {
    const container = document.getElementById('media-previews-container');
    const previewsGrid = document.getElementById('media-previews');
    
    if (selectedFiles.length === 0) {
        container.style.display = 'none';
        return;
    }
    
    container.style.display = 'block';
    previewsGrid.innerHTML = '';
    
    selectedFiles.forEach((file, index) => {
        const previewDiv = document.createElement('div');
        previewDiv.className = `media-preview ${index === primaryMediaIndex ? 'primary' : ''}`;
        previewDiv.setAttribute('data-index', index);
        
        const isVideo = file.type.startsWith('video/');
        const mediaElement = isVideo ? document.createElement('video') : document.createElement('img');
        
        if (isVideo) {
            mediaElement.controls = false;
            mediaElement.muted = true;
        }
        
        mediaElement.onload = function() {
            URL.revokeObjectURL(this.src);
        };
        
        mediaElement.src = URL.createObjectURL(file);
        
        const typeBadge = document.createElement('div');
        typeBadge.className = `media-type-badge ${isVideo ? 'video' : 'image'}`;
        typeBadge.textContent = isVideo ? 'VIDEO' : 'IMAGE';
        
        const controls = document.createElement('div');
        controls.className = 'media-preview-controls';
        
        const primaryBtn = document.createElement('button');
        primaryBtn.type = 'button';
        primaryBtn.className = `media-control-btn ${index === primaryMediaIndex ? 'primary' : ''}`;
        primaryBtn.innerHTML = index === primaryMediaIndex ? '★' : '☆';
        primaryBtn.title = 'Set as primary';
        primaryBtn.onclick = () => setPrimaryMedia(index);
        
        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'media-control-btn remove';
        removeBtn.innerHTML = '×';
        removeBtn.title = 'Remove';
        removeBtn.onclick = () => removeMedia(index);
        
        controls.appendChild(primaryBtn);
        controls.appendChild(removeBtn);
        
        const titleDiv = document.createElement('div');
        titleDiv.className = 'media-preview-title';
        const titleInput = document.createElement('input');
        titleInput.type = 'text';
        titleInput.placeholder = 'Enter title (optional)';
        titleInput.name = `media_titles[${index}]`;
        titleInput.maxLength = 200;
        titleDiv.appendChild(titleInput);
        
        previewDiv.appendChild(mediaElement);
        previewDiv.appendChild(typeBadge);
        previewDiv.appendChild(controls);
        previewDiv.appendChild(titleDiv);
        
        previewsGrid.appendChild(previewDiv);
    });
}

function setPrimaryMedia(index) {
    primaryMediaIndex = index;
    updateMediaPreviews();
}

function removeMedia(index) {
    selectedFiles.splice(index, 1);
    if (primaryMediaIndex === index) {
        primaryMediaIndex = null;
    } else if (primaryMediaIndex > index) {
        primaryMediaIndex--;
    }
    updateMediaPreviews();
}

@auth
    @if(auth()->user()->isAdmin())
    function editMainContent() {
        document.getElementById('edit-main-heading').value = document.getElementById('main-heading').textContent;
        document.getElementById('edit-main-description').value = document.getElementById('main-description').textContent;
        openModal('content-edit-modal');
    }

    function updateOpacityPreview(value) {
        document.getElementById('opacity-value').textContent = value + '%';
        document.querySelector('.title-overlay').style.opacity = value / 100;
    }
    
    function previewBackgroundImage(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            
            // Check file size (10MB = 10 * 1024 * 1024 bytes)
            if (file.size > 10 * 1024 * 1024) {
                showAlert('File size must be less than 10MB', 'error');
                input.value = '';
                return;
            }
            
            // Check file type
            const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!allowedTypes.includes(file.type)) {
                showAlert('Please select a valid image file (JPEG, PNG, GIF, or WebP)', 'error');
                input.value = '';
                return;
            }
            
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('preview-img').src = e.target.result;
                document.getElementById('image-preview').style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
    }
    
    function removeImagePreview() {
        document.getElementById('background-image').value = '';
        document.getElementById('image-preview').style.display = 'none';
        document.getElementById('preview-img').src = '';
    }
    
    function toggleRemoveBackground(checkbox) {
        const fileInput = document.getElementById('background-image');
        if (checkbox.checked) {
            fileInput.disabled = true;
            removeImagePreview();
        } else {
            fileInput.disabled = false;
        }
    }

    function saveMainContent(event) {
        event.preventDefault();
        const form = event.target;
        const formData = new FormData(form);
        const submitBtn = form.querySelector('[type="submit"]');
        const originalText = submitBtn.textContent;
        
        // Show upload progress if image is being uploaded
        const hasImage = formData.get('background_image') && formData.get('background_image').name;
        if (hasImage) {
            document.getElementById('upload-progress').style.display = 'block';
            submitBtn.textContent = 'Processing...';
        }
        
        submitBtn.disabled = true;
        
        fetch('{{ route("admin.site-content.update") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': window.Laravel.csrfToken
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('main-heading').textContent = formData.get('main_heading');
                document.getElementById('main-description').textContent = formData.get('main_description');
                
                // Update background image if changed
                if (data.background_image) {
                    const titleSection = document.getElementById('main-title-section');
                    titleSection.style.backgroundImage = `url('${data.background_image}')`;
                    titleSection.style.backgroundSize = 'cover';
                    titleSection.style.backgroundPosition = 'center';
                    titleSection.style.backgroundRepeat = 'no-repeat';
                } else if (formData.get('remove_background')) {
                    const titleSection = document.getElementById('main-title-section');
                    titleSection.style.backgroundImage = '';
                }
                
                // Update overlay opacity
                const opacity = formData.get('overlay_opacity') || 50;
                document.querySelector('.title-overlay').style.opacity = opacity / 100;
                
                closeModal('content-edit-modal');
                showAlert('Content updated successfully', 'success');
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                showAlert(data.message || 'An error occurred', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('An error occurred', 'error');
        })
        .finally(() => {
            // Hide progress bar and restore button
            document.getElementById('upload-progress').style.display = 'none';
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        });
    }

    function editLocation(id, name, description) {
        editingLocation = id;
        document.getElementById('location-modal-title').textContent = 'Edit Location';
        document.getElementById('location-name').value = name;
        document.getElementById('location-description').value = description || '';
        
        // Load existing media for this location
        loadExistingMedia(id);
        
        openModal('location-modal');
    }

    function saveLocation(event) {
        event.preventDefault();
        const form = event.target;
        const formData = new FormData(form);
        
        // Add selected media files
        selectedFiles.forEach((file, index) => {
            formData.append('media_files[]', file);
        });
        
        // Add primary media index
        if (primaryMediaIndex !== null) {
            formData.append('primary_media', primaryMediaIndex);
        }
        
        const submitBtn = form.querySelector('[type="submit"]');
        const submitText = document.getElementById('location-submit-text');
        const submitLoading = document.getElementById('location-submit-loading');
        
        // Show loading state
        submitBtn.disabled = true;
        submitText.style.display = 'none';
        submitLoading.style.display = 'inline';
        
        const url = editingLocation 
            ? `{{ url('admin/locations') }}/${editingLocation}/update`
            : '{{ route("admin.locations.create") }}';
        
        fetch(url, {
            method: 'POST',
            body: formData,
            headers: { 'X-CSRF-TOKEN': window.Laravel.csrfToken }
        })
        .then(res => res.json())
        .then(data => {
            console.log('Save response data:', data);
            console.log('editingLocation at start of success callback:', editingLocation);
            
            if (!data.success) return showAlert(data.message || 'Error', 'error');
            
            // Store the editing state BEFORE calling closeModal (which resets it)
            const wasEditing = editingLocation;
            console.log('wasEditing stored as:', wasEditing);
            
            closeModal('location-modal');
            showAlert(data.message, 'success');
            
            // Update location card dynamically instead of page reload
            if (wasEditing) {
                console.log('Updating existing location card for ID:', wasEditing);
                updateLocationCard(data.location);
            } else {
                console.log('Adding new location card (wasEditing was:', wasEditing, ')');
                addNewLocationCard(data.location);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('An error occurred', 'error');
        })
        .finally(() => {
            // Restore button state
            submitBtn.disabled = false;
            submitText.style.display = 'inline';
            submitLoading.style.display = 'none';
        });
    }

    function deleteLocation(id) {
        if (confirm('Are you sure you want to delete this location? This will also delete all houses and suites in this location.')) {
            fetch(`{{ url('admin/locations') }}/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': window.Laravel.csrfToken
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message, 'success');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    showAlert(data.message || 'An error occurred', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('An error occurred', 'error');
            });
        }
    }

    function resetLocationModal() {
        editingLocation = null;
        selectedFiles = [];
        primaryMediaIndex = null;
        
        document.getElementById('location-modal-title').textContent = 'Add Location';
        document.getElementById('location-form').reset();
        document.getElementById('media-previews-container').style.display = 'none';
        document.getElementById('existing-media-container').style.display = 'none';
        document.getElementById('media-previews').innerHTML = '';
        document.getElementById('existing-media').innerHTML = '';
        
        // Reset button state
        const submitText = document.getElementById('location-submit-text');
        const submitLoading = document.getElementById('location-submit-loading');
        submitText.style.display = 'inline';
        submitLoading.style.display = 'none';
        document.querySelector('#location-form [type="submit"]').disabled = false;
    }

    // Reset form when opening location modal
    document.getElementById('location-modal').addEventListener('click', function(e) {
        if (e.target === this) {
            resetLocationModal();
        }
    });
    
    // Override the closeModal function for location modal to reset state
    const originalCloseModal = window.closeModal;
    window.closeModal = function(modalId) {
        if (modalId === 'location-modal') {
            resetLocationModal();
        }
        originalCloseModal(modalId);
    };
    @endif
@endauth
</script>
@endpush

@include('components.gallery-lightbox')
@endsection