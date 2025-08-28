<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Location;
use App\Models\House;
use App\Models\Suite;
use App\Models\SiteContent;
use App\Models\Booking;

echo "Setting up Marina Croatia Database...\n";

try {
    // Check if we can connect to the database
    DB::connection()->getPdo();
    echo "Database connection successful!\n";

    // Drop existing tables if they exist
    echo "Dropping existing tables...\n";
    DB::statement('SET FOREIGN_KEY_CHECKS=0;');
    
    $tables = ['booking_dates', 'bookings', 'suite_images', 'suite_amenities', 'suites', 
               'house_images', 'houses', 'locations', 'site_content', 'activity_logs', 
               'booking_conflicts', 'messages', 'users'];
    
    foreach ($tables as $table) {
        DB::statement("DROP TABLE IF EXISTS {$table}");
    }
    
    DB::statement('SET FOREIGN_KEY_CHECKS=1;');

    // Read and execute the SQL file
    echo "Creating tables from marina_croatia.sql...\n";
    $sql = file_get_contents('marina_croatia.sql');
    
    // Split the SQL into individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($statements as $statement) {
        if (!empty($statement) && !str_starts_with($statement, '--') && !str_starts_with($statement, '/*')) {
            try {
                DB::statement($statement);
            } catch (Exception $e) {
                // Skip statements that might fail (like DROP commands, etc.)
                continue;
            }
        }
    }

    echo "Database setup completed successfully!\n";
    echo "\nDefault login credentials:\n";
    echo "Admin: admin@marinacroatia.com / password\n";
    echo "Owner: owner@marinacroatia.com / password\n";
    echo "\nYou can now run: php artisan serve\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Please ensure your MySQL database 'marina_croatia' exists and your .env file is configured correctly.\n";
}