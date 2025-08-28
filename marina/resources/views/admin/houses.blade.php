@extends('layouts.app')

@section('title', 'House Management - Marina Croatia')

@section('content')
<div class="container">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-title">
            <h1>House Management</h1>
            <p>Manage properties and house information</p>
        </div>
        <div class="page-actions">
            <button class="btn btn-primary" onclick="openModal('house-modal')">+ Add New House</button>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="card filters-card">
        <div class="card-header">
            <h3>Search & Filter</h3>
        </div>
        <div class="card-body">
            <form method="GET" class="filters-form">
                <div class="filter-group">
                    <label for="search">Search by Name or Address:</label>
                    <input type="text" id="search" name="search" 
                           value="{{ request('search') }}" 
                           placeholder="Enter house name or address...">
                </div>
                
                <div class="filter-group">
                    <label for="location_filter">Filter by Location:</label>
                    <select id="location_filter" name="location_id">
                        <option value="">All Locations</option>
                        @foreach($locations as $location)
                        <option value="{{ $location->id }}" {{ request('location_id') == $location->id ? 'selected' : '' }}>
                            {{ $location->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="owner_filter">Filter by Owner:</label>
                    <select id="owner_filter" name="owner_id">
                        <option value="">All Owners</option>
                        @foreach($owners as $owner)
                        <option value="{{ $owner->id }}" {{ request('owner_id') == $owner->id ? 'selected' : '' }}>
                            {{ $owner->full_name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                    <a href="{{ route('admin.houses') }}" class="btn btn-secondary">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Houses Grid -->
    <div class="houses-grid">
        @if($houses->count() > 0)
            @foreach($houses as $house)
            <div class="house-card">
                <div class="house-image">
                    @if($house->images && $house->images->count() > 0)
                        <img src="{{ $house->images->first()->image_url }}" alt="{{ $house->name }}" />
                    @else
                        <div class="placeholder-image">
                            <span>{{ $house->name }}</span>
                        </div>
                    @endif
                    <div class="house-badge" onclick="event.stopPropagation(); viewHouseGallery({{ $house->id }})">
                        <span>View House</span>
                    </div>
                </div>
                
                <div class="house-info">
                    <div class="house-header">
                        <h3>{{ $house->name }}</h3>
                        <div class="house-actions">
                            <button class="action-btn edit" onclick="editHouse({{ $house->id }})" title="Edit House">
                                ✏️
                            </button>
                            <button class="action-btn calendar" onclick="viewHouseBookings({{ $house->id }})" title="View Bookings">
                                📅
                            </button>
                            <button class="action-btn delete" onclick="deleteHouse({{ $house->id }})" title="Delete House">
                                ×
                            </button>
                        </div>
                    </div>
                    
                    <div class="house-details">
                        <div class="detail-item">
                            <span class="label">📍</span>
                            <span class="value">{{ $house->location->name }}</span>
                        </div>
                        
                        <div class="detail-item">
                            <span class="label">👤</span>
                            <span class="value">{{ $house->owner ? $house->owner->full_name : 'Unassigned' }}</span>
                        </div>
                        
                        @if($house->distance_to_sea)
                        <div class="detail-item">
                            <span class="label">🌊</span>
                            <span class="value">{{ $house->distance_to_sea }}</span>
                        </div>
                        @endif
                        
                        @if($house->parking_available)
                        <div class="detail-item">
                            <span class="label">🚗</span>
                            <span class="value">Parking Available</span>
                        </div>
                        @endif
                        
                        <div class="detail-item">
                            <span class="label">🏠</span>
                            <span class="value">{{ $house->suites->count() }} Suite{{ $house->suites->count() !== 1 ? 's' : '' }}</span>
                        </div>
                    </div>
                    
                    <div class="house-footer">
                        <button class="btn btn-outline" onclick="manageSuites({{ $house->id }}, '{{ $house->name }}')">
                            Manage Suites
                        </button>
                        <button class="btn btn-primary" onclick="viewHouseBookings({{ $house->id }})">
                            View Bookings
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
            
            <!-- Add New House Card -->
            <div class="house-card add-new" onclick="openModal('house-modal')">
                <div class="add-new-content">
                    <div class="add-icon">+</div>
                    <h3>Add New House</h3>
                    <p>Create a new property listing</p>
                </div>
            </div>
        @else
            <div class="empty-state">
                <h3>No Houses Found</h3>
                <p>
                    @if(request()->hasAny(['search', 'location_id', 'owner_id']))
                        No houses match your current filters. Try adjusting your search criteria.
                    @else
                        No houses have been created yet. Create your first house to get started!
                    @endif
                </p>
                <button class="btn btn-primary" onclick="openModal('house-modal')">Add First House</button>
            </div>
        @endif
    </div>

    <!-- Pagination -->
    @if(method_exists($houses, 'hasPages') && $houses->hasPages())
    <div class="pagination-wrapper">
        {{ $houses->appends(request()->query())->links() }}
    </div>
    @endif
</div>

<!-- House Modal (Add/Edit) -->
<div id="house-modal" class="modal">
    <div class="modal-content large">
        <div class="modal-header">
            <h3 id="house-modal-title">Add New House</h3>
            <button class="modal-close" onclick="closeModal('house-modal')">×</button>
        </div>
        <form id="house-form" onsubmit="saveHouse(event)">
            <div class="form-row">
                <div class="form-group">
                    <label for="house-name">House Name:</label>
                    <input type="text" id="house-name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="house-location">Location:</label>
                    <select id="house-location" name="location_id" required>
                        <option value="">Select Location</option>
                        @foreach($locations as $location)
                        <option value="{{ $location->id }}">{{ $location->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label for="house-owner">Assign to Owner:</label>
                <select id="house-owner" name="owner_id" required>
                    <option value="">Select Owner</option>
                    @foreach($owners as $owner)
                    <option value="{{ $owner->id }}">{{ $owner->full_name }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="house-address">Street Address:</label>
                    <input type="text" id="house-address" name="street_address" required>
                </div>
                <div class="form-group">
                    <label for="house-number">House Number:</label>
                    <input type="text" id="house-number" name="house_number">
                </div>
            </div>
            
            <div class="form-group">
                <label for="house-distance">Distance to Sea:</label>
                <input type="text" id="house-distance" name="distance_to_sea" placeholder="e.g., 50m to sea">
            </div>
            
            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" id="house-parking" name="parking_available">
                    <span class="checkmark"></span>
                    Parking Available
                </label>
            </div>
            
            <div class="form-group">
                <label for="house-parking-desc">Parking Description:</label>
                <input type="text" id="house-parking-desc" name="parking_description" placeholder="e.g., Private garage">
            </div>
            
            <div class="form-group">
                <label for="house-description">Description:</label>
                <textarea id="house-description" name="description" rows="3" placeholder="Describe the house..."></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="house-owner-phone">Owner Phone:</label>
                    <input type="tel" id="house-owner-phone" name="owner_phone" placeholder="+385 xx xxx xxxx">
                </div>
                <div class="form-group">
                    <label for="house-owner-email">Owner Email:</label>
                    <input type="email" id="house-owner-email" name="owner_email">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="house-bank-account">Bank Account Number:</label>
                    <input type="text" id="house-bank-account" name="bank_account_number">
                </div>
                <div class="form-group">
                    <label for="house-bank-name">Bank Name:</label>
                    <input type="text" id="house-bank-name" name="bank_name">
                </div>
            </div>
            
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="closeModal('house-modal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save House</button>
            </div>
        </form>
    </div>
</div>

@include('components.gallery-lightbox')

@push('styles')
<style>
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 2rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid #e5e7eb;
    }
    
    .page-title h1 {
        font-size: 2rem;
        font-weight: bold;
        color: #1f2937;
        margin: 0 0 0.5rem 0;
    }
    
    .page-title p {
        color: #6b7280;
        margin: 0;
    }
    
    .filters-card {
        margin-bottom: 2rem;
    }
    
    .filters-form {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr auto;
        gap: 1rem;
        align-items: end;
    }
    
    .filter-group {
        display: flex;
        flex-direction: column;
    }
    
    .filter-group label {
        font-size: 0.875rem;
        font-weight: 500;
        color: #374151;
        margin-bottom: 0.5rem;
    }
    
    .filter-actions {
        display: flex;
        gap: 0.5rem;
    }
    
    .houses-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 1.5rem;
    }
    
    .house-card {
        background: white;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        transition: all 0.2s;
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }
    
    .house-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        transform: translateY(-2px);
    }
    
    .house-image {
        height: 200px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        position: relative;
        overflow: hidden;
    }
    
    .house-image img {
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
    
    .house-badge {
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
    
    .house-badge:hover {
        background: rgba(0,0,0,0.9);
        transform: scale(1.05);
    }
    
    .house-info {
        padding: 1.5rem;
        display: flex;
        flex-direction: column;
        flex-grow: 1;
    }
    
    .house-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1rem;
    }
    
    .house-header h3 {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1f2937;
        margin: 0;
        flex-grow: 1;
    }
    
    .house-actions {
        display: flex;
        gap: 0.5rem;
        margin-left: 1rem;
    }
    
    .action-btn {
        width: 32px;
        height: 32px;
        border: 1px solid #d1d5db;
        background: white;
        border-radius: 6px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
        font-size: 14px;
    }
    
    .action-btn:hover {
        background: #f3f4f6;
    }
    
    .action-btn.edit:hover {
        background: #fbbf24;
        color: white;
        border-color: #fbbf24;
    }
    
    .action-btn.calendar:hover {
        background: #3b82f6;
        color: white;
        border-color: #3b82f6;
    }
    
    .action-btn.delete:hover {
        background: #ef4444;
        color: white;
        border-color: #ef4444;
    }
    
    .house-details {
        flex-grow: 1;
    }
    
    .detail-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.5rem 0;
        border-bottom: 1px solid #f3f4f6;
    }
    
    .detail-item:last-child {
        border-bottom: none;
    }
    
    .detail-item .label {
        font-size: 1rem;
        width: 20px;
        text-align: center;
    }
    
    .detail-item .value {
        font-size: 0.875rem;
        color: #1f2937;
        font-weight: 500;
    }
    
    .house-footer {
        padding-top: 1rem;
        margin-top: 1rem;
        border-top: 1px solid #f3f4f6;
        display: flex;
        gap: 0.75rem;
    }
    
    .btn.btn-outline {
        border: 1px solid #d1d5db;
        background: transparent;
        color: #374151;
        flex: 1;
    }
    
    .btn.btn-outline:hover {
        background: #f3f4f6;
    }
    
    .btn.btn-primary {
        flex: 1;
    }
    
    .house-card.add-new {
        border: 2px dashed #d1d5db;
        background: #f9fafb;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 400px;
        cursor: pointer;
    }
    
    .house-card.add-new:hover {
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
        font-weight: 300;
    }
    
    .empty-state {
        grid-column: 1 / -1;
        text-align: center;
        padding: 4rem 2rem;
        background: white;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
    }
    
    .empty-state h3 {
        color: #6b7280;
        margin-bottom: 1rem;
    }
    
    .empty-state p {
        color: #9ca3af;
        margin-bottom: 2rem;
        max-width: 500px;
        margin-left: auto;
        margin-right: auto;
    }
    
    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }
    
    .checkbox-label {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        cursor: pointer;
        font-weight: 500;
    }
    
    .pagination-wrapper {
        margin-top: 2rem;
        display: flex;
        justify-content: center;
    }
    
    @media (max-width: 768px) {
        .page-header {
            flex-direction: column;
            gap: 1rem;
            align-items: stretch;
        }
        
        .filters-form {
            grid-template-columns: 1fr;
        }
        
        .houses-grid {
            grid-template-columns: 1fr;
        }
        
        .form-row {
            grid-template-columns: 1fr;
        }
        
        .house-footer {
            flex-direction: column;
        }
        
        .house-actions {
            margin-left: 0;
            margin-top: 0.5rem;
        }
    }
</style>
@endpush

@push('scripts')
<script>
let editingHouse = null;

function editHouse(id) {
    const houses = @json($houses);
    const house = houses.data ? houses.data.find(h => h.id === id) : houses.find(h => h.id === id);
    
    if (house) {
        editingHouse = id;
        document.getElementById('house-modal-title').textContent = 'Edit House';
        document.getElementById('house-name').value = house.name;
        document.getElementById('house-location').value = house.location_id;
        document.getElementById('house-owner').value = house.owner_id || '';
        document.getElementById('house-address').value = house.street_address;
        document.getElementById('house-number').value = house.house_number || '';
        document.getElementById('house-distance').value = house.distance_to_sea || '';
        document.getElementById('house-parking').checked = house.parking_available;
        document.getElementById('house-parking-desc').value = house.parking_description || '';
        document.getElementById('house-description').value = house.description || '';
        document.getElementById('house-owner-phone').value = house.owner_phone || '';
        document.getElementById('house-owner-email').value = house.owner_email || '';
        document.getElementById('house-bank-account').value = house.bank_account_number || '';
        document.getElementById('house-bank-name').value = house.bank_name || '';
        openModal('house-modal');
    }
}

function saveHouse(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    
    const url = editingHouse 
        ? `{{ url('admin/houses') }}/${editingHouse}`
        : '{{ route("admin.houses.create") }}';
    
    if (editingHouse) {
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
            closeModal('house-modal');
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

function deleteHouse(id) {
    if (confirm('Are you sure you want to delete this house? This will also delete all suites and bookings.')) {
        fetch(`{{ url('admin/houses') }}/${id}`, {
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

function viewHouseGallery(houseId) {
    fetch(`/houses/${houseId}/gallery`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showLightbox(data.house_name + ' Gallery', data.media);
            } else {
                showLightbox(data.house_name + ' Gallery', []);
            }
        })
        .catch(error => {
            console.error('Error loading gallery:', error);
            showLightbox('House Gallery', []);
        });
}

function manageSuites(houseId, houseName) {
    // Navigate to suite management or open modal
    window.location.href = `/houses/${houseId}`;
}

function viewHouseBookings(houseId) {
    // Navigate to bookings page filtered by this house
    window.location.href = `{{ route('admin.bookings') }}?house_id=${houseId}`;
}

// Reset form when opening modal
document.getElementById('house-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        editingHouse = null;
        document.getElementById('house-modal-title').textContent = 'Add New House';
        document.getElementById('house-form').reset();
    }
});
</script>
@endpush
@endsection