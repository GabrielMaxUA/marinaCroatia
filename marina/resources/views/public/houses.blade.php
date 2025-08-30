@extends('layouts.app')

@section('title', 'Houses in {{ $location->name }} - Marina Croatia')

@section('content')
<!-- Breadcrumb -->
<div class="breadcrumb-container">
    <div class="container">
        <nav class="breadcrumb">
            <a href="{{ route('home') }}">Home</a>
            <span class="separator">></span>
            <span class="current">{{ $location->name }}</span>
        </nav>
    </div>
</div>

<!-- Location Header -->
<section class="location-header">
    <div class="container">
        <h1>{{ $location->name }}</h1>
        @if($location->description)
            <p>{{ $location->description }}</p>
        @endif
        <div class="location-stats">
            <span>{{ $houses->count() }} Properties Available</span>
        </div>
    </div>
</section>

<!-- Houses Grid -->
<section class="container">
    <div class="houses-grid">
        @if($houses->count() > 0)
            @foreach($houses as $house)
            <div class="house-card" onclick="viewHouse({{ $house->id }})">
                @auth
                    @if(auth()->user()->isAdmin())
                        <div class="admin-controls">
                            <button class="edit-btn" onclick="event.stopPropagation(); editHouse({{ $house->id }})" title="Edit House">✏️</button>
                            <button class="bank-btn" onclick="event.stopPropagation(); viewOwnerInfo({{ $house->owner->id }}, '{{ $house->name }}')" title="Owner & Bank Info">🏦</button>
                            <button class="delete-btn" onclick="event.stopPropagation(); deleteHouse({{ $house->id }})" title="Delete House">×</button>
                        </div>
                    @endif
                @endauth
                
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
                    <h3>{{ $house->name }}</h3>
                    <div class="house-details">
                        @if($house->distance_to_sea)
                        <div class="detail-item">
                            <span class="icon">🌊</span>
                            <span>{{ $house->distance_to_sea }}</span>
                        </div>
                        @endif
                        
                        @if($house->parking_available)
                        <div class="detail-item">
                            <span class="icon">🚗</span>
                            <span>{{ $house->parking_description ?: 'Parking Available' }}</span>
                        </div>
                        @endif
                    </div>
                    
                    @if($house->description)
                    <p class="house-description">{{ Str::limit($house->description, 100) }}</p>
                    @endif
                    
                </div>
            </div>
            @endforeach
            
            @auth
                @if(auth()->user()->isAdmin())
                    <div class="house-card add-new" onclick="openAddHouseModal()">
                        <div class="add-new-content">
                            <div class="add-icon">+</div>
                            <h3>Add New House</h3>
                            <p>Add a new property to {{ $location->name }}</p>
                        </div>
                    </div>
                @endif
            @endauth
        @else
            <div class="empty-state">
                <h3>No Houses Available</h3>
                <p>There are currently no properties available in {{ $location->name }}.</p>
                @auth
                    @if(auth()->user()->isAdmin())
                        <button class="btn btn-primary" onclick="openAddHouseModal()">Add First House</button>
                    @endif
                @endauth
            </div>
        @endif
    </div>
</section>

@auth
    @if(auth()->user()->isAdmin())
<!-- House Modal (Add/Edit) -->
<div id="house-modal" class="modal">
    <div class="modal-content large">
        <div class="modal-header">
            <h3 id="house-modal-title">Add House</h3>
            <button class="modal-close" onclick="closeModal('house-modal')">×</button>
        </div>

        <form id="house-form" onsubmit="saveHouse(event)">
            
            <!-- Row: House Name + Location -->
            <div class="form-row">
                <div class="form-group">
                    <label for="house-name">House Name:</label>
                    <input type="text" id="house-name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="house-location">Location:</label>
                    <select id="house-location" name="location_id" required>
                      <option value="{{ $location->id }}">{{ $location->name }}</option>
                    </select>
                </div>
            </div>

            <!-- Owner -->
            <div class="form-group">
                <label for="house-owner">Assign to Owner:</label>
                <select id="house-owner" name="owner_id" required>
                    <option value="{{ $house->owner->id ?? '' }}">
                        {{ $house->owner->full_name ?? 'Select Owner' }}
                    </option>
                </select>

            </div>

            <!-- Row: Address + House Number -->
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

            <!-- Distance to Sea -->
            <div class="form-group">
                <label for="house-distance">Distance to Sea:</label>
                <input type="text" id="house-distance" name="distance_to_sea" placeholder="e.g., 50m to sea">
            </div>

            <!-- Row: Parking + Pet Friendly -->
            <div class="form-row">
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" id="house-parking" name="parking_available" value="1"
                            {{ isset($house) && $house->parking_available ? 'checked' : '' }}>
                        <span class="checkmark"></span>
                        Parking Available
                    </label>
                </div>
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" id="house-pet-friendly" name="pet_friendly" value="1"
                            {{ isset($house) && $house->pet_friendly ? 'checked' : '' }}>
                        <span class="checkmark"></span>
                        Pet Friendly
                    </label>
                </div>
            </div>


            <!-- Parking Description -->
            <div class="form-group">
                <label for="house-parking-desc">Parking Description:</label>
                <input type="text" id="house-parking-desc" name="parking_description" placeholder="e.g., Private garage">
            </div>

            <!-- Description -->
            <div class="form-group">
                <label for="house-description">Description:</label>
                <textarea id="house-description" name="description" rows="3" placeholder="Describe the house..."></textarea>
            </div>

            <!-- Actions -->
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="closeModal('house-modal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save House</button>
            </div>
        </form>
    </div>
