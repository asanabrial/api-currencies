<?php

declare(strict_types=1);

namespace App\Providers;

use App\Application\Actions\ConvertCurrencyAction;
use App\Application\Actions\GetSupportedCurrenciesAction;
use App\Application\Actions\LoginUserAction;
use App\Application\Actions\LogoutUserAction;
use App\Application\Actions\RegisterUserAction;
use App\Domain\Ports\ExchangeRateRepository;
use App\Domain\Services\CurrencyConverterService;
use App\Infrastructure\Adapters\ExternalApiExchangeRateRepository;
use GuzzleHttp\Client;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ExchangeRateRepository::class, function ($app) {
            return new ExternalApiExchangeRateRepository(new Client());
        });

        $this->app->bind(CurrencyConverterService::class, function ($app) {
            return new CurrencyConverterService(
                $app->make(ExchangeRateRepository::class)
            );
        });

        // Application Actions
        $this->app->bind(ConvertCurrencyAction::class, function ($app) {
            return new ConvertCurrencyAction(
                $app->make(CurrencyConverterService::class)
            );
        });

        $this->app->bind(GetSupportedCurrenciesAction::class, function ($app) {
            return new GetSupportedCurrenciesAction(
                $app->make(CurrencyConverterService::class)
            );
        });

        $this->app->bind(RegisterUserAction::class);
        $this->app->bind(LoginUserAction::class);
        $this->app->bind(LogoutUserAction::class);
    }

    public function boot(): void
    {
        RateLimiter::for('currency-api', function (Request $request) {
            return $request->user()
                ? Limit::perMinute(120)->by($request->user()->id)
                : Limit::perMinute(60)->by($request->ip());
        });

        RateLimiter::for('currency-convert', function (Request $request) {
            return $request->user()
                ? Limit::perMinute(30)->by($request->user()->id)
                : Limit::perMinute(15)->by($request->ip());
        });
    }
}
