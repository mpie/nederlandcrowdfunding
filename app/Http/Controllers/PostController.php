<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

final class PostController
{
    public function index(): View
    {
        /** @var LengthAwarePaginator<Post> $posts */
        $posts = Post::published()
            ->orderByDesc('published_at')
            ->paginate(9);

        return view('posts.index', [
            'posts' => $posts,
        ]);
    }

    public function show(Post $post): View
    {
        if ($post->status->value !== 'published') {
            abort(404);
        }

        $relatedPosts = Post::published()
            ->where('id', '!=', $post->id)
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();

        return view('posts.show', [
            'post' => $post,
            'relatedPosts' => $relatedPosts,
        ]);
    }
}