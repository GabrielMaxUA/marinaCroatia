<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\OwnerController;

Route::get('/', function () {
    // Get locations with houses for homepage (with admin filtering support)
    $query = \App\Models\Location::with(['houses.suites', 'houses.owner', 'media', 'primaryImage']);
    
    // Add location filter for admin users
    if (auth()->check() && auth()->user()->isAdmin() && request('location_id')) {
        $query->where('id', request('location_id'));
    }
    
    // Use pagination for admin, get all for public
    if (auth()->check() && auth()->user()->isAdmin()) {
        $locations = $query->orderBy('name')->paginate(12);
        // Get all locations for the dropdown filter
        $allLocations = \App\Models\Location::with(['houses', 'media', 'primaryImage'])->orderBy('name')->get();
    } else {
        $locations = $query->orderBy('name')->get();
        $allLocations = $locations; // For public, use same data
    }
    
    $mainHeading = \App\Models\SiteContent::where('content_key', 'main_heading')->value('content_value') ?? 'Luxury Croatian Accommodations';
    $mainDescription = \App\Models\SiteContent::where('content_key', 'main_description')->value('content_value') ?? 'We are a premium travel agency specializing in exclusive accommodations along the Croatian coast.';
    $backgroundImage = \App\Models\SiteContent::where('content_key', 'background_image')->value('content_value');
    $overlayOpacity = \App\Models\SiteContent::where('content_key', 'overlay_opacity')->value('content_value') ?? '50';
    
    return view('welcome', compact('locations', 'allLocations', 'mainHeading', 'mainDescription', 'backgroundImage', 'overlayOpacity'));
})->name('home');

// Public routes for viewing locations, houses, and suites
Route::get('/locations/{location}', function($locationId, \Illuminate\Http\Request $request) {
    // Handle special "all" case for admin - show all houses from all locations
    if ($locationId === 'all' && auth()->check() && auth()->user()->isAdmin()) {
        // Create a virtual location object for "all locations"
        $location = (object) ['id' => 'all', 'name' => 'All Locations', 'description' => 'Houses from all locations'];
        
        // Query all houses from all locations
        $query = \App\Models\House::with(['location', 'owner', 'suites', 'images']);
        
        // Apply filters if present
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'LIKE', '%' . $request->search . '%')
                  ->orWhere('street_address', 'LIKE', '%' . $request->search . '%');
            });
        }
        
        if ($request->location_id) {
            $query->where('location_id', $request->location_id);
        }
        
        if ($request->owner_id) {
            $query->where('owner_id', $request->owner_id);
        }
        
        $houses = $query->orderBy('location_id')->orderBy('name')->get();
    } else {
        // Normal case - specific location
        $location = \App\Models\Location::with(['houses.suites', 'houses.owner'])->findOrFail($locationId);
        
        // Apply filters for admin users
        $query = $location->houses()->with(['owner', 'suites', 'images']);
        
        if (auth()->check() && auth()->user()->isAdmin()) {
            // Apply search filter
            if ($request->search) {
                $query->where(function($q) use ($request) {
                    $q->where('name', 'LIKE', '%' . $request->search . '%')
                      ->orWhere('street_address', 'LIKE', '%' . $request->search . '%');
                });
            }
            
            // Apply owner filter
            if ($request->owner_id) {
                $query->where('owner_id', $request->owner_id);
            }
        }
        
        $houses = $query->get();
    }
    
    // Get all locations and owners for admin filters
    $allLocations = auth()->check() && auth()->user()->isAdmin() ? 
        \App\Models\Location::orderBy('name')->get() : collect();
    $owners = auth()->check() && auth()->user()->isAdmin() ? 
        \App\Models\User::where('role', 'owner')->orderBy('first_name')->get() : collect();
    
    return view('public.houses', compact('location', 'houses', 'allLocations', 'owners'));
})->name('public.houses');

Route::get('/houses/{house}', function($houseId) {
    $house = \App\Models\House::with(['location', 'owner', 'suites.amenities', 'suites.images'])->findOrFail($houseId);
    $suites = $house->suites;
    
    return view('public.suites', compact('house', 'suites'));
})->name('public.suites');

Route::get('/houses/{house}/gallery', function($houseId) {
    $house = \App\Models\House::with(['images'])->findOrFail($houseId);
    
    // For now, return placeholder data since we don't have house images in the database yet
    $media = [];
    
    // If house has images, format them for the gallery
    if ($house->images && $house->images->count() > 0) {
        foreach ($house->images as $image) {
            $media[] = [
                'type' => 'image',
                'url' => $image->image_url,
                'thumbnail' => $image->thumbnail_url ?? $image->image_url
            ];
        }
    }
    
    return response()->json([
        'success' => true,
        'house_name' => $house->name,
        'media' => $media
    ]);
});

Route::get('/suites/{suite}/gallery', function($suiteId) {
    $suite = \App\Models\Suite::with(['images'])->findOrFail($suiteId);
    
    // For now, return placeholder data since we don't have suite images in the database yet
    $media = [];
    
    // If suite has images, format them for the gallery
    if ($suite->images && $suite->images->count() > 0) {
        foreach ($suite->images as $image) {
            $media[] = [
                'type' => 'image',
                'url' => $image->image_url,
                'thumbnail' => $image->thumbnail_url ?? $image->image_url
            ];
        }
    }
    
    return response()->json([
        'success' => true,
        'suite_name' => $suite->name,
        'media' => $media
    ]);
});

