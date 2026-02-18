<?php

declare(strict_types=1);

namespace App\Enums;

enum MenuLocation: string
{
    case Navbar = 'navbar';
    case FooterPages = 'footer_pages';
    case FooterAbout = 'footer_about';

    public function label(): string
    {
        return match ($this) {
            self::Navbar => 'Navigatiebalk',
            self::FooterPages => 'Footer - Pagina\'s',
            self::FooterAbout => 'Footer - Over ons',
        };
    }
}