<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Post;
use Illuminate\Console\Command;

final class CleanBlogContent extends Command
{
    protected $signature = 'cms:clean-blog-content';

    protected $description = 'Clean imported WordPress HTML from blog posts';

    private const array ALLOWED_TAGS = [
        'p', 'br', 'strong', 'b', 'em', 'i', 'u', 'a',
        'ul', 'ol', 'li',
        'h2', 'h3', 'h4', 'h5', 'h6',
        'blockquote', 'pre', 'code',
        'table', 'thead', 'tbody', 'tr', 'th', 'td',
        'img', 'figure', 'figcaption',
        'hr', 'sup', 'sub', 'span',
    ];

    public function handle(): int
    {
        $posts = Post::all();
        $bar = $this->output->createProgressBar($posts->count());

        $this->info("Cleaning {$posts->count()} blog posts...");
        $bar->start();

        foreach ($posts as $post) {
            $cleanContent = $this->cleanHtml($post->content ?? '');
            $cleanExcerpt = $this->cleanExcerpt($post->excerpt ?? '', $post->title);

            $post->update([
                'content' => $cleanContent,
                'excerpt' => $cleanExcerpt,
            ]);

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('All blog posts cleaned successfully.');

        return self::SUCCESS;
    }

    private function cleanHtml(string $html): string
    {
        // Remove WordPress wrapper elements
        $html = (string) preg_replace('/<header[^>]*>.*?<\/header>/si', '', $html);
        $html = (string) preg_replace('/<div\s+itemprop="author"[^>]*>.*?<\/div>/si', '', $html);
        $html = (string) preg_replace('/<div\s+itemprop="publisher"[^>]*>.*?<\/div>/si', '', $html);
        $html = (string) preg_replace('/<meta[^>]*\/?>/si', '', $html);

        // Remove WordPress comment markers
        $html = (string) preg_replace('/<!--.*?-->/s', '', $html);

        // Extract content from articleBody div if present
        if (preg_match('/<div\s+itemprop="articleBody"[^>]*>(.*?)<\/div>/si', $html, $matches)) {
            $html = $matches[1];
        }

        // Remove entry-content wrapper div
        $html = (string) preg_replace('/<div[^>]*class="[^"]*entry-content[^"]*"[^>]*>(.*?)<\/div>/si', '$1', $html);

        // Remove date strings that appear as bare text (e.g. "15-01-2026")
        $html = (string) preg_replace('/^\s*\d{2}-\d{2}-\d{4}\s*/m', '', $html);

        // Remove itemprop and itemscope attributes from remaining tags
        $html = (string) preg_replace('/\s*(itemprop|itemscope|itemtype)\s*=\s*"[^"]*"/i', '', $html);
        $html = (string) preg_replace('/\s*itemscope\b/i', '', $html);

        // Strip to allowed tags only
        $allowedTagStr = implode('', array_map(fn (string $tag): string => "<{$tag}>", self::ALLOWED_TAGS));
        $html = strip_tags($html, $allowedTagStr);

        // Remove empty paragraphs
        $html = (string) preg_replace('/<p>\s*(&nbsp;)?\s*<\/p>/i', '', $html);

        // Normalize whitespace
        $html = (string) preg_replace('/\n{3,}/', "\n\n", $html);

        // Remove target="_self" from links (unnecessary)
        $html = str_replace(' target="_self"', '', $html);

        // Rewrite old domain links to relative
        $html = str_replace('https://nederlandcrowdfunding.nl', '', $html);

        return trim($html);
    }

    private function cleanExcerpt(string $excerpt, string $title): string
    {
        // Remove repeated title from excerpt
        $excerpt = str_replace($title, '', $excerpt);

        // Remove date patterns at the start
        $excerpt = (string) preg_replace('/^\s*\d{2}-\d{2}-\d{4}\s*/m', '', $excerpt);

        // Trim and clean whitespace
        $excerpt = trim((string) preg_replace('/\s+/', ' ', $excerpt));

        // Limit to 300 chars
        if (mb_strlen($excerpt) > 300) {
            $excerpt = mb_substr($excerpt, 0, 297) . '...';
        }

        return $excerpt;
    }
}