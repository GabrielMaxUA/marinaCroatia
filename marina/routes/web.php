<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\OwnerController;

Route::get('/', function () {
    // Get locations with houses for public homepage
    $locations = \App\Models\Location::with(['houses.suites'])->get();
    $mainHeading = \App\Models\SiteContent::where('content_key', 'main_heading')->value('content_value') ?? 'Luxury Croatian Accommodations';
    $mainDescription = \App\Models\SiteContent::where('content_key', 'main_description')->value('content_value') ?? 'We are a premium travel agency specializing in exclusive accommodations along the Croatian coast.';
    
    return view('welcome', compact('locations', 'mainHeading', 'mainDescription'));
})->name('home');

// Public routes for viewing locations, houses, and suites
Route::get('/locations/{location}', function($locationId) {
    $location = \App\Models\Location::with(['houses.suites', 'houses.owner'])->findOrFail($locationId);
    $houses = $location->houses;
    
    return view('public.houses', compact('location', 'houses'));
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
    Route::get('/locations', [AdminController::class, 'locations'])->name('locations');
    Route::get('/houses', [AdminController::class, 'houses'])->name('houses');
    Route::get('/owners', [AdminController::class, 'owners'])->name('owners');
    Route::get('/owners/{owner}/info', [AdminController::class, 'getOwnerInfo'])->name('owners.info');
    Route::post('/owners', [AdminController::class, 'createOwner'])->name('owners.create');
    Route::put('/owners/{owner}', [AdminController::class, 'updateOwner'])->name('owners.update');
    Route::delete('/owners/{owner}', [AdminController::class, 'deleteOwner'])->name('owners.delete');
    Route::post('/owners/{owner}/reset-password', [AdminController::class, 'resetOwnerPassword'])->name('owners.reset-password');
    
    Route::post('/locations', [AdminController::class, 'createLocation'])->name('locations.create');
    Route::get('/locations/{location}', [AdminController::class, 'getLocation'])->name('locations.show');
    Route::put('/locations/{location}', [AdminController::class, 'updateLocation'])->name('locations.update');
    Route::delete('/locations/{location}', [AdminController::class, 'deleteLocation'])->name('locations.delete');
    
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
