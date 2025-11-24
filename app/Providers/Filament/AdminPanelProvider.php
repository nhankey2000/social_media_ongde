<?php

namespace App\Providers\Filament;

use App\Filament\Resources\CategoryResource;
use App\Filament\Resources\DanhmucBXResource;
use App\Filament\Resources\DanhmucNHSResource;
use App\Filament\Resources\DanhmucResource;
use App\Filament\Resources\DataImagesBXResource;
use App\Filament\Resources\DataImagesNHResource;
use App\Filament\Resources\DataPostBXResource;
use App\Filament\Resources\DataPostNHResource;
use App\Filament\Resources\DataPostResource;
use App\Filament\Resources\ImageLibraryResource;
use App\Filament\Resources\ImageMenuResource;
use App\Filament\Resources\ImagesDataResource;
use App\Filament\Resources\MenuCategoryResource;
use App\Filament\Resources\UserResource;
use App\Filament\Resources\ReportResource;
use App\Filament\Resources\LocationResource;
use App\Filament\Resources\MenuNhaHangResource;
use App\Models\MenuNhaHang;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use App\Filament\Resources\MessageResource;
use App\Filament\Resources\VipCardResource;
// Thêm import cho MessageResource
use App\Filament\Resources\PlatformAccountResource;

// Thêm import cho PlatformAccountResource

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandLogo(asset('images/logo.png'))
            ->brandLogoHeight('50px') // 👈 chỉnh tại đây


            ->renderHook('panels::footer', function () {
//                return view('components.chatbot');
            })
            ->colors([
                'primary' => Color::Amber,
            ])
//            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->resources([
                DanhmucBXResource::class,
                DanhmucNHSResource::class,
                DanhMucResource::class,
                DataPostNHResource::class,
                DataPostBXResource::class,
                DataPostResource::class,
                ImageMenuResource::class,
                MenuCategoryResource::class,
                UserResource::class,
                DataImagesBXResource::class,
                DataImagesNHResource::class,
                ImagesDataResource::class,
                VipCardResource::class,
                MenuNhaHangResource::class,
                LocationResource::class,
                ReportResource::class,
            ])
//
//            ->viteTheme('resources/css/filament/theme.css')

//            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])

//            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);

    }
}
