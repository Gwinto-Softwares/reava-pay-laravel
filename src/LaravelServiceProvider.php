<?php

namespace ReavaPay;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use ReavaPay\Console\BalancesCommand;
use ReavaPay\Console\StatusCommand;
use ReavaPay\Console\TransactionsCommand;
use ReavaPay\Console\WebhooksRetryCommand;
use ReavaPay\Http\Controllers\WebhookController;
use ReavaPay\Models\ReavaPaySetting;

class LaravelServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/reava-pay.php', 'reava-pay');

        // Register manager — credentials come from DB (connect flow), not .env
        $this->app->singleton(ReavaPayManager::class, function ($app) {
            $config = $app['config']['reava-pay'];
            $apiKey = '';
            $baseUrl = $config['base_url'] ?? 'https://reavapay.com/api/v1';

            try {
                $settings = ReavaPaySetting::first();
                if ($settings && $settings->hasValidCredentials()) {
                    $apiKey = $settings->api_secret;
                    $baseUrl = $settings->base_url ?: $baseUrl;
                }
            } catch (\Throwable) {
                // Table may not exist yet
            }

            return new ReavaPayManager($apiKey, $baseUrl, $config['timeout'] ?? 30);
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/reava-pay.php' => config_path('reava-pay.php'),
            ], 'reava-pay-config');

            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/reava-pay'),
            ], 'reava-pay-views');

            $this->publishes([
                __DIR__ . '/../database/migrations/' => database_path('migrations'),
            ], 'reava-pay-migrations');

            $this->commands([
                StatusCommand::class,
                TransactionsCommand::class,
                BalancesCommand::class,
                WebhooksRetryCommand::class,
            ]);
        }

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'reava-pay');
        $this->loadRoutesFrom(__DIR__ . '/../routes/admin.php');

        // Auto-register webhook route (CSRF-exempt)
        $path = config('reava-pay.webhook_path', 'webhooks/reava-pay');
        Route::post($path, [WebhookController::class, 'handle'])
            ->name('reava-pay.webhooks')
            ->middleware('web')
            ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);
    }
}
