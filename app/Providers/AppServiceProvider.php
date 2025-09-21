<?php

declare(strict_types=1);

namespace App\Providers;

use App\Actions\ConvertCurrencyAction;
use App\Actions\GetSupportedCurrenciesAction;
use App\Actions\LoginUserAction;
use App\Actions\LogoutUserAction;
use App\Actions\RegisterUserAction;
use App\Application\UseCases\ConvertCurrencyUseCase;
use App\Application\UseCases\GetSupportedCurrenciesUseCase;
use App\Application\UseCases\LoginUserUseCase;
use App\Application\UseCases\LogoutUserUseCase;
use App\Application\UseCases\RegisterUserUseCase;
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

        $this->app->bind(ConvertCurrencyUseCase::class, function ($app) {
            return new ConvertCurrencyUseCase(
                $app->make(CurrencyConverterService::class)
            );
        });

        $this->app->bind(GetSupportedCurrenciesUseCase::class, function ($app) {
            return new GetSupportedCurrenciesUseCase(
                $app->make(CurrencyConverterService::class)
            );
        });

        $this->app->bind(ConvertCurrencyAction::class, function ($app) {
            return new ConvertCurrencyAction(
                $app->make(ConvertCurrencyUseCase::class)
            );
        });

        $this->app->bind(GetSupportedCurrenciesAction::class, function ($app) {
            return new GetSupportedCurrenciesAction(
                $app->make(GetSupportedCurrenciesUseCase::class)
            );
        });

        // Authentication UseCases
        $this->app->bind(RegisterUserUseCase::class);
        $this->app->bind(LoginUserUseCase::class);
        $this->app->bind(LogoutUserUseCase::class);

        // Authentication Actions
        $this->app->bind(RegisterUserAction::class, function ($app) {
            return new RegisterUserAction(
                $app->make(RegisterUserUseCase::class)
            );
        });

        $this->app->bind(LoginUserAction::class, function ($app) {
            return new LoginUserAction(
                $app->make(LoginUserUseCase::class)
            );
        });

        $this->app->bind(LogoutUserAction::class, function ($app) {
            return new LogoutUserAction(
                $app->make(LogoutUserUseCase::class)
            );
        });
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
