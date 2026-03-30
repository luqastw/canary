<?php

namespace App\Providers;

use App\Contracts\Repositories\FlagRepositoryInterface;
use App\Contracts\Repositories\GroupRepositoryInterface;
use App\Contracts\Repositories\TargetingRepositoryInterface;
use App\Contracts\Repositories\TenantRepositoryInterface;
use App\Contracts\Services\AuthServiceInterface;
use App\Contracts\Services\EvaluationServiceInterface;
use App\Contracts\Services\FlagServiceInterface;
use App\Contracts\Services\GroupServiceInterface;
use App\Contracts\Services\TargetingServiceInterface;
use App\Repositories\FlagRepository;
use App\Repositories\GroupRepository;
use App\Repositories\TargetingRepository;
use App\Repositories\TenantRepository;
use App\Services\AuthService;
use App\Services\EvaluationService;
use App\Services\FlagService;
use App\Services\GroupService;
use App\Services\TargetingService;
use Illuminate\Support\ServiceProvider;

class FeatureFlagServiceProvider extends ServiceProvider
{
    /**
     * All of the container bindings that should be registered.
     *
     * @var array
     */
    public array $bindings = [
        // Repositories
        TenantRepositoryInterface::class => TenantRepository::class,
        FlagRepositoryInterface::class => FlagRepository::class,
        GroupRepositoryInterface::class => GroupRepository::class,
        TargetingRepositoryInterface::class => TargetingRepository::class,

        // Services
        AuthServiceInterface::class => AuthService::class,
        FlagServiceInterface::class => FlagService::class,
        GroupServiceInterface::class => GroupService::class,
        TargetingServiceInterface::class => TargetingService::class,
        EvaluationServiceInterface::class => EvaluationService::class,
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
