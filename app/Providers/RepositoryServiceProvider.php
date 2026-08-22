<?php

namespace App\Providers;

use App\Repositories\Contracts\AvatarUploadTokenRepositoryContract;
use App\Repositories\Contracts\ClientMetaRepositoryContract;
use App\Repositories\Contracts\CmcPostRepositoryContract;
use App\Repositories\Contracts\DonationRepositoryContract;
use App\Repositories\Contracts\FundRepositoryContract;
use App\Repositories\Contracts\GroupRepositoryContract;
use App\Repositories\Contracts\NotificationRepositoryContract;
use App\Repositories\Contracts\StageRepositoryContract;
use App\Repositories\Contracts\TokenRepositoryContract;
use App\Repositories\Contracts\UserRepositoryContract;
use App\Repositories\Contracts\YoutubeVideoRepositoryContract;
use App\Repositories\Eloquent\AvatarUploadTokenRepository;
use App\Repositories\Eloquent\ClientMetaRepository;
use App\Repositories\Eloquent\CmcPostRepository;
use App\Repositories\Eloquent\DonationRepository;
use App\Repositories\Eloquent\FundRepository;
use App\Repositories\Eloquent\GroupRepository;
use App\Repositories\Eloquent\NotificationRepository;
use App\Repositories\Eloquent\StageRepository;
use App\Repositories\Eloquent\TokenRepository;
use App\Repositories\Eloquent\UserRepository;
use App\Repositories\Eloquent\YoutubeVideoRepository;
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
        $this->app->bind(GroupRepositoryContract::class, GroupRepository::class);
        $this->app->bind(NotificationRepositoryContract::class, NotificationRepository::class);
        $this->app->bind(CmcPostRepositoryContract::class, CmcPostRepository::class);
        $this->app->bind(FundRepositoryContract::class, FundRepository::class);
        $this->app->bind(DonationRepositoryContract::class, DonationRepository::class);
        $this->app->bind(YoutubeVideoRepositoryContract::class, YoutubeVideoRepository::class);
    }
}
