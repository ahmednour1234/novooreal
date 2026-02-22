<?php

namespace App\Providers;
ini_set('memory_limit', '-1');
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\ServiceProvider;
use App\Models\BusinessSetting;
use Carbon\Carbon;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */


public function boot()
{
    // Set the paginator to use Bootstrap for pagination styling
    Paginator::useBootstrap();

    // Set the locale for Carbon to Arabic
    Carbon::setLocale('ar'); 

    try {
        // Get the timezone setting from the BusinessSetting model
        $timezone = BusinessSetting::where('key', 'time_zone')->first();

        if ($timezone) {
            // Set the application's timezone based on the stored value
            config(['app.timezone' => $timezone->value]);
            date_default_timezone_set($timezone->value); // Set the default timezone for PHP
        }
    } catch (\Exception $exception) {
        // Optionally, log the exception if needed
        // Log::error("Error while setting timezone: " . $exception->getMessage());
    }
}

}
