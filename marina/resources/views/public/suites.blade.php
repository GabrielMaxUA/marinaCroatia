@extends('layouts.app')

@section('title', 'Suites in {{ $house->name }} - Marina Croatia')

@section('content')
<!-- Breadcrumb -->
<div class="breadcrumb-container">
    <div class="container">
        <nav class="breadcrumb">
            <a href="{{ route('home') }}">Home</a>
            <span class="separator">></span>
            <a href="{{ route('public.houses', $house->location->id) }}">{{ $house->location->name }}</a>
            <span class="separator">></span>
            <span class="current">{{ $house->name }}</span>
        </nav>
    </div>
</div>

<!-- House Header -->
<section class="house-header">
    <div class="container">
        <div class="house-header-content">
            <div class="house-header-info">
                <h1>{{ $house->name }}</h1>
                <div class="house-location">
                    <span class="icon">📍</span>
                    <span>{{ $house->location->name }}</span>
                </div>
                
                <div class="house-features">
                    @if($house->distance_to_sea)
                    <div class="feature">
                        <span class="icon">🌊</span>
                        <span>{{ $house->distance_to_sea }}</span>
                    </div>
                    @endif
                    
                    @if($house->parking_available)
                    <div class="feature">
                        <span class="icon">🚗</span>
                        <span>{{ $house->parking_description ?: 'Parking Available' }}</span>
                    </div>
                    @endif
                    
                </div>
                
                @if($house->description)
                <p class="house-description">{{ $house->description }}</p>
                @endif
            </div>
            
        </div>
    </div>
</section>

<!-- Suites Grid -->
<section class="container">
    <div class="section-header">
        <h2>Available Suites</h2>
        <p>Choose from our selection of comfortable accommodations</p>
    </div>
    
    <div class="suites-grid">
        @if($suites->count() > 0)
            @foreach($suites as $suite)
            <div class="suite-card">
                @auth
                    @if(auth()->user()->isAdmin())
                        <div class="admin-controls">
                            <button class="edit-btn" onclick="event.stopPropagation(); editSuite({{ $suite->id }})" title="Edit Suite">✏️</button>
                            <button class="calendar-btn" onclick="event.stopPropagation(); viewSuiteCalendar({{ $suite->id }})" title="View Calendar">📅</button>
                            <button class="bank-btn" onclick="event.stopPropagation(); viewOwnerInfo({{ $suite->house->owner->id }}, '{{ $suite->name }}')" title="Owner & Bank Info">🏦</button>
                            <button class="delete-btn" onclick="event.stopPropagation(); deleteSuite({{ $suite->id }})" title="Delete Suite">×</button>
                        </div>
                    @endif
                @endauth
                
                <div class="suite-image">
                    @if($suite->images && $suite->images->count() > 0)
                        <img src="{{ $suite->images->first()->image_url }}" alt="{{ $suite->name }}" />
                    @else
                        <div class="placeholder-image">
                            <span>{{ $suite->name }}</span>
                        </div>
                    @endif
                    <div class="suite-badge" onclick="event.stopPropagation(); viewSuiteGallery({{ $suite->id }})">
                        <span>View Suite</span>
                    </div>
                </div>
                
                <div class="suite-info">
                    <div class="suite-content">
                        <h3>{{ $suite->name }}</h3>
                        
                        <div class="suite-details">
                            <div class="detail-row">
                                <div class="detail-item">
                                    <span class="icon">👥</span>
                                    <span><strong>{{ $suite->capacity_people }}</strong> Guests</span>
                                </div>
                                @if($suite->floor_number)
                                <div class="detail-item">
                                    <span class="icon">🏢</span>
                                    <span>Floor {{ $suite->floor_number }}</span>
                                </div>
                                @endif
                            </div>
                            
                            <div class="detail-row">
                                <div class="detail-item">
                                    <span class="icon">🛏️</span>
                                    <span><strong>{{ $suite->bedrooms }}</strong> {{ Str::plural('Bedroom', $suite->bedrooms) }}</span>
                                </div>
                                <div class="detail-item">
                                    <span class="icon">🚿</span>
                                    <span><strong>{{ $suite->bathrooms }}</strong> {{ Str::plural('Bathroom', $suite->bathrooms) }}</span>
                                </div>
                            </div>
                        </div>
                        
                        @if($suite->description)
                        <p class="suite-description">{{ Str::limit($suite->description, 120) }}</p>
                        @endif
                        
                        @if($suite->amenities && $suite->amenities->count() > 0)
                        <div class="suite-amenities">
                            <h4>Amenities</h4>
                            <div class="amenities-list">
                                @foreach($suite->amenities->take(4) as $amenity)
                                <span class="amenity-tag">{{ $amenity->amenity_name }}</span>
                                @endforeach
                                @if($suite->amenities->count() > 4)
                                <span class="amenity-tag more">+{{ $suite->amenities->count() - 4 }} more</span>
                                @endif
                            </div>
                        </div>
                        @endif
                    </div>
                    
                    <div class="suite-actions">
                        @auth
                            @if(auth()->user()->isAdmin())
                                <button class="btn btn-primary" onclick="viewSuiteCalendar({{ $suite->id }})">
                                    📅 Manage Bookings
                                </button>
                            @elseif(auth()->user()->isOwner() && $house->owner_id === auth()->user()->id)
                                <button class="btn btn-primary" onclick="viewSuiteCalendar({{ $suite->id }})">
                                    📅 My Bookings
                                </button>
                            @endif
                        @endauth
                        
                        @guest
                        <button class="btn btn-success" onclick="checkAvailability({{ $suite->id }})">
                            📅 Check Availability
                        </button>
                        @endguest
                    </div>
                </div>
            </div>
            @endforeach
            
            @auth
                @if(auth()->user()->isAdmin())
                    <div class="suite-card add-new" onclick="openAddSuiteModal()">
                        <div class="add-new-content">
                            <div class="add-icon">+</div>
                            <h3>Add New Suite</h3>
                            <p>Add a new suite to {{ $house->name }}</p>
                        </div>
                    </div>
                @endif
            @endauth
        @else
            <div class="empty-state">
                <h3>No Suites Available</h3>
                <p>There are currently no suites available in {{ $house->name }}.</p>
                @auth
                    @if(auth()->user()->isAdmin())
                        <button class="btn btn-primary" onclick="openAddSuiteModal()">Add First Suite</button>
                    @endif
                @endauth
            </div>
        @endif
    </div>
