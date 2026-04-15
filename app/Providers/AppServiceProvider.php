<?php

namespace App\Providers;

use App\Support\Currency;
use App\Models\User;
use App\Observers\UserObserver;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if ($this->app->environment('testing', 'local')) {
            $compiledViewPath = sys_get_temp_dir()
                . DIRECTORY_SEPARATOR
                . ($this->app->environment('testing') ? 'bogo-eagles-finance-testing' : 'bogo-eagles-finance-local')
                . DIRECTORY_SEPARATOR
                . 'views';

            File::ensureDirectoryExists($compiledViewPath);

            config()->set('view.compiled', $compiledViewPath);
        }
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
