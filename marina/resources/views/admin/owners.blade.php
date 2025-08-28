@extends('layouts.app')

@section('title', 'Owner Management - Marina Croatia')

@section('content')
<div class="container">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-title">
            <h1>Owner Management</h1>
            <p>Manage property owners and their accounts</p>
        </div>
        <div class="page-actions">
            <button class="btn btn-primary" onclick="openModal('owner-modal')">+ Add New Owner</button>
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
                    <label for="search">Search by Name or Email:</label>
                    <input type="text" id="search" name="search" 
                           value="{{ request('search') }}" 
                           placeholder="Enter name or email...">
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
                    <label for="status_filter">Status:</label>
                    <select id="status_filter" name="status">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                
                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                    <a href="{{ route('admin.owners') }}" class="btn btn-secondary">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Owners Grid -->
    <div class="owners-grid">
        @if($owners->count() > 0)
            @foreach($owners as $owner)
            <div class="owner-card">
                <div class="owner-header">
                    <div class="owner-info">
                        <h3>{{ $owner->full_name }}</h3>
                        <span class="status-badge {{ $owner->is_active ? 'active' : 'inactive' }}">
                            {{ $owner->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                    <div class="owner-actions">
                        <button class="action-btn edit" onclick="editOwner({{ $owner->id }})" title="Edit Owner">
                            ✏️
                        </button>
                        <button class="action-btn reset" onclick="resetOwnerPassword({{ $owner->id }})" title="Reset Password">
                            🔑
                        </button>
                        <button class="action-btn {{ $owner->is_active ? 'deactivate' : 'activate' }}" 
                                onclick="toggleOwnerStatus({{ $owner->id }}, {{ $owner->is_active ? 'false' : 'true' }})" 
                                title="{{ $owner->is_active ? 'Deactivate' : 'Activate' }}">
                            {{ $owner->is_active ? '⏸️' : '▶️' }}
                        </button>
                    </div>
                </div>
                
                <div class="owner-details">
                    <div class="detail-item">
                        <span class="label">Email:</span>
                        <span class="value">{{ $owner->email }}</span>
                    </div>
                    
                    @if($owner->phone)
                    <div class="detail-item">
                        <span class="label">Phone:</span>
                        <span class="value">{{ $owner->phone }}</span>
                    </div>
                    @endif
                    
                    <div class="detail-item">
                        <span class="label">Properties:</span>
                        <span class="value">{{ $owner->houses->count() }} Houses</span>
                    </div>
                    
                    <div class="detail-item">
                        <span class="label">Total Suites:</span>
                        <span class="value">{{ $owner->houses->sum(fn($h) => $h->suites->count()) }} Suites</span>
                    </div>
                </div>
                
                <div class="owner-footer">
                    <button class="btn btn-outline" onclick="viewOwnerProperties({{ $owner->id }}, '{{ $owner->full_name }}')">
                        View Properties
                    </button>
                    <button class="btn btn-primary" onclick="viewOwnerBookings({{ $owner->id }}, '{{ $owner->full_name }}')">
                        View Bookings
                    </button>
                </div>
            </div>
            @endforeach
            
            <!-- Add New Owner Card -->
            <div class="owner-card add-new" onclick="openModal('owner-modal')">
                <div class="add-new-content">
                    <div class="add-icon">+</div>
                    <h3>Add New Owner</h3>
                    <p>Create a new property owner account</p>
                </div>
            </div>
        @else
            <div class="empty-state">
                <h3>No Owners Found</h3>
                <p>
                    @if(request()->hasAny(['search', 'location_id', 'status']))
                        No owners match your current filters. Try adjusting your search criteria.
                    @else
                        No property owners have been created yet. Create your first owner to get started!
                    @endif
                </p>
                <button class="btn btn-primary" onclick="openModal('owner-modal')">Add First Owner</button>
            </div>
        @endif
    </div>

    <!-- Pagination -->
    @if(method_exists($owners, 'hasPages') && $owners->hasPages())
    <div class="pagination-wrapper">
        {{ $owners->appends(request()->query())->links() }}
    </div>
    @endif
</div>

<!-- Owner Modal (Add/Edit) -->
<div id="owner-modal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="owner-modal-title">Add New Owner</h3>
            <button class="modal-close" onclick="closeModal('owner-modal')">×</button>
        </div>
        <form id="owner-form" onsubmit="saveOwner(event)">
            <div class="form-row">
                <div class="form-group">
                    <label for="owner-first-name">First Name:</label>
                    <input type="text" id="owner-first-name" name="first_name" required>
                </div>
                <div class="form-group">
                    <label for="owner-last-name">Last Name:</label>
                    <input type="text" id="owner-last-name" name="last_name" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="owner-email">Email Address:</label>
                <input type="email" id="owner-email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="owner-phone">Phone Number:</label>
                <input type="tel" id="owner-phone" name="phone" placeholder="+385 xx xxx xxxx">
            </div>
            
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="closeModal('owner-modal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Owner</button>
            </div>
        </form>
    </div>
</div>

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
    
    .owners-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 1.5rem;
    }
    
    .owner-card {
        background: white;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        transition: all 0.2s;
        overflow: hidden;
    }
    
    .owner-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        transform: translateY(-2px);
    }
    
    .owner-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        padding: 1.5rem 1.5rem 0 1.5rem;
    }
    
    .owner-info h3 {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1f2937;
        margin: 0 0 0.5rem 0;
    }
    
    .status-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    
    .status-badge.active {
        background: #dcfce7;
        color: #166534;
    }
    
    .status-badge.inactive {
        background: #fef2f2;
        color: #dc2626;
    }
    
    .owner-actions {
        display: flex;
        gap: 0.5rem;
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
    
    .action-btn.reset:hover {
        background: #3b82f6;
        color: white;
        border-color: #3b82f6;
    }
    
    .action-btn.deactivate:hover {
        background: #ef4444;
        color: white;
        border-color: #ef4444;
    }
    
    .action-btn.activate:hover {
        background: #10b981;
        color: white;
        border-color: #10b981;
    }
    
    .owner-details {
        padding: 1rem 1.5rem;
    }
    
    .detail-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.5rem 0;
        border-bottom: 1px solid #f3f4f6;
    }
    
    .detail-item:last-child {
        border-bottom: none;
    }
    
    .detail-item .label {
        font-size: 0.875rem;
        color: #6b7280;
        font-weight: 500;
    }
    
    .detail-item .value {
        font-size: 0.875rem;
        color: #1f2937;
        font-weight: 500;
    }
    
    .owner-footer {
        padding: 0 1.5rem 1.5rem 1.5rem;
        display: flex;
        gap: 0.75rem;
    }
    
    .btn.btn-outline {
        border: 1px solid #d1d5db;
        background: transparent;
        color: #374151;
    }
    
    .btn.btn-outline:hover {
        background: #f3f4f6;
    }
    
    .owner-card.add-new {
        border: 2px dashed #d1d5db;
        background: #f9fafb;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 300px;
        cursor: pointer;
    }
    
    .owner-card.add-new:hover {
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
        
        .owners-grid {
            grid-template-columns: 1fr;
        }
        
        .form-row {
            grid-template-columns: 1fr;
        }
        
        .owner-footer {
            flex-direction: column;
        }
    }
