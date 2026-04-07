<?php

namespace App\Providers;

use App\Support\Currency;
use App\Models\User;
use App\Observers\UserObserver;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        User::observe(UserObserver::class);

        Blade::directive('money', function ($expression) {
            return "<?php echo \\App\\Support\\Currency::format({$expression}); ?>";
        });
    }
}