</div>
@endif

@endauth

@include('components.gallery-lightbox')

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
    
    .location-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 3rem 0;
        position: relative;
        text-align: center;
    }
    
    .location-header h1 {
        font-size: 2.5rem;
        font-weight: bold;
        margin-bottom: 1rem;
    }
    
    .location-header p {
        font-size: 1.125rem;
        opacity: 0.9;
        margin-bottom: 1rem;
        max-width: 600px;
        margin-left: auto;
        margin-right: auto;
    }
    
    .location-stats span {
        background: rgba(255,255,255,0.2);
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.875rem;
        font-weight: 500;
    }
    
    .houses-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 2rem;
        padding: 2rem 0;
    }
    
    .house-card {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        cursor: pointer;
        transition: transform 0.3s, box-shadow 0.3s;
        position: relative;
        height: auto;
    }
    
    .house-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.15);
    }
    
    .house-card.add-new {
        border: 2px dashed #cbd5e0;
        background: #f8fafc;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 300px;
    }
    
    .house-card.add-new:hover {
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
        position: static;
    }
    
    .admin-controls .edit-btn:hover {
        background: #f59e0b;
        color: white;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(245, 158, 11, 0.4);
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
    
    .house-image {
        height: 180px;
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
        top: 8px;
        left: 8px;
        background: rgba(0,0,0,0.8);
        color: white;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 500;
    }
    
    .house-info {
        padding: 1.5rem;
    }
    
    .house-info h3 {
        font-size: 1.25rem;
        font-weight: bold;
        color: #1f2937;
        margin: 0 0 1rem 0;
    }
    
    .house-details {
        margin-bottom: 1rem;
    }
    
    .detail-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 0.5rem;
        font-size: 0.875rem;
        color: #6b7280;
    }
    
    .detail-item .icon {
        font-size: 1rem;
    }
    
    .house-description {
        color: #6b7280;
        font-size: 0.875rem;
        margin-bottom: 1rem;
        line-height: 1.5;
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
    
    .form-grid-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }
    
    .form-grid-3 {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr;
        gap: 1rem;
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
    
    @media (max-width: 768px) {
        .houses-grid {
            grid-template-columns: 1fr;
        }
        
        .location-header h1 {
            font-size: 2rem;
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
let editingHouse = null;

function viewHouse(houseId) {
    window.location.href = `/houses/${houseId}`;
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

@auth
    @if(auth()->user()->isAdmin())
    function openAddHouseModal() {
        editingHouse = null;
        document.getElementById('house-modal-title').textContent = 'Add House to {{ $location->name }}';
        document.getElementById('house-form').reset();
        openModal('house-modal');
    }

    function editHouse(houseId) {
        // Load house data and populate form
        fetch(`/admin/houses/${houseId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const house = data.house;
                    editingHouse = houseId;
                    document.getElementById('house-modal-title').textContent = 'Edit House';
                    
                    document.getElementById('house-name').value = house.name;
                    document.getElementById('house-owner').value = house.owner_id;
                    document.getElementById('house-address').value = house.street_address;
                    document.getElementById('house-number').value = house.house_number || '';
                    document.getElementById('house-distance').value = house.distance_to_sea || '';
                    document.getElementById('house-parking').checked = house.parking_available;
                    document.getElementById('house-parking').checked = house.parking_available;
                    document.getElementById('house-parking-desc').value = house.parking_description || '';
                    document.getElementById('house-description').value = house.description || '';
                    
                    openModal('house-modal');
                } else {
                    showAlert('Error loading house data', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('Error loading house data', 'error');
            });
    }

    // function saveHouse(event) {
    //     event.preventDefault();
    //     const form = event.target;
    //     const formData = new FormData(form);  
    //     formData.append('location_id', {{ $location->id }});
        
    //     const url = editingHouse 
    //         ? `/admin/houses/${editingHouse}`
    //         : '{{ route("admin.houses.create") }}';
        
    //     if (editingHouse) {
    //         formData.append('_method', 'PUT');
    //     }
        
    //     fetch(url, {
    //         method: 'POST',
    //         body: formData,
    //         headers: {
    //             'X-CSRF-TOKEN': window.Laravel.csrfToken
    //         }
    //     })
    //     .then(response => response.json())
    //     .then(data => {
    //         if (data.success) {
    //             closeModal('house-modal');
    //             showAlert(data.message, 'success');
    //             setTimeout(() => {
    //                 window.location.reload();
    //             }, 1000);
    //         } else {
    //             showAlert(data.message || 'An error occurred', 'error');
    //         }
    //     })
    //     .catch(error => {
    //         console.error('Error:', error);
    //         showAlert('An error occurred', 'error');
    //     });
    // }
    function saveHouse(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);

    // Ensure checkboxes are included if unchecked
    if (!formData.has('parking_available')) formData.append('parking_available', 0);
    if (!formData.has('pet_friendly')) formData.append('pet_friendly', 0);

    formData.append('location_id', {{ $location->id }});

    const url = editingHouse 
        ? `/admin/houses/${editingHouse}`
        : '{{ route("admin.houses.create") }}';

    if (editingHouse) formData.append('_method', 'PUT');

    fetch(url, {
        method: 'POST',
        body: formData,
        headers: { 'X-CSRF-TOKEN': window.Laravel.csrfToken }
    })
    .then(res => res.json())
    .then(data => {
        if (!data.success) return showAlert(data.message || 'Error', 'error');

        closeModal('house-modal');
        showAlert(data.message, 'success');

        // Update house card on page dynamically
        if (editingHouse) {
            const card = document.querySelector(`.house-card[onclick="viewHouse(${editingHouse})"]`);
            if (card) {
                const detailsContainer = card.querySelector('.house-details');

                // Parking
                let parkingDetail = Array.from(detailsContainer.querySelectorAll('.detail-item'))
                    .find(d => d.querySelector('.icon')?.textContent.trim() === '🚗');
                if (formData.get('parking_available') == 1) {
                    if (!parkingDetail) {
                        const newDetail = document.createElement('div');
                        newDetail.className = 'detail-item';
                        newDetail.innerHTML = `<span class="icon">🚗</span><span>${formData.get('parking_description') || 'Parking Available'}</span>`;
                        detailsContainer.appendChild(newDetail);
                    } else {
                        parkingDetail.querySelector('span:last-child').textContent = formData.get('parking_description') || 'Parking Available';
                    }
                } else if (parkingDetail) {
                    parkingDetail.remove();
                }

                // Pet Friendly
                let petDetail = Array.from(detailsContainer.querySelectorAll('.detail-item'))
                    .find(d => d.querySelector('.icon')?.textContent.trim() === '🐾');
                if (formData.get('pet_friendly') == 1) {
                    if (!petDetail) {
                        const newDetail = document.createElement('div');
                        newDetail.className = 'detail-item';
                        newDetail.innerHTML = `<span class="icon">🐾</span><span>Pet Friendly</span>`;
                        detailsContainer.appendChild(newDetail);
                    }
                } else if (petDetail) {
                    petDetail.remove();
                }

                // Update other fields
                card.querySelector('.house-info h3').textContent = formData.get('name');
                const desc = card.querySelector('.house-description');
                if (desc) desc.textContent = formData.get('description').substring(0, 100);
            }
        }
    })
    .catch(err => console.error(err));
}

    function deleteHouse(houseId) {
        if (confirm('Are you sure you want to delete this house? This will also delete all suites in this house.')) {
            fetch(`/admin/houses/${houseId}`, {
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

    @endif
@endauth
</script>
@endpush
<!-- Include Owner Info Modal Component -->
@include('components.owner-info-modal')

@endsection