Route::get('/locations/{location}/gallery', function($locationId) {
    $location = \App\Models\Location::with(['media'])->findOrFail($locationId);
    
    $media = [];
    
    // If location has media, format them for the gallery
    if ($location->media && $location->media->count() > 0) {
        foreach ($location->media as $mediaItem) {
            $media[] = [
                'type' => $mediaItem->media_type,
                'url' => $mediaItem->full_url,
                'thumbnail' => $mediaItem->full_url
            ];
        }
    }
    
    return response()->json([
        'success' => true,
        'location_name' => $location->name,
        'media' => $media
    ]);
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('/change-password', [AuthController::class, 'changePassword'])->name('change-password');
});

Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    Route::get('/dashboard', function() { return redirect()->route('home'); })->name('dashboard');
    Route::get('/locations', function() { return redirect()->route('home'); })->name('locations');
    
    Route::get('/houses', function() {
        // Redirect to public houses with special "all" parameter for admin
        return redirect()->route('public.houses', ['location' => 'all']);
    })->name('houses');
    Route::get('/owners', [AdminController::class, 'owners'])->name('owners');
    Route::get('/owners/{owner}/info', [AdminController::class, 'getOwnerInfo'])->name('owners.info');
    Route::post('/owners', [AdminController::class, 'createOwner'])->name('owners.create');
    Route::put('/owners/{owner}', [AdminController::class, 'updateOwner'])->name('owners.update');
    Route::delete('/owners/{owner}', [AdminController::class, 'deleteOwner'])->name('owners.delete');
    Route::post('/owners/{owner}/reset-password', [AdminController::class, 'resetOwnerPassword'])->name('owners.reset-password');
    
    Route::post('/locations', [AdminController::class, 'createLocation'])->name('locations.create');
    Route::get('/locations/{location}', [AdminController::class, 'getLocation'])->name('locations.show');
    Route::post('/locations/{location}/update', [AdminController::class, 'updateLocation'])->name('locations.update');
    Route::delete('/locations/{location}', [AdminController::class, 'deleteLocation'])->name('locations.delete');
    Route::delete('/locations/{location}/media/{media}', [AdminController::class, 'deleteLocationMedia'])->name('locations.media.delete');
    Route::put('/locations/{location}/media/{media}/primary', [AdminController::class, 'setLocationMediaPrimary'])->name('locations.media.primary');
    
    Route::post('/houses', [AdminController::class, 'createHouse'])->name('houses.create');
    Route::get('/houses/{house}', [AdminController::class, 'getHouse'])->name('houses.show');
    Route::put('/houses/{house}', [AdminController::class, 'updateHouse'])->name('houses.update');
    Route::delete('/houses/{house}', [AdminController::class, 'deleteHouse'])->name('houses.delete');
    
    Route::post('/suites', [AdminController::class, 'createSuite'])->name('suites.create');
    Route::get('/suites/{suite}', [AdminController::class, 'getSuite'])->name('suites.show');
    Route::put('/suites/{suite}', [AdminController::class, 'updateSuite'])->name('suites.update');
    Route::delete('/suites/{suite}', [AdminController::class, 'deleteSuite'])->name('suites.delete');
    Route::get('/houses/{house}/suites', [AdminController::class, 'getHouseSuites'])->name('houses.suites');
    
    Route::post('/site-content', [AdminController::class, 'updateSiteContent'])->name('site-content.update');
    
    Route::get('/bookings', [AdminController::class, 'bookings'])->name('bookings');
    Route::post('/bookings', [AdminController::class, 'createBooking'])->name('bookings.create');
    Route::get('/calendar', [AdminController::class, 'calendar'])->name('calendar');
    Route::get('/calendar/day/{date}', [AdminController::class, 'calendarDay'])->name('calendar.day');
    Route::put('/bookings/{booking}', [AdminController::class, 'updateBooking'])->name('bookings.update');
    Route::delete('/bookings/{booking}', [AdminController::class, 'cancelBooking'])->name('bookings.cancel');
});

Route::prefix('owner')->name('owner.')->middleware(['auth'])->group(function () {
    Route::get('/dashboard', function() { return redirect()->route('home'); })->name('dashboard');
    Route::get('/houses/{house}', [OwnerController::class, 'house'])->name('house');
    Route::get('/suites/{suite}', [OwnerController::class, 'suite'])->name('suite');
    Route::get('/suites/{suite}/calendar', [OwnerController::class, 'calendar'])->name('suite.calendar');
    
    Route::get('/bookings', [OwnerController::class, 'bookings'])->name('bookings');
    Route::post('/bookings', [OwnerController::class, 'createBooking'])->name('bookings.create');
    Route::put('/bookings/{booking}', [OwnerController::class, 'updateBooking'])->name('bookings.update');
    Route::delete('/bookings/{booking}', [OwnerController::class, 'cancelBooking'])->name('bookings.cancel');
    Route::get('/bookings/{booking}', [OwnerController::class, 'bookingDetails'])->name('bookings.details');
    
    Route::get('/profile', [OwnerController::class, 'profile'])->name('profile');
    Route::put('/profile', [OwnerController::class, 'updateProfile'])->name('profile.update');
});
