<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class PostController
{
    public function index(Request $request): View
    {
        $search = $request->string('zoeken')->trim()->value();

        $posts = Post::published()
            ->when($search !== '', fn ($query) => $query
                ->where(fn ($q) => $q
                    ->where('title', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%")
                )
            )
            ->orderByDesc('published_at')
            ->paginate(9)
            ->withQueryString();

        return view('posts.index', [
            'posts' => $posts,
            'search' => $search,
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $search = $request->string('q')->trim()->value();

        if (mb_strlen($search) < 2) {
            return response()->json([]);
        }

        $posts = Post::published()
            ->where(fn ($q) => $q
                ->where('title', 'like', "%{$search}%")
                ->orWhere('excerpt', 'like', "%{$search}%")
            )
            ->orderByDesc('published_at')
            ->limit(8)
            ->get(['title', 'slug', 'excerpt', 'published_at']);

        return response()->json(
            $posts->map(fn (Post $post): array => [
                'title' => $post->title,
                'url' => route('posts.show', $post),
                'excerpt' => $post->excerpt ? \Illuminate\Support\Str::limit($post->excerpt, 100) : null,
                'date' => $post->published_at?->format('d M Y'),
            ])->values()
        );
    }

    public function show(Post $post): View
    {
        if ($post->status->value !== 'published') {
            abort(404);
        }

        $previousPost = Post::published()
            ->where('published_at', '<', $post->published_at)
            ->orderByDesc('published_at')
            ->first();

        $nextPost = Post::published()
            ->where('published_at', '>', $post->published_at)
            ->orderBy('published_at')
            ->first();

        $relatedPosts = Post::published()
            ->where('id', '!=', $post->id)
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();

        return view('posts.show', [
            'post' => $post,
            'previousPost' => $previousPost,
            'nextPost' => $nextPost,
            'relatedPosts' => $relatedPosts,
        ]);
    }
}