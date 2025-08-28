@extends('layouts.app')

@section('title', 'Houses Management - Marina Croatia')

@section('content')
<div class="container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h2>Houses Management</h2>
        <button class="btn btn-primary" onclick="openModal('house-modal')">+ Add House</button>
    </div>

    <!-- Filters -->
    <div class="card" style="margin-bottom: 2rem;">
        <div class="card-body">
            <form method="GET" style="display: grid; grid-template-columns: 1fr 1fr 1fr auto; gap: 1rem; align-items: end;">
                <div class="form-group" style="margin: 0;">
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
                <div class="form-group" style="margin: 0;">
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
                <div class="form-group" style="margin: 0;">
                    <label>&nbsp;</label>
                    <div style="display: flex; gap: 0.5rem;">
                        <button type="submit" class="btn btn-secondary">Filter</button>
                        <a href="{{ route('admin.houses') }}" class="btn btn-secondary">Clear</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Houses Grid -->
    <div class="grid grid-2">
        @forelse($houses as $house)
        <div class="card">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                <h3>{{ $house->name }}</h3>
                <div>
                    <button class="btn btn-secondary" onclick="editHouse({{ $house->id }})" title="Edit">✏️</button>
                    <button class="btn btn-success" onclick="manageSuites({{ $house->id }}, '{{ $house->name }}')" title="Manage Suites">🏠</button>
                    <button class="btn btn-danger" onclick="deleteHouse({{ $house->id }})" title="Delete">×</button>
                </div>
            </div>
            <div class="card-body">
                <p><strong>Location:</strong> {{ $house->location->name }}</p>
                <p><strong>Owner:</strong> {{ $house->owner ? $house->owner->full_name : 'Not assigned' }}</p>
                <p><strong>Address:</strong> {{ $house->street_address }}{{ $house->house_number ? ' ' . $house->house_number : '' }}</p>
                @if($house->distance_to_sea)
                <p><strong>Distance to Sea:</strong> {{ $house->distance_to_sea }}</p>
                @endif
                @if($house->parking_available)
                <p><strong>Parking:</strong> Yes{{ $house->parking_description ? ' - ' . $house->parking_description : '' }}</p>
                @endif
                <p><strong>Suites:</strong> {{ $house->suites->count() }}</p>
                <p><strong>Status:</strong> 
                    <span class="status-badge {{ $house->is_active ? 'active' : 'inactive' }}">
                        {{ $house->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </p>
                @if($house->description)
                <p style="margin-top: 1rem;"><strong>Description:</strong><br>{{ Str::limit($house->description, 150) }}</p>
                @endif
                
                <!-- Banking Information -->
                @if($house->bank_account_number || $house->bank_name)
                <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #e5e7eb;">
                    <h4 style="font-size: 14px; margin-bottom: 0.5rem;">Banking Information</h4>
                    @if($house->bank_account_number)
                    <p style="font-size: 13px;"><strong>Account:</strong> {{ $house->bank_account_number }}</p>
                    @endif
                    @if($house->bank_name)
                    <p style="font-size: 13px;"><strong>Bank:</strong> {{ $house->bank_name }}</p>
                    @endif
                </div>
                @endif
            </div>
        </div>
        @empty
        <div class="card" style="grid-column: 1 / -1;">
            <div class="card-body" style="text-align: center; padding: 3rem;">
                <h3 style="color: #6b7280; margin-bottom: 1rem;">No Houses Found</h3>
                <p style="color: #9ca3af;">No houses match your current filters. Try adjusting your search criteria.</p>
            </div>
        </div>
        @endforelse
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
            <h3 id="suites-modal-title">Suites Management</h3>
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
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="closeModal('suite-modal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Suite</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
let editingHouse = null;
let currentHouse = null;
let editingSuite = null;

function editHouse(id) {
    const houses = @json($houses);
    const house = houses.find(h => h.id === id);
    
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
            // Refresh the suites modal
            manageSuites(currentHouse, 'House');
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
    if (confirm('Are you sure you want to delete this suite? This will also delete all bookings.')) {
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
                // Refresh the suites modal
                manageSuites(currentHouse, 'House');
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

// Reset forms when opening modals
document.getElementById('house-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        editingHouse = null;
        document.getElementById('house-modal-title').textContent = 'Add House';
        document.getElementById('house-form').reset();
    }
});

document.getElementById('suite-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        editingSuite = null;
        document.getElementById('suite-modal-title').textContent = 'Add Suite';
        document.getElementById('suite-form').reset();
    }
});
</script>
@endpush
@endsection