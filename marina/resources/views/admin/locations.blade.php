@extends('layouts.app')

@section('title', 'Location Management - Marina Croatia')

@section('content')
<div class="container">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-title">
            <h1>Location Management</h1>
            <p>Manage locations and regional settings</p>
        </div>
        <div class="page-actions">
            <button class="btn btn-primary" onclick="openModal('location-modal')">+ Add New Location</button>
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
                    <label for="search">Search by Name or Description:</label>
                    <input type="text" id="search" name="search" 
                           value="{{ request('search') }}" 
                           placeholder="Enter location name or description...">
                </div>
                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary">Search</button>
                    <a href="{{ route('admin.locations') }}" class="btn btn-secondary">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-error">
            {{ session('error') }}
        </div>
    @endif

    <!-- Locations Grid -->
    <div class="houses-grid">
        @if($locations->count() > 0)
            @foreach($locations as $location)
            <div class="house-card">
                <div class="house-image">
                    <div class="placeholder-image">
                        <span>{{ $location->name }}</span>
                    </div>
                    <div class="house-badge" onclick="event.stopPropagation(); viewLocationDetails({{ $location->id }})">
                        <span>View Location</span>
                    </div>
                </div>
                
                <div class="house-info">
                    <div class="house-header">
                        <h3>{{ $location->name }}</h3>
                        <div class="house-actions">
                            <button class="action-btn edit" onclick="editLocation({{ $location->id }})" title="Edit Location">
                                ✏️
                            </button>
                            <button class="action-btn delete" onclick="deleteLocation({{ $location->id }})" title="Delete Location">
                                ×
                            </button>
                        </div>
                    </div>
                    
                    <div class="house-details">
                        @if($location->description)
                            <div class="detail-item">
                                <span class="label">📝</span>
                                <span class="value">{{ Str::limit($location->description, 40) }}</span>
                            </div>
                        @endif
                        
                        <div class="detail-item">
                            <span class="label">🏘️</span>
                            <span class="value">{{ $location->houses->count() }} Houses</span>
                        </div>
                        
                        <div class="detail-item">
                            <span class="label">🏠</span>
                            <span class="value">{{ $location->houses->sum(function($house) { return $house->suites->count(); }) }} Suites</span>
                        </div>
                        
                        <div class="detail-item">
                            <span class="label">📅</span>
                            <span class="value">{{ \Carbon\Carbon::parse($location->created_at)->format('M d, Y') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
            
            <!-- Add New Location Card -->
            <div class="house-card add-new" onclick="openModal('location-modal')">
                <div class="add-new-content">
                    <div class="add-icon">+</div>
                    <h3>Add New Location</h3>
                    <p>Create a new location</p>
                </div>
            </div>
        @else
            <div class="empty-state">
                <div class="empty-icon">🏖️</div>
                <h3>No Locations Found</h3>
                <p>{{ request('search') ? 'No locations match your search criteria.' : 'You haven\'t created any locations yet.' }}</p>
                <button class="btn btn-primary" onclick="openModal('location-modal')">+ Add First Location</button>
            </div>
        @endif
    </div>

    <!-- Pagination -->
    @if($locations->hasPages())
        <div class="pagination-wrapper">
            {{ $locations->appends(request()->query())->links() }}
        </div>
    @endif
</div>

<!-- Location Modal -->
<div id="location-modal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="location-modal-title">Add New Location</h3>
            <button class="modal-close" onclick="closeModal('location-modal')">×</button>
        </div>
        <div class="modal-body">
            <form id="location-form" method="POST" action="{{ route('admin.locations.create') }}">
                @csrf
                <input type="hidden" id="location-method" name="_method" value="">
                
                <div class="form-group">
                    <label for="location-name">Location Name *</label>
                    <input type="text" id="location-name" name="name" required maxlength="100" 
                           placeholder="Enter location name...">
                </div>
                
                <div class="form-group">
                    <label for="location-description">Description</label>
                    <textarea id="location-description" name="description" rows="3" maxlength="500"
                              placeholder="Enter location description..."></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('location-modal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Location</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    .alert {
        padding: 1rem 1.5rem;
        border-radius: 8px;
        margin-bottom: 2rem;
        font-weight: 500;
    }
    
    .alert-success {
        background-color: #d1fae5;
        color: #065f46;
        border: 1px solid #a7f3d0;
    }
    
    .alert-error {
        background-color: #fee2e2;
        color: #991b1b;
        border: 1px solid #fca5a5;
    }

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
        border: 2px dashed #d1d5db;
        border-radius: 12px;
    }
    
    .empty-icon {
        font-size: 4rem;
        margin-bottom: 1rem;
    }
    
    .empty-state h3 {
        color: #6b7280;
        margin-bottom: 0.5rem;
    }
    
    .empty-state p {
        color: #9ca3af;
        margin-bottom: 2rem;
    }
    
    @media (max-width: 768px) {
        .page-header {
            flex-direction: column;
            gap: 1rem;
            align-items: stretch;
        }
        
        .houses-grid {
            grid-template-columns: 1fr;
        }
        
        .house-header {
            flex-direction: column;
            gap: 1rem;
        }
        
        .house-actions {
            margin-left: 0;
        }
    }
</style>
@endpush

@push('scripts')
<script>
let editingLocation = null;

function editLocation(locationId) {
    // Redirect to edit page with URL parameter
    window.location.href = `/admin/locations?edit=${locationId}`;
}

function viewLocationDetails(locationId) {
    // Could redirect to location houses view or show details modal
    window.location.href = `/locations/${locationId}`;
}

@if($editLocation)
// Auto-populate and show edit modal
document.addEventListener('DOMContentLoaded', function() {
    editingLocation = {{ $editLocation->id }};
    document.getElementById('location-modal-title').textContent = 'Edit Location';
    document.getElementById('location-method').value = 'PUT';
    document.getElementById('location-form').action = '/admin/locations/{{ $editLocation->id }}';
    
    document.getElementById('location-name').value = '{{ $editLocation->name }}';
    document.getElementById('location-description').value = '{{ $editLocation->description ?? '' }}';
    
    openModal('location-modal');
});
@endif

function deleteLocation(locationId) {
    if (confirm('Are you sure you want to delete this location? This will also delete all associated houses and suites.')) {
        fetch(`/admin/locations/${locationId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Error deleting location');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting location');
        });
    }
}

// Reset form when opening modal
document.getElementById('location-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        editingLocation = null;
        document.getElementById('location-modal-title').textContent = 'Add New Location';
        document.getElementById('location-method').value = '';
        document.getElementById('location-form').action = '{{ route("admin.locations.create") }}';
        document.getElementById('location-form').reset();
    }
});
</script>
@endpush