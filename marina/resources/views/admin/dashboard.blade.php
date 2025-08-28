@extends('layouts.app')

@section('title', 'Admin Dashboard - Marina Croatia')

@section('content')
<div id="main-page" class="page active">
    <!-- Main Title Section -->
    <section class="main-title">
        <button class="edit-btn" onclick="editMainContent()">✏️ Edit Content</button>
        <h1 id="main-heading">{{ $mainHeading }}</h1>
        <p id="main-description">{{ $mainDescription }}</p>
    </section>

    <!-- Quick Stats Section -->
    <section class="container">
        <div class="grid grid-4" style="margin-bottom: 2rem;">
            <div class="card stats-card">
                <h3>{{ $locations->count() }}</h3>
                <p>Locations</p>
            </div>
            <div class="card stats-card">
                <h3>{{ $locations->sum(fn($l) => $l->houses->count()) }}</h3>
                <p>Houses</p>
            </div>
            <div class="card stats-card">
                <h3>{{ $locations->sum(fn($l) => $l->houses->sum(fn($h) => $h->suites->count())) }}</h3>
                <p>Suites</p>
            </div>
            <div class="card stats-card">
                <h3>{{ \App\Models\User::where('role', 'owner')->count() }}</h3>
                <p>Owners</p>
            </div>
        </div>
    </section>

    <!-- Owner Management -->
    <section class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h2>Owner Management</h2>
            <button class="btn btn-primary" onclick="openModal('owner-modal')">+ Add Owner</button>
        </div>
        
        <div class="grid grid-3" id="owners-container">
            @foreach(\App\Models\User::where('role', 'owner')->with('houses')->get() as $owner)
            <div class="card owner-card">
                <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <h3>{{ $owner->full_name }}</h3>
                    <div>
                        <button class="btn btn-secondary" onclick="editOwner({{ $owner->id }})" title="Edit">✏️</button>
                        <button class="btn btn-warning" onclick="resetOwnerPassword({{ $owner->id }})" title="Reset Password">🔑</button>
                        <button class="btn btn-danger" onclick="toggleOwnerStatus({{ $owner->id }}, {{ $owner->is_active ? 'false' : 'true' }})" 
                                title="{{ $owner->is_active ? 'Deactivate' : 'Activate' }}">
                            {{ $owner->is_active ? '⏸️' : '▶️' }}
                        </button>
                        <button class="btn btn-danger" onclick="deleteOwner({{ $owner->id }})" title="Delete Owner">🗑️</button>
                    </div>
                </div>
                <div class="card-body">
                    <p><strong>Email:</strong> {{ $owner->email }}</p>
                    @if($owner->phone)
                        <p><strong>Phone:</strong> {{ $owner->phone }}</p>
                    @endif
                    <p><strong>Password:</strong> 
                        <span id="password-{{ $owner->id }}" style="display: none;">{{ $owner->temp_password ?: 'Not set' }}</span>
                        <button class="btn btn-sm btn-info" onclick="togglePassword({{ $owner->id }})" id="toggle-{{ $owner->id }}">Show</button>
                    </p>
                    <p><strong>Houses:</strong> {{ $owner->houses->count() }}</p>
                    <p><strong>Status:</strong> 
                        <span class="status-badge {{ $owner->is_active ? 'active' : 'inactive' }}">
                            {{ $owner->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </p>
                </div>
            </div>
            @endforeach
        </div>
    </section>

    <!-- Location Management -->
    <section class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h2>Location Management</h2>
            <button class="btn btn-primary" onclick="openModal('location-modal')">+ Add Location</button>
        </div>
        
        <div class="grid grid-3" id="locations-container">
            @foreach($locations as $location)
            <div class="card location-card">
                <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <h3>{{ $location->name }}</h3>
                    <div>
                        <button class="btn btn-secondary" onclick="editLocation({{ $location->id }}, '{{ $location->name }}', '{{ $location->description }}')" title="Edit">✏️</button>
                        <button class="btn btn-danger" onclick="deleteLocation({{ $location->id }})" title="Delete">×</button>
                    </div>
                </div>
                <div class="card-body">
                    @if($location->description)
                        <p>{{ $location->description }}</p>
                    @endif
                    <p><strong>Houses:</strong> {{ $location->houses->count() }}</p>
                    <div style="margin-top: 1rem;">
                        <button class="btn btn-success" onclick="showLocationHouses({{ $location->id }}, '{{ $location->name }}')">
                            Manage Houses ({{ $location->houses->count() }})
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </section>
</div>

<!-- Owner Modal -->
<div id="owner-modal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="owner-modal-title">Add Owner</h3>
            <button class="modal-close" onclick="closeModal('owner-modal')">×</button>
        </div>
        <form id="owner-form" onsubmit="saveOwner(event)">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
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
                <label for="owner-email">Email:</label>
                <input type="email" id="owner-email" name="email" required>
            </div>
            <div class="form-group">
                <label for="owner-phone">Phone:</label>
                <input type="tel" id="owner-phone" name="phone">
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="closeModal('owner-modal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Owner</button>
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

<!-- Houses Modal -->
<div id="houses-modal" class="modal">
    <div class="modal-content large">
        <div class="modal-header">
            <h3 id="houses-modal-title">Houses in Location</h3>
            <button class="modal-close" onclick="closeModal('houses-modal')">×</button>
        </div>
        <div class="modal-body">
            <div style="margin-bottom: 1rem;">
                <button class="btn btn-success" onclick="openModal('house-modal')">+ Add House</button>
            </div>
            <div id="houses-container" class="grid grid-2">
                <!-- Houses will be populated here -->
            </div>
        </div>
    </div>
</div>

<!-- House Modal -->
<div id="house-modal" class="modal">
    <div class="modal-content large">
        <div class="modal-header">
            <h3 id="house-modal-title">Add House</h3>
            <button class="modal-close" onclick="closeModal('house-modal')">×</button>
        </div>
        <form id="house-form" onsubmit="saveHouse(event)">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label for="house-name">House Name:</label>
                    <input type="text" id="house-name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="house-owner">Assign to Owner:</label>
                    <select id="house-owner" name="owner_id" required>
                        <option value="">Select Owner</option>
                        @foreach(\App\Models\User::where('role', 'owner')->get() as $owner)
                        <option value="{{ $owner->id }}">{{ $owner->full_name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1rem;">
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
                <label style="display: flex; align-items: center; gap: 8px;">
                    <input type="checkbox" id="house-parking" name="parking_available">
                    Parking Available
                </label>
            </div>
            <div class="form-group">
                <label for="house-parking-desc">Parking Description:</label>
                <input type="text" id="house-parking-desc" name="parking_description" placeholder="e.g., Private garage">
            </div>
            <div class="form-group">
                <label for="house-description">Description:</label>
                <textarea id="house-description" name="description" rows="3"></textarea>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label for="house-owner-phone">Owner Phone:</label>
                    <input type="tel" id="house-owner-phone" name="owner_phone">
                </div>
                <div class="form-group">
                    <label for="house-owner-email">Owner Email:</label>
                    <input type="email" id="house-owner-email" name="owner_email">
                </div>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label for="house-bank-account">Bank Account:</label>
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

<!-- Suites Modal -->
<div id="suites-modal" class="modal">
    <div class="modal-content large">
        <div class="modal-header">
            <h3 id="suites-modal-title">Suites in House</h3>
            <button class="modal-close" onclick="closeModal('suites-modal')">×</button>
        </div>
        <div class="modal-body">
            <div style="margin-bottom: 1rem;">
                <button class="btn btn-success" onclick="openModal('suite-modal')">+ Add Suite</button>
            </div>
            <div id="suites-container" class="grid grid-2">
                <!-- Suites will be populated here -->
            </div>
        </div>
    </div>
</div>

<!-- Suite Modal -->
<div id="suite-modal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="suite-modal-title">Add Suite</h3>
            <button class="modal-close" onclick="closeModal('suite-modal')">×</button>
        </div>
        <form id="suite-form" onsubmit="saveSuite(event)">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label for="suite-name">Suite Name/Number:</label>
                    <input type="text" id="suite-name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="suite-capacity">Capacity (people):</label>
                    <input type="number" id="suite-capacity" name="capacity_people" min="1" max="20" required>
                </div>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
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
                    <input type="number" id="suite-floor" name="floor_number">
                </div>
            </div>
            <div class="form-group">
                <label for="suite-description">Description:</label>
                <textarea id="suite-description" name="description" rows="3"></textarea>
            </div>
            
            <div class="form-group">
                <label>Amenities:</label>
                <div class="amenities-checkboxes" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.5rem; margin-top: 0.5rem;">
                    <label style="display: flex; align-items: center; gap: 0.5rem;">
                        <input type="checkbox" name="amenities[]" value="Pet Friendly">
                        Pet Friendly
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.5rem;">
                        <input type="checkbox" name="amenities[]" value="Dishwasher">
                        Dishwasher
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.5rem;">
                        <input type="checkbox" name="amenities[]" value="Washing Machine">
                        Washing Machine
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.5rem;">
                        <input type="checkbox" name="amenities[]" value="Balcony">
                        Balcony
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.5rem;">
                        <input type="checkbox" name="amenities[]" value="Sea View">
                        Sea View
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.5rem;">
                        <input type="checkbox" name="amenities[]" value="Air Conditioning">
                        Air Conditioning
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.5rem;">
                        <input type="checkbox" name="amenities[]" value="WiFi">
                        WiFi
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.5rem;">
                        <input type="checkbox" name="amenities[]" value="Parking">
                        Parking
                    </label>
                </div>
            </div>
            
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="closeModal('suite-modal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Suite</button>
            </div>
        </form>
    </div>
</div>

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

@push('scripts')
<script>
let currentLocation = null;
let editingLocation = null;
let editingHouse = null;
let editingOwner = null;
let currentHouse = null;
let editingSuite = null;

// Owner Management Functions
function editOwner(id) {
    const owners = @json(\App\Models\User::where('role', 'owner')->get());
    const owner = owners.find(o => o.id === id);
    
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
    if (confirm('Are you sure you want to reset this owner\'s password?\nA new temporary password will be generated.')) {
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
    const actionPast = activate === 'true' ? 'activated' : 'deactivated';
    const status = activate === 'true' ? 'ACTIVE' : 'INACTIVE';
    
    if (confirm(`Are you sure you want to ${action} this owner?\nThe owner status will be set to: ${status}`)) {
        const formData = new FormData();
        // Convert string to boolean value
        formData.append('is_active', activate === 'true' ? '1' : '0');
        formData.append('_method', 'PUT');
        
        fetch(`{{ url('admin/owners') }}/${id}`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': window.Laravel.csrfToken
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showAlert(`Owner has been ${actionPast} successfully!`, 'success');
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                console.error('Toggle status error:', data);
                showAlert(data.message || 'An error occurred', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('An error occurred while updating owner status', 'error');
        });
    }
}

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
    const method = editingLocation ? 'PUT' : 'POST';
    
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

function showLocationHouses(locationId, locationName) {
    currentLocation = locationId;
    document.getElementById('houses-modal-title').textContent = `Houses in ${locationName}`;
    
    // Load houses for this location
    const location = @json($locations).find(l => l.id === locationId);
    const housesContainer = document.getElementById('houses-container');
    housesContainer.innerHTML = '';
    
    if (location.houses && location.houses.length > 0) {
        location.houses.forEach(house => {
            const houseCard = createHouseCard(house);
            housesContainer.appendChild(houseCard);
        });
    } else {
        housesContainer.innerHTML = '<p>No houses in this location yet.</p>';
    }
    
    openModal('houses-modal');
}

function createHouseCard(house) {
    const card = document.createElement('div');
    card.className = 'card';
    card.innerHTML = `
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h4>${house.name}</h4>
            <div>
                <button class="btn btn-secondary" onclick="editHouse(${house.id})" title="Edit">✏️</button>
                <button class="btn btn-danger" onclick="deleteHouse(${house.id})" title="Delete">×</button>
            </div>
        </div>
        <div class="card-body">
            <p><strong>Owner:</strong> ${house.owner ? house.owner.first_name + ' ' + house.owner.last_name : 'Not assigned'}</p>
            <p><strong>Address:</strong> ${house.street_address}${house.house_number ? ' ' + house.house_number : ''}</p>
            ${house.distance_to_sea ? `<p><strong>Distance:</strong> ${house.distance_to_sea}</p>` : ''}
            <p><strong>Suites:</strong> ${house.suites ? house.suites.length : 0}</p>
            <div style="margin-top: 1rem;">
                <button class="btn btn-primary" onclick="manageSuites(${house.id}, '${house.name}')">
                    Manage Suites (${house.suites ? house.suites.length : 0})
                </button>
            </div>
        </div>
    `;
    return card;
}

function saveHouse(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    formData.append('location_id', currentLocation);
    
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
            // Refresh houses list
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
    if (confirm('Are you sure you want to delete this house? This will also delete all suites in this house.')) {
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

function manageSuites(houseId, houseName) {
    currentHouse = houseId;
    document.getElementById('suites-modal-title').textContent = `Suites in ${houseName}`;
    
    // Load suites for this house
    fetch(`{{ url('admin/houses') }}/${houseId}/suites`)
        .then(response => response.json())
        .then(data => {
            const suitesContainer = document.getElementById('suites-container');
            suitesContainer.innerHTML = '';
            
            if (data.success && data.suites.length > 0) {
                data.suites.forEach(suite => {
                    const suiteCard = createSuiteCard(suite);
                    suitesContainer.appendChild(suiteCard);
                });
            } else {
                suitesContainer.innerHTML = '<p>No suites in this house yet.</p>';
            }
            
            openModal('suites-modal');
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Error loading suites', 'error');
        });
}

function createSuiteCard(suite) {
    const card = document.createElement('div');
    card.className = 'card';
    card.innerHTML = `
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h4>${suite.name}</h4>
            <div>
                <button class="btn btn-secondary" onclick="editSuite(${suite.id})" title="Edit">✏️</button>
                <button class="btn btn-info" onclick="manageAmenities(${suite.id}, '${suite.name}')" title="Amenities">🏠</button>
                <button class="btn btn-primary" onclick="viewSuiteCalendar(${suite.id}, '${suite.name}')" title="Calendar">📅</button>
                <button class="btn btn-danger" onclick="deleteSuite(${suite.id})" title="Delete">×</button>
            </div>
        </div>
        <div class="card-body">
            <p><strong>Capacity:</strong> ${suite.capacity_people} people</p>
            <p><strong>Bedrooms:</strong> ${suite.bedrooms}</p>
            <p><strong>Bathrooms:</strong> ${suite.bathrooms}</p>
            ${suite.floor_number ? `<p><strong>Floor:</strong> ${suite.floor_number}</p>` : ''}
            <p><strong>Status:</strong> 
                <span class="status-badge ${suite.is_active ? 'active' : 'inactive'}">
                    ${suite.is_active ? 'Active' : 'Inactive'}
                </span>
            </p>
        </div>
    `;
    return card;
}

function saveSuite(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    formData.append('house_id', currentHouse);
    
    const url = editingSuite 
        ? `{{ url('admin/suites') }}/${editingSuite}`
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
            // Refresh suites list
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

function editSuite(id) {
    // Load suite data and open edit modal
    fetch(`{{ url('admin/suites') }}/${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const suite = data.suite;
                editingSuite = id;
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

function deleteSuite(id) {
    if (confirm('Are you sure you want to delete this suite? This will also delete all bookings for this suite.')) {
        fetch(`{{ url('admin/suites') }}/${id}`, {
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

function manageAmenities(suiteId, suiteName) {
    showAlert('Amenities management coming soon!', 'warning');
}

function viewSuiteCalendar(suiteId, suiteName) {
    showAlert('Calendar view coming soon!', 'warning');
}

// Reset form when opening location modal
document.getElementById('location-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        editingLocation = null;
        document.getElementById('location-modal-title').textContent = 'Add Location';
        document.getElementById('location-form').reset();
    }
});

// Reset form when opening house modal  
document.getElementById('house-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        editingHouse = null;
        document.getElementById('house-modal-title').textContent = 'Add House';
        document.getElementById('house-form').reset();
    }
});

function deleteOwner(id) {
    if (confirm('Are you sure you want to delete this owner?\n\n⚠️ WARNING: This action cannot be undone!\nThe owner and all associated data will be permanently removed.')) {
        fetch(`{{ url('admin/owners') }}/${id}`, {
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
            showAlert('An error occurred while deleting owner', 'error');
        });
    }
}

function togglePassword(ownerId) {
    const passwordSpan = document.getElementById('password-' + ownerId);
    const toggleBtn = document.getElementById('toggle-' + ownerId);
    
    if (passwordSpan.style.display === 'none') {
        passwordSpan.style.display = 'inline';
        toggleBtn.textContent = 'Hide';
    } else {
        passwordSpan.style.display = 'none';
        toggleBtn.textContent = 'Show';
    }
}

// Reset form when opening owner modal
document.getElementById('owner-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        editingOwner = null;
        document.getElementById('owner-modal-title').textContent = 'Add Owner';
        document.getElementById('owner-form').reset();
    }
});
</script>
@endpush
@endsection