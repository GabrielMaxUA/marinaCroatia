<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Location;
use App\Models\House;
use App\Models\Suite;
use App\Models\Booking;
use App\Models\BookingDate;
use App\Models\SiteContent;
use App\Models\BankInfo;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;
use App\Models\LocationMedia;

class AdminController extends Controller
{
    /**
     * Optimize and convert image to WebP format using native PHP GD
     */
    private function optimizeImage($uploadedFile, $maxWidth = 1920, $maxHeight = 1080, $quality = 80)
    {
        try {
            // Check if GD extension and WebP support are available
            if (!extension_loaded('gd') || !function_exists('imagewebp')) {
                throw new \Exception('GD extension or WebP support is not available');
            }

            // Get image info
            $imageInfo = getimagesize($uploadedFile->getPathname());
            if (!$imageInfo) {
                throw new \Exception('Invalid image file');
            }

            list($originalWidth, $originalHeight, $imageType) = $imageInfo;

            // Create image resource from uploaded file
            switch ($imageType) {
                case IMAGETYPE_JPEG:
                    $sourceImage = imagecreatefromjpeg($uploadedFile->getPathname());
                    break;
                case IMAGETYPE_PNG:
                    $sourceImage = imagecreatefrompng($uploadedFile->getPathname());
                    break;
                case IMAGETYPE_GIF:
                    $sourceImage = imagecreatefromgif($uploadedFile->getPathname());
                    break;
                case IMAGETYPE_WEBP:
                    $sourceImage = imagecreatefromwebp($uploadedFile->getPathname());
                    break;
                default:
                    throw new \Exception('Unsupported image type');
            }

            if (!$sourceImage) {
                throw new \Exception('Failed to create image resource');
            }

            // Calculate new dimensions while maintaining aspect ratio
            $aspectRatio = $originalWidth / $originalHeight;
            
            if ($originalWidth > $maxWidth || $originalHeight > $maxHeight) {
                if ($maxWidth / $maxHeight > $aspectRatio) {
                    $newHeight = $maxHeight;
                    $newWidth = intval($maxHeight * $aspectRatio);
                } else {
                    $newWidth = $maxWidth;
                    $newHeight = intval($maxWidth / $aspectRatio);
                }
            } else {
                $newWidth = $originalWidth;
                $newHeight = $originalHeight;
            }

            // Create new image
            $newImage = imagecreatetruecolor($newWidth, $newHeight);
            
            // Preserve transparency for PNG
            if ($imageType === IMAGETYPE_PNG) {
                imagealphablending($newImage, false);
                imagesavealpha($newImage, true);
                $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
                imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $transparent);
            }

            // Resize image
            imagecopyresampled($newImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);

            // Generate unique filename with webp extension
            $filename = 'background_' . time() . '_' . uniqid() . '.webp';
            $directory = 'backgrounds';
            
            // Ensure the storage link exists for public access
            if (!file_exists(public_path('storage'))) {
                Artisan::call('storage:link');
            }
            
            $fullPath = storage_path('app/public/' . $directory);
            
            // Create directory if it doesn't exist
            if (!file_exists($fullPath)) {
                mkdir($fullPath, 0755, true);
            }
            
            $filePath = $fullPath . '/' . $filename;

            // Save as WebP
            $success = imagewebp($newImage, $filePath, $quality);

            // Clean up memory
            imagedestroy($sourceImage);
            imagedestroy($newImage);

            if (!$success) {
                throw new \Exception('Failed to save WebP image');
            }

            return $directory . '/' . $filename;
            
        } catch (\Exception $e) {
            // Fallback: use original file upload method
            Log::warning('Image optimization failed: ' . $e->getMessage());
            return $uploadedFile->store('backgrounds', 'public');
        }
    }

    /**
     * Fast location media optimization - Performance optimized
     */
    private function optimizeLocationMedia($uploadedFile, $mediaType = 'image', $maxWidth = 800, $maxHeight = 600, $quality = 70)
    {
        try {
            if ($mediaType === 'video') {
                // For videos, just store directly - no processing needed
                return $uploadedFile->store('locations/videos', 'public');
            }

            $fileSize = $uploadedFile->getSize();
            
            // Skip processing for small images (under 1MB) - just store them
            if ($fileSize < 1024 * 1024) {
                return $uploadedFile->store('locations/images', 'public');
            }

            // Quick dimension check
            $imageInfo = @getimagesize($uploadedFile->getPathname());
            if (!$imageInfo) {
                // If we can't get image info, just store it
                return $uploadedFile->store('locations/images', 'public');
            }

            list($originalWidth, $originalHeight, $imageType) = $imageInfo;

            // Skip processing if image is already small enough
            if ($originalWidth <= $maxWidth && $originalHeight <= $maxHeight) {
                return $uploadedFile->store('locations/images', 'public');
            }

            // Only process if we have GD and WebP support
            if (!extension_loaded('gd') || !function_exists('imagewebp')) {
                return $uploadedFile->store('locations/images', 'public');
            }

            // Quick image creation with error handling
            $sourceImage = null;
            switch ($imageType) {
                case IMAGETYPE_JPEG:
                    $sourceImage = @imagecreatefromjpeg($uploadedFile->getPathname());
                    break;
                case IMAGETYPE_PNG:
                    $sourceImage = @imagecreatefrompng($uploadedFile->getPathname());
                    break;
                case IMAGETYPE_WEBP:
                    $sourceImage = @imagecreatefromwebp($uploadedFile->getPathname());
                    break;
                default:
                    // Unsupported types, store as-is
                    return $uploadedFile->store('locations/images', 'public');
            }

            if (!$sourceImage) {
                // If image creation fails, store original
                return $uploadedFile->store('locations/images', 'public');
            }

            // Fast dimension calculation
            $aspectRatio = $originalWidth / $originalHeight;
            
            if ($aspectRatio > 1) {
                // Landscape
                $newWidth = $maxWidth;
                $newHeight = intval($maxWidth / $aspectRatio);
            } else {
                // Portrait or square
                $newHeight = $maxHeight;
                $newWidth = intval($maxHeight * $aspectRatio);
            }

            // Create new image
            $newImage = imagecreatetruecolor($newWidth, $newHeight);
            
            // Simple resampling - no transparency handling for speed
            imagecopyresampled($newImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);

            // Generate path
            $filename = 'loc_' . time() . '_' . uniqid() . '.webp';
            $directory = 'locations/images';
            $fullPath = storage_path('app/public/' . $directory);
            
            if (!file_exists($fullPath)) {
                mkdir($fullPath, 0755, true);
            }
            
            // Save with lower quality for speed
            $success = @imagewebp($newImage, $fullPath . '/' . $filename, $quality);

            // Immediate cleanup
            imagedestroy($sourceImage);
            imagedestroy($newImage);

            if ($success) {
                return $directory . '/' . $filename;
            }
            
        } catch (\Exception $e) {
            Log::warning('Fast image optimization failed: ' . $e->getMessage());
        }

        // Fallback - store original file
        return $uploadedFile->store('locations/images', 'public');
    }

    /**
     * Handle location media uploads - Optimized for performance
     */
    private function handleLocationMediaUploads($location, $request)
    {
        $mediaFiles = $request->file('media_files');
        $mediaTitles = $request->input('media_titles', []);
        $primaryMediaIndex = $request->input('primary_media');

        // Process files in batches for better performance
        $batchSize = 3; // Process max 3 files with optimization
        $processed = 0;

        foreach ($mediaFiles as $index => $file) {
            $mediaType = str_starts_with($file->getMimeType(), 'video/') ? 'video' : 'image';
            
            // For performance, skip heavy processing after processing some files
            if ($processed >= $batchSize && $mediaType === 'image' && $file->getSize() > 5 * 1024 * 1024) {
                // For large images after batch limit, just store directly
                $mediaPath = $file->store('locations/images', 'public');
            } else {
                $mediaPath = $this->optimizeLocationMedia($file, $mediaType);
                if ($mediaType === 'image') $processed++;
            }
            
            $isPrimary = ($index == $primaryMediaIndex);
            
            // If this is set as primary, make sure no other media is primary for this location
            if ($isPrimary) {
                LocationMedia::where('location_id', $location->id)
                    ->where('media_type', $mediaType)
                    ->update(['is_primary' => false]);
            }

            LocationMedia::create([
                'location_id' => $location->id,
                'media_type' => $mediaType,
                'media_url' => $mediaPath,
                'media_title' => $mediaTitles[$index] ?? null,
                'display_order' => $index,
                'is_primary' => $isPrimary
            ]);
        }
    }

    public function locations(Request $request)
    {
        $query = Location::with(['houses.suites', 'houses.owner', 'primaryImage']);
        
        // Apply search filter
        if ($request->search) {
            $query->where('name', 'LIKE', '%' . $request->search . '%')
                  ->orWhere('description', 'LIKE', '%' . $request->search . '%');
        }
        
        $locations = $query->orderBy('name')->paginate(12);
        $owners = User::where('role', 'owner')->where('is_active', true)->orderBy('first_name')->get();
        
        // Check if we need to edit a location
        $editLocation = null;
        if ($request->edit) {
            $editLocation = Location::find($request->edit);
        }
        
        return view('admin.locations', compact('locations', 'owners', 'editLocation'));
    }


    public function owners(Request $request)
    {
        $query = User::where('role', 'owner')
                    ->with(['houses.location', 'houses.suites']);
        
        // Apply filters
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('first_name', 'LIKE', '%' . $request->search . '%')
                  ->orWhere('last_name', 'LIKE', '%' . $request->search . '%')
                  ->orWhere('email', 'LIKE', '%' . $request->search . '%');
            });
        }
        
        if ($request->location_id) {
            $query->whereHas('houses', function($q) use ($request) {
                $q->where('location_id', $request->location_id);
            });
        }
        
        if ($request->status) {
            $isActive = $request->status === 'active';
            $query->where('is_active', $isActive);
        }
        
        $owners = $query->paginate(12);
        $locations = Location::all();
        
        return view('admin.owners', compact('owners', 'locations'));
    }

    public function getOwnerInfo($ownerId)
    {
        try {
            $owner = User::where('role', 'owner')
                        ->withCount(['houses', 'houses as suites_count' => function($query) {
                            $query->join('suites', 'houses.id', '=', 'suites.house_id');
                        }])
                        ->findOrFail($ownerId);

            $bankInfo = BankInfo::where('owner_id', $ownerId)
                               ->where('is_active', true)
                               ->first();

            return response()->json([
                'success' => true,
                'owner' => [
                    'full_name' => $owner->full_name,
                    'email' => $owner->email,
                    'phone' => $owner->phone,
                    'houses_count' => $owner->houses_count,
                    'suites_count' => $owner->suites_count ?: 0,
                ],
                'bankInfo' => $bankInfo ? [
                    'bank_name' => $bankInfo->bank_name,
                    'account_number' => $bankInfo->account_number,
                    'iban' => $bankInfo->iban,
                    'swift' => $bankInfo->swift,
                    'bank_address' => $bankInfo->bank_address,
                ] : null
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Owner not found or error loading data'
            ], 404);
        }
    }

    public function createOwner(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
        ]);

        $tempPassword = 'temp' . rand(1000, 9999);

        $owner = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password_hash' => Hash::make($tempPassword),
            'temp_password' => $tempPassword, // Store for admin viewing
            'role' => 'owner',
            'is_active' => true
        ]);

        return response()->json([
            'success' => true,
            'owner' => $owner,
            'temporary_password' => $tempPassword,
            'message' => 'Owner created successfully'
        ]);
    }

    public function updateOwner(Request $request, User $owner)
    {
        $request->validate([
            'first_name' => 'string|max:100',
            'last_name' => 'string|max:100',
            'email' => 'email|unique:users,email,' . $owner->id,
            'phone' => 'nullable|string|max:20',
            'is_active' => 'sometimes'
        ]);

        // Handle the update data
        $updateData = $request->only([
            'first_name', 'last_name', 'email', 'phone'
        ]);

        // Handle is_active separately to ensure proper boolean conversion
        if ($request->has('is_active')) {
            $updateData['is_active'] = $request->boolean('is_active');
        }

        $owner->update($updateData);

        return response()->json([
            'success' => true,
            'owner' => $owner->fresh(),
            'message' => 'Owner updated successfully'
        ]);
    }

    public function resetOwnerPassword(User $owner)
    {
        $tempPassword = 'temp' . rand(1000, 9999);
        
        $owner->update([
            'password_hash' => Hash::make($tempPassword),
            'temp_password' => $tempPassword // Store for admin viewing
        ]);

        return response()->json([
            'success' => true,
            'temporary_password' => $tempPassword,
            'message' => 'Password reset successfully'
        ]);
    }

    public function deleteOwner(User $owner)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Access denied. Admin privileges required.');
        }

        // Check if owner has houses
        if ($owner->houses()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete owner. Please reassign or delete their houses first.'
            ]);
        }

        $owner->delete();

        return response()->json([
            'success' => true,
            'message' => 'Owner deleted successfully'
        ]);
    }

    public function getLocation(Location $location)
    {
        // Load media with all details for admin editing
        $location->load('media');
        
        return response()->json([
            'success' => true,
            'location' => $location
        ]);
    }

    public function createLocation(Request $request)
    {
        // Increase memory limit and execution time for file processing
        ini_set('memory_limit', '256M');
        ini_set('max_execution_time', '300'); // 5 minutes
        
        // Ensure user is authenticated and authorized
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $request->validate([
            'name' => 'required|string|max:100|unique:locations,name',
            'description' => 'nullable|string',
            'media_files' => 'nullable|array|max:10', // Limit to 10 files max
            'media_files.*' => 'file|mimes:jpeg,png,jpg,gif,webp,mp4,mov,avi,wmv|max:20480', // Reduced to 20MB for better performance
            'media_titles' => 'nullable|array',
            'media_titles.*' => 'nullable|string|max:200',
            'primary_media' => 'nullable|integer'
        ]);

        DB::beginTransaction();
        
        try {
            $location = Location::create([
                'name' => $request->name,
                'description' => $request->description,
                'created_by' => Auth::id()
            ]);

            // Handle media uploads
            if ($request->hasFile('media_files')) {
                $this->handleLocationMediaUploads($location, $request);
            }

            DB::commit();
            
            // Load the location with its media and houses for the response
            $location->load(['media', 'houses', 'primaryImage']);

            // Check if this is an AJAX request (from welcome page) or regular form submission
            if ($request->expectsJson() || $request->ajax() || $request->wantsJson() || $request->header('Accept') === 'application/json') {
                return response()->json([
                    'success' => true,
                    'location' => [
                        'id' => $location->id,
                        'name' => $location->name,
                        'description' => $location->description,
                        'houses_count' => $location->houses->count(),
                        'media_count' => $location->media->count(),
                        'primary_image_url' => $location->primaryImage ? $location->primaryImage->full_url : null,
                    ],
                    'message' => 'Location created successfully'
                ]);
            }

            return redirect()->route('admin.locations')->with('success', 'Location created successfully');
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to create location: ' . $e->getMessage());
            
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create location: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Failed to create location')->withInput();
        }
    }

    public function updateLocation(Request $request, Location $location)
    {
        // Increase memory limit and execution time for file processing
        ini_set('memory_limit', '256M');
        ini_set('max_execution_time', '300'); // 5 minutes
        
        // Ensure user is authenticated and authorized
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $request->validate([
            'name' => 'required|string|max:100|unique:locations,name,' . $location->id,
            'description' => 'nullable|string',
            'media_files' => 'nullable|array|max:10', // Limit to 10 files max
            'media_files.*' => 'file|mimes:jpeg,png,jpg,gif,webp,mp4,mov,avi,wmv|max:20480', // 20MB max
            'media_titles' => 'nullable|array',
            'media_titles.*' => 'nullable|string|max:200',
            'primary_media' => 'nullable|integer'
        ]);

        DB::beginTransaction();
        
        try {
            // Update location basic info
            $location->update($request->only(['name', 'description']));

            // Handle media uploads if present
            if ($request->hasFile('media_files')) {
                $this->handleLocationMediaUploads($location, $request);
            }

            DB::commit();
            
            // Load the location with its media and houses for the response
            $location->load(['media', 'houses', 'primaryImage']);

            // Check if this is an AJAX request (from welcome page) or regular form submission
            if ($request->expectsJson() || $request->ajax() || $request->wantsJson() || $request->header('Accept') === 'application/json') {
                return response()->json([
                    'success' => true,
                    'location' => [
                        'id' => $location->id,
                        'name' => $location->name,
                        'description' => $location->description,
                        'houses_count' => $location->houses->count(),
                        'media_count' => $location->media->count(),
                        'primary_image_url' => $location->primaryImage ? $location->primaryImage->full_url : null,
                    ],
                    'message' => 'Location updated successfully'
                ]);
            }

            // For the update route, always return JSON since it's only called via AJAX
            return response()->json([
                'success' => true,
                'location' => [
                    'id' => $location->id,
                    'name' => $location->name,
                    'description' => $location->description,
                    'houses_count' => $location->houses->count(),
                    'media_count' => $location->media->count(),
                    'primary_image_url' => $location->primaryImage ? $location->primaryImage->full_url : null,
                ],
                'message' => 'Location updated successfully'
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to update location: ' . $e->getMessage());
            
            // For the update route, always return JSON since it's only called via AJAX
            return response()->json([
                'success' => false,
                'message' => 'Failed to update location: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteLocation(Location $location)
    {
        DB::beginTransaction();
        try {
            $location->houses()->delete();
            $location->delete();
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Location deleted successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error deleting location'
            ], 500);
        }
    }

    public function deleteLocationMedia(Location $location, LocationMedia $media)
    {
        // Ensure user is authenticated and authorized
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        // Verify the media belongs to this location
        if ($media->location_id !== $location->id) {
            return response()->json([
                'success' => false,
                'message' => 'Media does not belong to this location'
            ], 400);
        }

        DB::beginTransaction();
        try {
            // Delete the file from storage
            $mediaPath = str_replace('/storage/', '', $media->media_url);
            if (\Storage::disk('public')->exists($mediaPath)) {
                \Storage::disk('public')->delete($mediaPath);
            }

            // If this was the primary media and there are other media files, set another as primary
            $wasPrimary = $media->is_primary;
            
            // Delete the media record
            $media->delete();

            // If this was primary, set another image as primary
            if ($wasPrimary) {
                $nextPrimaryMedia = LocationMedia::where('location_id', $location->id)
                    ->where('media_type', 'image')
                    ->first();
                    
                if ($nextPrimaryMedia) {
                    $nextPrimaryMedia->update(['is_primary' => true]);
                }
            }

            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Media deleted successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Failed to delete location media: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete media'
            ], 500);
        }
    }

    public function setLocationMediaPrimary(Location $location, LocationMedia $media)
    {
        // Ensure user is authenticated and authorized
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        // Verify the media belongs to this location
        if ($media->location_id !== $location->id) {
            return response()->json([
                'success' => false,
                'message' => 'Media does not belong to this location'
            ], 400);
        }

        DB::beginTransaction();
        try {
            // Remove primary status from all media for this location
            LocationMedia::where('location_id', $location->id)
                ->update(['is_primary' => false]);

            // Set this media as primary
            $media->update(['is_primary' => true]);

            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Primary media updated successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Failed to set primary media: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update primary media'
            ], 500);
        }
    }

    public function createHouse(Request $request)
    {
        $request->validate([
            'location_id' => 'required|exists:locations,id',
            'owner_id' => 'required|exists:users,id',
            'name' => 'required|string|max:150',
            'street_address' => 'required|string|max:200',
            'house_number' => 'nullable|string|max:10',
            'distance_to_sea' => 'nullable|string|max:50',
            'parking_available' => 'nullable|boolean',
            'pet_friendly' => 'nullable|boolean',
            'parking_description' => 'nullable|string',
            'description' => 'nullable|string'
        ]);

        $house = House::create($request->all());

        return response()->json([
            'success' => true,
            'house' => $house->load(['location', 'owner']),
            'message' => 'House created successfully'
        ]);
    }

    public function updateHouse(Request $request, House $house)
    {
        $request->validate([
            'location_id' => 'required|exists:locations,id',
            'owner_id' => 'required|exists:users,id',
            'name' => 'required|string|max:150',
            'street_address' => 'required|string|max:200',
            'house_number' => 'nullable|string|max:10',
            'distance_to_sea' => 'nullable|string|max:50',
            'parking_available' => 'nullable|boolean',
            'pet_friendly' => 'nullable|boolean',
            'parking_description' => 'nullable|string',
            'description' => 'nullable|string'
        ]);

        $house->update($request->all());

        return response()->json([
            'success' => true,
            'house' => $house->load(['location', 'owner']),
            'message' => 'House updated successfully'
        ]);
    }

    public function deleteHouse(House $house)
    {
        DB::beginTransaction();
        try {
            $house->suites()->delete();
            $house->delete();
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'House deleted successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error deleting house'
            ], 500);
        }
    }

    public function createSuite(Request $request)
    {
        $request->validate([
            'house_id' => 'required|exists:houses,id',
            'name' => 'required|string|max:100',
            'capacity_people' => 'required|integer|min:1|max:20',
            'bedrooms' => 'required|integer|min:0|max:10',
            'bathrooms' => 'required|integer|min:0|max:10',
            'floor_number' => 'nullable|integer',
            'description' => 'nullable|string',
            'amenities' => 'array',
            'amenities.*' => 'string|max:100'
        ]);

        $suite = Suite::create($request->except(['amenities']));

        // Add amenities if provided
        if ($request->has('amenities')) {
            foreach ($request->amenities as $amenity) {
                $suite->amenities()->create(['amenity_name' => $amenity]);
            }
        }

        return response()->json([
            'success' => true,
            'suite' => $suite->load(['house', 'amenities']),
            'message' => 'Suite created successfully'
        ]);
    }

    public function updateSuite(Request $request, Suite $suite)
    {
        $request->validate([
            'house_id' => 'required|exists:houses,id',
            'name' => 'required|string|max:100',
            'capacity_people' => 'required|integer|min:1|max:20',
            'bedrooms' => 'required|integer|min:0|max:10',
            'bathrooms' => 'required|integer|min:0|max:10',
            'floor_number' => 'nullable|integer',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'amenities' => 'array',
            'amenities.*' => 'string|max:100'
        ]);

        $suite->update($request->except(['amenities']));

        // Update amenities if provided
        if ($request->has('amenities')) {
            // Delete existing amenities
            $suite->amenities()->delete();
            // Add new amenities
            foreach ($request->amenities as $amenity) {
                $suite->amenities()->create(['amenity_name' => $amenity]);
            }
        }

        return response()->json([
            'success' => true,
            'suite' => $suite->load(['house', 'amenities']),
            'message' => 'Suite updated successfully'
        ]);
    }

    public function deleteSuite(Suite $suite)
    {
        $suite->delete();

        return response()->json([
            'success' => true,
            'message' => 'Suite deleted successfully'
        ]);
    }

    public function updateSiteContent(Request $request)
    {
        $request->validate([
            'main_heading' => 'required|string|max:200',
            'main_description' => 'required|string|max:1000',
            'background_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240', // 10MB max
            'overlay_opacity' => 'nullable|integer|min:0|max:100',
            'remove_background' => 'nullable|boolean'
        ]);

        try {
            // Update text content
            SiteContent::set('main_heading', $request->main_heading, Auth::id());
            SiteContent::set('main_description', $request->main_description, Auth::id());
            SiteContent::set('overlay_opacity', $request->overlay_opacity ?? 50, Auth::id());
        } catch (\Exception $e) {
            Log::error('Failed to update site content: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update content: ' . $e->getMessage()
            ], 500);
        }

        $backgroundImageUrl = null;

        // Handle background image removal
        if ($request->has('remove_background') && $request->remove_background) {
            $oldImage = SiteContent::get('background_image');
            if ($oldImage && Storage::exists('public/' . $oldImage)) {
                Storage::delete('public/' . $oldImage);
            }
            SiteContent::set('background_image', null, Auth::id());
        }
        
        // Handle new background image upload
        if ($request->hasFile('background_image')) {
            // Delete old background image if it exists
            $oldImage = SiteContent::get('background_image');
            if ($oldImage && Storage::exists('public/' . $oldImage)) {
                Storage::delete('public/' . $oldImage);
            }

            // Optimize and store new image
            $imagePath = $this->optimizeImage($request->file('background_image'));
            SiteContent::set('background_image', $imagePath, Auth::id());
            $backgroundImageUrl = Storage::url($imagePath);
        }

        return response()->json([
            'success' => true,
            'message' => 'Site content updated successfully',
            'background_image' => $backgroundImageUrl
        ]);
    }

    public function bookings(Request $request)
    {
        $query = Booking::with(['suite.house.location', 'creator']);

        // Filter by booking source (admin/owner)
        if ($request->source && $request->source !== 'all') {
            if ($request->source === 'admin') {
                $query->where('is_owner_booking', false);
            } else {
                $query->where('is_owner_booking', true);
            }
        }

        // Filter by location
        if ($request->location_id) {
            $query->whereHas('suite.house', function($q) use ($request) {
                $q->where('location_id', $request->location_id);
            });
        }

        // Filter by owner
        if ($request->owner_id) {
            $query->whereHas('suite.house', function($q) use ($request) {
                $q->where('owner_id', $request->owner_id);
            });
        }

        // Filter by house
        if ($request->house_id) {
            $query->whereHas('suite', function($q) use ($request) {
                $q->where('house_id', $request->house_id);
            });
        }

        // Filter by suite
        if ($request->suite_id) {
            $query->where('suite_id', $request->suite_id);
        }

        // Filter by status
        if ($request->status) {
            switch($request->status) {
                case 'active':
                    $query->whereNull('cancelled_at')
                          ->where('check_out', '>=', now());
                    break;
                case 'completed':
                    $query->whereNull('cancelled_at')
                          ->where('check_out', '<', now());
                    break;
                case 'cancelled':
                    $query->whereNotNull('cancelled_at');
                    break;
                case 'upcoming':
                    $query->whereNull('cancelled_at')
                          ->where('check_in', '>', now());
                    break;
            }
        }

        $bookings = $query->orderBy('check_in', 'desc')->paginate(20);
        $locations = Location::all();
        $owners = User::where('role', 'owner')->get();
        $houses = House::with('location')->get();
        $suites = Suite::with('house')->get();

        return view('admin.bookings', compact('bookings', 'locations', 'owners', 'houses', 'suites'));
    }

    public function createBooking(Request $request)
    {
        $request->validate([
            'suite_id' => 'required|exists:suites,id',
            'guest_name' => 'required|string|max:200',
            'guest_phone' => 'required|string|max:20',
            'guest_quantity' => 'required|integer|min:1',
            'check_in' => 'required|date|after:today',
            'check_out' => 'required|date|after:check_in',
            'parking_needed' => 'boolean',
            'pets_allowed' => 'boolean',
            'has_small_kids' => 'boolean',
            'deposit_paid' => 'boolean',
            'deposit_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $checkIn = new \DateTime($request->check_in);
        $checkOut = new \DateTime($request->check_out);
        $totalNights = $checkIn->diff($checkOut)->days;

        DB::beginTransaction();
        try {
            $booking = Booking::create([
                'suite_id' => $request->suite_id,
                'created_by' => Auth::id(),
                'booking_source' => 'admin',
                'guest_name' => $request->guest_name,
                'guest_phone' => $request->guest_phone,
                'guest_quantity' => $request->guest_quantity,
                'check_in' => $request->check_in,
                'check_out' => $request->check_out,
                'total_nights' => $totalNights,
                'parking_needed' => $request->boolean('parking_needed'),
                'pets_allowed' => $request->boolean('pets_allowed'),
                'has_small_kids' => $request->boolean('has_small_kids'),
                'deposit_paid' => $request->boolean('deposit_paid'),
                'deposit_amount' => $request->deposit_amount,
                'notes' => $request->notes,
                'is_owner_booking' => false,
            ]);

            // Create BookingDate records for each day of the booking
            $current = clone $checkIn;
            while ($current < $checkOut) {
                BookingDate::create([
                    'booking_id' => $booking->id,
                    'suite_id' => $request->suite_id,
                    'booking_date' => $current->format('Y-m-d')
                ]);
                $current->addDay();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'booking' => $booking->load(['suite', 'creator']),
                'message' => 'Booking created successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error creating booking: ' . $e->getMessage()
            ], 500);
        }
    }


    public function updateBooking(Request $request, Booking $booking)
    {
        if ($booking->is_owner_booking && !$request->has('force_admin_edit')) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot edit owner bookings'
            ], 403);
        }

        $request->validate([
            'suite_id' => 'required|exists:suites,id',
            'guest_name' => 'required|string|max:200',
            'guest_phone' => 'required|string|max:20',
            'guest_quantity' => 'required|integer|min:1',
            'check_in' => 'required|date',
            'check_out' => 'required|date|after:check_in',
            'parking_needed' => 'boolean',
            'pets_allowed' => 'boolean',
            'has_small_kids' => 'boolean',
            'deposit_paid' => 'boolean',
            'deposit_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $checkIn = new \DateTime($request->check_in);
        $checkOut = new \DateTime($request->check_out);
        $totalNights = $checkIn->diff($checkOut)->days;

        $booking->update([
            'suite_id' => $request->suite_id,
            'guest_name' => $request->guest_name,
            'guest_phone' => $request->guest_phone,
            'guest_quantity' => $request->guest_quantity,
            'check_in' => $request->check_in,
            'check_out' => $request->check_out,
            'total_nights' => $totalNights,
            'parking_needed' => $request->boolean('parking_needed'),
            'pets_allowed' => $request->boolean('pets_allowed'),
            'has_small_kids' => $request->boolean('has_small_kids'),
            'deposit_paid' => $request->boolean('deposit_paid'),
            'deposit_amount' => $request->deposit_amount,
            'notes' => $request->notes,
        ]);

        return response()->json([
            'success' => true,
            'booking' => $booking->load(['suite', 'creator']),
            'message' => 'Booking updated successfully'
        ]);
    }

    public function cancelBooking(Booking $booking)
    {
        if ($booking->is_owner_booking) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot cancel owner bookings'
            ], 403);
        }

        $booking->update([
            'cancelled_at' => now(),
            'cancelled_by' => Auth::id()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Booking cancelled successfully'
        ]);
    }

    public function getHouse(House $house)
    {
        $house->load(['location', 'owner', 'suites']);
        
        return response()->json([
            'success' => true,
            'house' => $house
        ]);
    }

    // Suite management methods
    public function getHouseSuites(House $house)
    {
        $suites = $house->suites()->with('amenities')->get();
        
        return response()->json([
            'success' => true,
            'suites' => $suites
        ]);
    }

    public function getSuite(Suite $suite)
    {
        return response()->json([
            'success' => true,
            'suite' => $suite
        ]);
    }

    public function calendar(Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Access denied. Admin privileges required.');
        }

        $locations = Location::with('houses.suites')->get();
        
        // Get bookings for the calendar view using the database view
        $year = $request->get('year', date('Y'));
        $month = $request->get('month', date('n'));
        $locationId = $request->get('location_id');
        
        $query = DB::table('calendar_view')
                   ->whereYear('booking_date', $year)
                   ->whereMonth('booking_date', $month);
        
        if ($locationId) {
            $query->where('location_name', Location::find($locationId)->name ?? '');
        }
        
        $bookings = $query->get();
        
        return view('admin.calendar', compact('locations', 'bookings'));
    }

    public function calendarDay($date)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Access denied. Admin privileges required.');
        }

        $bookings = DB::table('calendar_view')
                     ->where('booking_date', $date)
                     ->get();

        return response()->json([
            'success' => true,
            'bookings' => $bookings
        ]);
    }
}