</style>
@endpush

@push('scripts')
<script>
let editingOwner = null;

function editOwner(id) {
    const owners = @json($owners);
    const owner = owners.data ? owners.data.find(o => o.id === id) : owners.find(o => o.id === id);
    
    if (owner) {
        editingOwner = id;
        document.getElementById('owner-modal-title').textContent = 'Edit Owner';
        document.getElementById('owner-first-name').value = owner.first_name;
        document.getElementById('owner-last-name').value = owner.last_name;
        document.getElementById('owner-email').value = owner.email;
        document.getElementById('owner-phone').value = owner.phone || '';
        openModal('owner-modal');
    }
}

function saveOwner(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    
    const url = editingOwner 
        ? `{{ url('admin/owners') }}/${editingOwner}`
        : '{{ route("admin.owners.create") }}';
    
    if (editingOwner) {
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
            closeModal('owner-modal');
            if (!editingOwner && data.temporary_password) {
                showAlert(`Owner created successfully! Temporary password: ${data.temporary_password}`, 'success', 8000);
            } else {
                showAlert(data.message, 'success');
            }
            setTimeout(() => {
                window.location.reload();
            }, editingOwner ? 1000 : 8000);
        } else {
            showAlert(data.message || 'An error occurred', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('An error occurred', 'error');
    });
}

function resetOwnerPassword(id) {
    if (confirm('Are you sure you want to reset this owner\'s password?')) {
        fetch(`{{ url('admin/owners') }}/${id}/reset-password`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': window.Laravel.csrfToken
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert(`Password reset successfully! New password: ${data.temporary_password}`, 'success', 8000);
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

function toggleOwnerStatus(id, activate) {
    const action = activate === 'true' ? 'activate' : 'deactivate';
    if (confirm(`Are you sure you want to ${action} this owner?`)) {
        const formData = new FormData();
        formData.append('is_active', activate);
        formData.append('_method', 'PUT');
        
        fetch(`{{ url('admin/owners') }}/${id}`, {
            method: 'POST',
            body: formData,
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

function viewOwnerProperties(ownerId, ownerName) {
    // Navigate to houses page filtered by this owner
    window.location.href = `{{ route('admin.houses') }}?owner_id=${ownerId}`;
}

function viewOwnerBookings(ownerId, ownerName) {
    // Navigate to bookings page filtered by this owner
    window.location.href = `{{ route('admin.bookings') }}?owner_id=${ownerId}`;
}

// Reset form when opening modal
document.getElementById('owner-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        editingOwner = null;
        document.getElementById('owner-modal-title').textContent = 'Add New Owner';
        document.getElementById('owner-form').reset();
    }
});
</script>
@endpush
@endsection