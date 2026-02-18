<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Post;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class PageController
{
    public function home(): View
    {
        $page = Page::where('slug', 'home')->published()->first();

        $latestPosts = Post::published()
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();

        $ledenPage = Page::where('slug', 'leden')->published()->first();
        $allMembers = collect($ledenPage?->block('members.items', []));
        $memberLogos = $allMembers
            ->filter(fn (array $member): bool => ! empty($member['logo']))
            ->values();

        return view('pages.home', [
            'page' => $page,
            'latestPosts' => $latestPosts,
            'memberLogos' => $memberLogos,
            'memberCount' => $allMembers->count(),
        ]);
    }

    public function show(string $slug): View
    {
        $page = Page::findByFullSlug($slug);

        if ($page === null || $page->status->value !== 'published') {
            throw new NotFoundHttpException();
        }

        return view('pages.show', [
            'page' => $page,
        ]);
    }
}