</section>

@include('components.gallery-lightbox')

@auth
    @if(auth()->user()->isAdmin())
        <!-- Suite Modal (Add/Edit) -->
        <div id="suite-modal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 id="suite-modal-title">Add Suite to {{ $house->name }}</h3>
                    <button class="modal-close" onclick="closeModal('suite-modal')">×</button>
                </div>
                <form id="suite-form" onsubmit="saveSuite(event)">
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label for="suite-name">Suite Name/Number:</label>
                            <input type="text" id="suite-name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="suite-capacity">Capacity (people):</label>
                            <input type="number" id="suite-capacity" name="capacity_people" min="1" max="20" required>
                        </div>
                    </div>
                    
                    <div class="form-grid-3">
                        <div class="form-group">
                            <label for="suite-bedrooms">Bedrooms:</label>
                            <input type="number" id="suite-bedrooms" name="bedrooms" min="0" max="10" required>
                        </div>
                        <div class="form-group">
                            <label for="suite-bathrooms">Bathrooms:</label>
                            <input type="number" id="suite-bathrooms" name="bathrooms" min="0" max="10" required>
                        </div>
                        <div class="form-group">
                            <label for="suite-floor">Floor Number:</label>
                            <input type="number" id="suite-floor" name="floor_number" min="0" max="50">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="suite-description">Description:</label>
                        <textarea id="suite-description" name="description" rows="4"></textarea>
                    </div>
                    
                    <div class="modal-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeModal('suite-modal')">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Suite</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endauth

