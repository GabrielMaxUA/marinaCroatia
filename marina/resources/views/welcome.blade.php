@extends('layouts.app')

@section('title', 'Marina Croatia - Luxury Croatian Accommodations')

@section('content')
<!-- Main Title Section -->
<section class="main-title">
    @auth
        @if(auth()->user()->isAdmin())
            <button class="edit-btn" onclick="editMainContent()">✏️ Edit Content</button>
        @endif
    @endauth
    <h1 id="main-heading">{{ $mainHeading ?? 'Luxury Croatian Accommodations' }}</h1>
    <p id="main-description">{{ $mainDescription ?? 'We are a premium travel agency specializing in exclusive accommodations along the Croatian coast.' }}</p>
</section>

<!-- Locations Grid -->
<section class="container">
    <div class="locations-grid">
        @if($locations && $locations->count() > 0)
            @foreach($locations as $location)
            <div class="location-card" onclick="viewLocation({{ $location->id }})">
                @auth
                    @if(auth()->user()->isAdmin())
                        <div class="admin-controls">
                            <button class="edit-btn" onclick="event.stopPropagation(); editLocation({{ $location->id }}, '{{ $location->name }}', '{{ $location->description }}')" title="Edit Location">✏️</button>
                            <button class="delete-btn" onclick="event.stopPropagation(); deleteLocation({{ $location->id }})" title="Delete Location">×</button>
                        </div>
                    @endif
                @endauth
                <div class="location-image">
                    <div class="placeholder-image">
                        <span>{{ $location->name }}</span>
                    </div>
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

@auth
    @if(auth()->user()->isAdmin())
        <!-- Content Edit Modal -->
        <div id="content-edit-modal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Edit Main Content</h3>
                    <button class="modal-close" onclick="closeModal('content-edit-modal')">×</button>
                </div>
                <form id="content-edit-form" onsubmit="saveMainContent(event)">
                    <div class="form-group">
                        <label for="edit-main-heading">Main Heading:</label>
                        <input type="text" id="edit-main-heading" name="main_heading" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-main-description">Description:</label>
                        <textarea id="edit-main-description" name="main_description" rows="4" required></textarea>
                    </div>
                    <div class="modal-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeModal('content-edit-modal')">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Location Modal -->
        <div id="location-modal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 id="location-modal-title">Add Location</h3>
                    <button class="modal-close" onclick="closeModal('location-modal')">×</button>
                </div>
                <form id="location-form" onsubmit="saveLocation(event)">
                    <div class="form-group">
                        <label for="location-name">Location Name:</label>
                        <input type="text" id="location-name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="location-description">Description (optional):</label>
                        <textarea id="location-description" name="description" rows="3"></textarea>
                    </div>
                    <div class="modal-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeModal('location-modal')">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Location</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endauth

@push('styles')
<style>
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
        border-top: 1px solid #f3f4f6;
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
    }
</style>
@endpush

@push('scripts')
<script>
let editingLocation = null;

function viewLocation(locationId) {
    window.location.href = `/locations/${locationId}`;
}

@auth
    @if(auth()->user()->isAdmin())
    function editMainContent() {
        document.getElementById('edit-main-heading').value = document.getElementById('main-heading').textContent;
        document.getElementById('edit-main-description').value = document.getElementById('main-description').textContent;
        openModal('content-edit-modal');
    }

    function saveMainContent(event) {
        event.preventDefault();
        const form = event.target;
        const formData = new FormData(form);
        
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
                closeModal('content-edit-modal');
                showAlert('Content updated successfully', 'success');
            } else {
                showAlert(data.message || 'An error occurred', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('An error occurred', 'error');
        });
    }

    function editLocation(id, name, description) {
        editingLocation = id;
        document.getElementById('location-modal-title').textContent = 'Edit Location';
        document.getElementById('location-name').value = name;
        document.getElementById('location-description').value = description || '';
        openModal('location-modal');
    }

    function saveLocation(event) {
        event.preventDefault();
        const form = event.target;
        const formData = new FormData(form);
        
        const url = editingLocation 
            ? `{{ url('admin/locations') }}/${editingLocation}`
            : '{{ route("admin.locations.create") }}';
        
        if (editingLocation) {
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
                closeModal('location-modal');
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

    // Reset form when opening location modal
    document.getElementById('location-modal').addEventListener('click', function(e) {
        if (e.target === this) {
            editingLocation = null;
            document.getElementById('location-modal-title').textContent = 'Add Location';
            document.getElementById('location-form').reset();
        }
    });
    @endif
@endauth
</script>
@endpush
@endsection