<?php

declare(strict_types=1);

namespace App\View\Composers;

use App\Enums\MenuLocation;
use App\Models\MenuItem;
use Illuminate\View\View;

final readonly class MenuComposer
{
    public function compose(View $view): void
    {
        $view->with([
            'navbarItems' => MenuItem::getMenuForLocation(MenuLocation::Navbar),
            'footerPagesItems' => MenuItem::getMenuForLocation(MenuLocation::FooterPages),
            'footerAboutItems' => MenuItem::getMenuForLocation(MenuLocation::FooterAbout),
        ]);
    }
}