<?php

namespace App\Providers;

use App\Repositories\Contracts\AvatarUploadTokenRepositoryContract;
use App\Repositories\Contracts\ClientMetaRepositoryContract;
use App\Repositories\Contracts\StageRepositoryContract;
use App\Repositories\Contracts\TokenRepositoryContract;
use App\Repositories\Contracts\UserRepositoryContract;
use App\Repositories\Eloquent\AvatarUploadTokenRepository;
use App\Repositories\Eloquent\ClientMetaRepository;
use App\Repositories\Eloquent\StageRepository;
use App\Repositories\Eloquent\TokenRepository;
use App\Repositories\Eloquent\UserRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserRepositoryContract::class, UserRepository::class);
        $this->app->bind(ClientMetaRepositoryContract::class, ClientMetaRepository::class);
        $this->app->bind(TokenRepositoryContract::class, TokenRepository::class);
        $this->app->bind(StageRepositoryContract::class, StageRepository::class);
        $this->app->bind(AvatarUploadTokenRepositoryContract::class, AvatarUploadTokenRepository::class);
    }
}