@push('styles')
<style>
    .breadcrumb-container {
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        padding: 1rem 0;
    }
    
    .breadcrumb {
        font-size: 0.875rem;
        color: #64748b;
    }
    
    .breadcrumb a {
        color: #3b82f6;
        text-decoration: none;
    }
    
    .breadcrumb a:hover {
        text-decoration: underline;
    }
    
    .separator {
        margin: 0 0.5rem;
    }
    
    .current {
        color: #1e293b;
        font-weight: 500;
    }
    
    .house-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 2rem 0;
        position: relative;
    }
    
    .house-header-content {
        display: block;
    }
    
    .house-header h1 {
        font-size: 2.5rem;
        font-weight: bold;
        margin-bottom: 1rem;
    }
    
    .house-location {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 1.125rem;
        margin-bottom: 1.5rem;
        opacity: 0.9;
    }
    
    .house-features {
        display: flex;
        flex-wrap: wrap;
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }
    
    .feature {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.875rem;
        background: rgba(255,255,255,0.2);
        padding: 0.5rem 1rem;
        border-radius: 20px;
    }
    
    .house-description {
        font-size: 1rem;
        line-height: 1.6;
        opacity: 0.9;
        max-width: 600px;
    }
    
    
    .section-header {
        text-align: center;
        margin: 3rem 0;
    }
    
    .section-header h2 {
        font-size: 2rem;
        font-weight: bold;
        color: #1f2937;
        margin-bottom: 0.5rem;
    }
    
    .section-header p {
        color: #6b7280;
        font-size: 1.125rem;
    }
    
    .suites-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 2rem;
        padding-bottom: 2rem;
    }
    
    .suite-card {
        background: white;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        transition: transform 0.3s, box-shadow 0.3s;
        position: relative;
        height: auto;
        display: flex;
        flex-direction: column;
    }
    
    .suite-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.15);
    }
    
    .suite-card.add-new {
        border: 2px dashed #cbd5e0;
        background: #f8fafc;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 400px;
        cursor: pointer;
    }
    
    .suite-card.add-new:hover {
        border-color: #3b82f6;
        background: #eff6ff;
    }
    
    .add-new-content {
        text-align: center;
        color: #6b7280;
        padding: 2rem;
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
        top: 12px;
        right: 12px;
        z-index: 10;
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
    }
    
    .admin-controls .edit-btn,
    .admin-controls .calendar-btn,
    .admin-controls .bank-btn,
    .admin-controls .delete-btn {
        background: rgba(255,255,255,0.95);
        border: none;
        width: 36px;
        height: 36px;
        border-radius: 8px;
        cursor: pointer;
        font-size: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        backdrop-filter: blur(10px);
    }
    
    .admin-controls .edit-btn:hover {
        background: #f59e0b;
        color: white;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(245, 158, 11, 0.4);
    }
    
    .admin-controls .calendar-btn:hover {
        background: #3b82f6;
        color: white;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
    }
    
    .admin-controls .bank-btn:hover {
        background: #10b981;
        color: white;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
    }
    
    .admin-controls .delete-btn {
        color: #ef4444;
        font-weight: bold;
    }
    
    .admin-controls .delete-btn:hover {
        background: #ef4444;
        color: white;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
    }
    
    .suite-image {
        height: 200px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        position: relative;
        overflow: hidden;
    }
    
    .suite-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .placeholder-image {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.25rem;
        font-weight: bold;
        text-align: center;
    }
    
    .suite-badge {
        position: absolute;
        top: 12px;
        left: 12px;
        background: rgba(0,0,0,0.8);
        color: white;
        padding: 0.5rem 0.75rem;
        border-radius: 6px;
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .suite-badge:hover {
        background: rgba(0,0,0,0.9);
        transform: scale(1.05);
    }
    
    .suite-info {
        padding: 1.5rem;
        display: flex;
        flex-direction: column;
        flex-grow: 1;
    }
    
    .suite-content {
        flex-grow: 1;
    }
    
    .suite-info h3 {
        font-size: 1.5rem;
        font-weight: bold;
        color: #1f2937;
        margin: 0 0 1rem 0;
    }
    
    .suite-details {
        margin-bottom: 1rem;
    }
    
    .detail-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.75rem;
    }
    
    .detail-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.875rem;
        color: #6b7280;
        width: 46%;
    }
    
    .detail-item .icon {
        font-size: 1rem;
    }
    
    .suite-description {
        color: #6b7280;
        font-size: 0.875rem;
        line-height: 1.6;
        margin-bottom: 1rem;
    }
    
    .suite-amenities {
        margin-bottom: 1.5rem;
    }
    
    .suite-amenities h4 {
        font-size: 0.875rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.5rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    
    .amenities-list {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    
    .amenity-tag {
        background: #f3f4f6;
        color: #374151;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 500;
    }
    
    .amenity-tag.more {
        background: #e5e7eb;
        color: #6b7280;
    }
    
    .suite-actions {
        border-top: 1px solid #f3f4f6;
        padding-top: 1rem;
        margin-top: 1rem;
    }
    
    .empty-state {
        grid-column: 1 / -1;
        text-align: center;
        padding: 4rem 2rem;
        background: white;
        border-radius: 16px;
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
    
    .form-grid-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }
    
    .form-grid-3 {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 1rem;
    }
    
    @media (max-width: 768px) {
        .suites-grid {
            grid-template-columns: 1fr;
        }
        
        .house-header h1 {
            font-size: 2rem;
        }
        
        .house-features {
            flex-direction: column;
            gap: 0.75rem;
        }
        
        .detail-row {
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .form-grid-2,
        .form-grid-3 {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@push('scripts')
<script>
let editingSuite = null;

@auth
    @if(auth()->user()->isAdmin())
    function openAddSuiteModal() {
        editingSuite = null;
        document.getElementById('suite-modal-title').textContent = 'Add Suite to {{ $house->name }}';
        document.getElementById('suite-form').reset();
        openModal('suite-modal');
    }

    function editSuite(suiteId) {
        fetch(`/admin/suites/${suiteId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const suite = data.suite;
                    editingSuite = suiteId;
                    document.getElementById('suite-modal-title').textContent = 'Edit Suite';
                    
                    document.getElementById('suite-name').value = suite.name;
                    document.getElementById('suite-capacity').value = suite.capacity_people;
                    document.getElementById('suite-bedrooms').value = suite.bedrooms;
                    document.getElementById('suite-bathrooms').value = suite.bathrooms;
                    document.getElementById('suite-floor').value = suite.floor_number || '';
                    document.getElementById('suite-description').value = suite.description || '';
                    
                    openModal('suite-modal');
                } else {
                    showAlert('Error loading suite data', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('Error loading suite data', 'error');
            });
    }

    function saveSuite(event) {
        event.preventDefault();
        const form = event.target;
        const formData = new FormData(form);
        formData.append('house_id', {{ $house->id }});
        
        const url = editingSuite 
            ? `/admin/suites/${editingSuite}`
            : '{{ route("admin.suites.create") }}';
        
        if (editingSuite) {
            formData.append('_method', 'PUT');
        }
        
        fetch(url, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': window.Laravel.csrfToken
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                closeModal('suite-modal');
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

    function deleteSuite(suiteId) {
        if (confirm('Are you sure you want to delete this suite? This will also delete all bookings for this suite.')) {
            fetch(`/admin/suites/${suiteId}`, {
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

    function viewSuiteCalendar(suiteId) {
        window.location.href = `/admin/suites/${suiteId}/calendar`;
    }

    @endif
    
    @if(auth()->user()->isOwner())
    function viewSuiteCalendar(suiteId) {
        window.location.href = `/owner/suites/${suiteId}/calendar`;
    }
    @endif
@endauth

@guest
function checkAvailability(suiteId) {
    showAlert('Please contact us to check availability for this suite!', 'info');
}
@endguest

function viewSuiteGallery(suiteId) {
    fetch(`/suites/${suiteId}/gallery`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showLightbox(data.suite_name + ' Gallery', data.media);
            } else {
                showLightbox(data.suite_name + ' Gallery', []);
            }
        })
        .catch(error => {
            console.error('Error loading gallery:', error);
            showLightbox('Suite Gallery', []);
        });
}
</script>
@endpush
<!-- Include Owner Info Modal Component -->
@include('components.owner-info-modal')

@endsection