<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\PageStatus;
use App\Models\Post;
use DOMDocument;
use DOMNode;
use DOMXPath;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class RescrapePostContent extends Command
{
    protected $signature = 'cms:rescrape-posts';

    protected $description = 'Rescrape all blog posts from nederlandcrowdfunding.nl with correct dates, full content and images';

    private const string BASE_URL = 'https://nederlandcrowdfunding.nl';

    /** @var array<string, string> Slug mapping from our DB slug to original URL slug */
    private array $urlMap = [
        '2026-start-met-twee-nieuwe-leden-voor-de-branchevereniging' => '2026/01/15/2026-start-met-twee-nieuwe-leden-voor-de-branchevereniging',
        'crowdfinance-ruim-eur23mld-aan-spaargeld-actief-in-de-nederlandse-economie' => '2025/11/10/crowdfinance-ruim-23mld-aan-spaargeld-actief-in-de-nederlandse-economie',
        'eerste-week-van-de-crowdfinance-van-10-tot-met-14-november-2025' => '2025/10/28/eerste-week-van-de-crowdfinance-van-10-tot-met-14-november-2025',
        'nieuw-lid-voor-de-branchevereniging' => '2025/10/28/nieuw-lid-voor-de-branchevereniging',
        'stappenplan-afm-nu-ook-gepubliceerd' => '2025/09/16/stappenplan-afm-nu-ook-gepubliceerd',
        'branchevereniging-nederland-crowdfunding-roept-op-tot-structurele-beleidsaandacht-in-verkiezingsprogrammas' => '2025/07/17/branchevereniging-nederland-crowdfunding-roept-op-tot-structurele-beleidsaandacht-in-verkiezingsprogrammas',
        'nieuwe-leden-voor-de-branchevereniging' => '2025/06/26/nieuwe-leden-voor-de-branchevereniging',
        'fd-over-crowdfunding' => '2025/05/06/fd-over-crowdfunding',
        'zoeken-op-naam-in-het-kadaster-weer-mogelijk' => '2025/02/07/zoeken-op-naam-in-het-kadaster-weer-mogelijk',
        'vijf-nieuwe-leden-voor-de-branchevereniging' => '2025/02/04/vijf-nieuwe-leden',
        'crowdfundplatforms-verzoeken-toegang-tot-regelingen-van-essentieel-belang-voor-een-financieel-gezond-mkb' => '2024/04/05/crowdfundplatforms-verzoeken-toegang-tot-regelingen-van-essentieel-belang-voor-een-financieel-gezond-mkb',
        'leden-branchevereniging-nederland-crowdfunding-ontvangen-vergunning-van-de-afm' => '2023/11/13/afm-vergunning',
        'activeer-uw-spaargeld' => '2020/11/17/activeer-uw-spaargeld',
        'crowdfundplatforms-bieden-investeerders-transparante-informatie' => '2021/03/29/crowdfundplatforms-bieden-investeerders-transparante-informatie',
        'column-activeer-uw-spaargeld' => '2021/03/17/opinie-activeer-uw-spaargeld',
        'betrek-investerende-particulier-bij-herstel-nederlandse-mkb' => '2020/10/17/betrek-investerende-particulier-bij-herstel-nederlandse-mkb',
        'eu-parlement-stemt-in-met-crowdfund-regelgeving' => '2020/10/07/eu-parlement-stemt-in-met-crowdfund-regelgeving',
        'ledenmutaties-matchingcapital-en-waardevoorjegeld-nieuwe-leden' => '2020/07/20/ledenmutaties-matchingcapital-en-waardevoorjegeld-nieuwe-leden',
        'financieringsmonitor-bevestigt-belangrijke-rol-crowdfunding-in-nederlands-financieringslandschap' => '2020/01/28/financieringsmonitor-bevestigt-belangrijke-rol-crowdfunding-in-nederlands-financieringslandschap',
        'crowdfundingscan-helpt-ondernemers-financiering-te-vinden-bij-crowdfundbedrijven' => '2019/10/07/crowdfundingscan-helpt-ondernemers-financiering-te-vinden',
        'leden-nederland-crowdfunding-presenteren-reele-netto-rendementscijfers' => '2019/05/09/leden-nederland-crowdfunding-presenteren-reele-netto-rendementscijfers',
        'robbert-loos-directeur-nederland-crowdfunding' => '2019/04/02/robbert-loos-directeur-nederland-crowdfunding',
        'afm-crowdfundingplatformen-verbeteren-hun-informatieverstrekking' => '2019/02/28/crowdfundingplatformen-verbeteren-hun-informatieverstrekking',
        'complementaire-financiering-wint-terrein' => '2019/01/28/complementaire-financiering-wint-terrein',
        'crowdfunding-groeit-hard-door-in-2018' => '2019/01/01/crowdfunding-groeit-hard-door-in-2018',
        'eu-parlement-stemt-over-crowdfunding-regelgeving' => '2018/11/28/europees-parlement-stemt-over-crowdfunding-regelgeving',
    ];

    /** @var array<string, string> Additional posts not yet in DB (URL path => title placeholder) */
    private array $newPosts = [
        '2021/01/18/activeer-uw-spaargeld-jan21' => 'activeer-uw-spaargeld-jan21',
        '2021/02/10/activeer-uw-spaargeld-feb21' => 'activeer-uw-spaargeld-feb21',
        '2021/03/15/activeer-uw-spaargeld-mrt21' => 'activeer-uw-spaargeld-mrt21',
        '2021/05/04/activeer-uw-spaargeld-apr21' => 'activeer-uw-spaargeld-apr21',
        '2021/06/24/activeer-uw-spaargeld-jun21' => 'activeer-uw-spaargeld-jun21',
        '2021/09/13/activeer-uw-spaargeld-sept21' => 'activeer-uw-spaargeld-sept21',
        '2020/10/15/activeer-uw-spaargeld-okt20' => 'activeer-uw-spaargeld-okt20',
    ];

    public function handle(): int
    {
        Storage::disk('public')->makeDirectory('posts');

        $this->info('Updating existing posts...');
        foreach ($this->urlMap as $dbSlug => $urlPath) {
            $post = Post::where('slug', $dbSlug)->first();
            if (! $post) {
                $this->warn("Post not found: {$dbSlug}");
                continue;
            }

            $this->processPost($post, $urlPath);
        }

        $this->info('Importing new posts...');
        foreach ($this->newPosts as $urlPath => $slugHint) {
            if (Post::where('slug', $slugHint)->exists()) {
                $this->line("  Already exists: {$slugHint}");
                continue;
            }

            $post = new Post();
            $post->slug = $slugHint;
            $post->status = PageStatus::Published;
            $this->processPost($post, $urlPath, true);
        }

        $this->info('Done! All posts updated.');

        return self::SUCCESS;
    }

    private function processPost(Post $post, string $urlPath, bool $isNew = false): void
    {
        $url = self::BASE_URL . '/' . $urlPath . '/';
        $this->line("  Scraping: {$url}");

        $html = @file_get_contents($url);
        if (! $html) {
            $this->error("  Failed to fetch: {$url}");
            return;
        }

        $doc = new DOMDocument();
        @$doc->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_NOERROR);
        $xpath = new DOMXPath($doc);

        // Extract date from URL path
        if (preg_match('#^(\d{4})/(\d{2})/(\d{2})/#', $urlPath, $m)) {
            $publishedAt = "{$m[1]}-{$m[2]}-{$m[3]} 12:00:00";
            $post->published_at = $publishedAt;
        }

        // Extract title from h1
        $titleNodes = $xpath->query('//h1[contains(@class, "entry-title")] | //h1[contains(@class, "page-title")] | //header//h1 | //h1');
        if ($titleNodes && $titleNodes->length > 0) {
            $title = trim($titleNodes->item(0)->textContent);
            if (! empty($title)) {
                $post->title = $title;
            }
        }

        // Extract content from entry-content
        $contentNodes = $xpath->query('//div[contains(@class, "entry-content")] | //div[contains(@class, "post-content")] | //article//div[contains(@class, "content")]');
        if ($contentNodes && $contentNodes->length > 0) {
            $contentNode = $contentNodes->item(0);
            $contentHtml = $this->getInnerHtml($contentNode);

            // Download and replace images
            $contentHtml = $this->processImages($contentHtml, $post->slug);

            // Clean up the HTML
            $contentHtml = $this->cleanHtml($contentHtml);

            if (strlen($contentHtml) > 50) {
                $post->content = $contentHtml;
                $this->info("    Content: " . strlen($contentHtml) . " chars");
            } else {
                $this->warn("    Content too short, keeping existing");
            }
        } else {
            $this->warn("    No content found");
        }

        // Set excerpt if empty
        if (empty($post->excerpt) && ! empty($post->content)) {
            $post->excerpt = Str::limit(strip_tags($post->content), 200);
        }

        if ($isNew) {
            $baseSlug = Str::slug($post->title);
            $slug = $baseSlug;
            $counter = 2;
            while (Post::where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }
            $post->slug = $slug;
        }

        $post->save();
        $this->info("    Saved: {$post->title} ({$post->published_at})");
    }

    private function getInnerHtml(DOMNode $node): string
    {
        $html = '';
        foreach ($node->childNodes as $child) {
            $html .= $node->ownerDocument->saveHTML($child);
        }

        return $html;
    }

    private function processImages(string $html, string $slug): string
    {
        return (string) preg_replace_callback(
            '/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i',
            function (array $matches) use ($slug): string {
                $originalUrl = $matches[0];
                $imgSrc = $matches[1];

                // Skip data URIs, SVGs
                if (str_starts_with($imgSrc, 'data:') || str_ends_with($imgSrc, '.svg')) {
                    return $originalUrl;
                }

                // Make absolute URL
                if (str_starts_with($imgSrc, '/')) {
                    $imgSrc = self::BASE_URL . $imgSrc;
                } elseif (! str_starts_with($imgSrc, 'http')) {
                    return $originalUrl;
                }

                // Skip external images that aren't from the original site
                if (! str_contains($imgSrc, 'nederlandcrowdfunding.nl') && ! str_contains($imgSrc, 'wp-content')) {
                    return $originalUrl;
                }

                $this->line("    Downloading image: " . basename($imgSrc));

                $imageData = @file_get_contents($imgSrc);
                if (! $imageData) {
                    $this->warn("    Failed to download image");
                    return $originalUrl;
                }

                $ext = pathinfo(parse_url($imgSrc, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION) ?: 'jpg';
                $filename = Str::slug($slug) . '-' . Str::random(6) . '.' . $ext;
                $path = 'posts/' . $filename;

                Storage::disk('public')->put($path, $imageData);

                $alt = '';
                if (preg_match('/alt=["\']([^"\']*)["\']/', $originalUrl, $altMatch)) {
                    $alt = $altMatch[1];
                }

                return '<img src="/storage/' . htmlspecialchars($path) . '" alt="' . htmlspecialchars($alt) . '">';
            },
            $html
        );
    }

    private function cleanHtml(string $html): string
    {
        // Remove script/style tags
        $html = (string) preg_replace('#<(script|style|noscript)[^>]*>.*?</\1>#si', '', $html);

        // Remove WordPress specific elements
        $html = (string) preg_replace('#<div[^>]*class="[^"]*sharedaddy[^"]*"[^>]*>.*?</div>#si', '', $html);
        $html = (string) preg_replace('#<div[^>]*class="[^"]*wp-block-buttons[^"]*"[^>]*>.*?</div>#si', '', $html);
        $html = (string) preg_replace('#<div[^>]*class="[^"]*sd-content[^"]*"[^>]*>.*?</div>#si', '', $html);

        // Remove empty paragraphs
        $html = (string) preg_replace('#<p>\s*(&nbsp;|\xC2\xA0)?\s*</p>#', '', $html);

        // Remove excessive whitespace
        $html = (string) preg_replace('#\n{3,}#', "\n\n", $html);

        // Remove class/style/id attributes except on img
        $html = (string) preg_replace('#(<(?!img)[^>]*)\s+(class|style|id|data-[a-z-]+)="[^"]*"#i', '$1', $html);

        // Clean up remaining whitespace
        return trim($html);
    }
}