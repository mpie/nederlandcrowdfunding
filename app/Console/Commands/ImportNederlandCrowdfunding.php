<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\PageStatus;
use App\Models\FileUpload;
use App\Models\Page;
use App\Models\Post;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler;

final class ImportNederlandCrowdfunding extends Command
{
    protected $signature = 'cms:import-nederlandcrowdfunding';

    protected $description = 'Import content from the existing nederlandcrowdfunding.nl website';

    private const string BASE_URL = 'https://nederlandcrowdfunding.nl';

    /** @var array<string, string> */
    private array $pageUrls = [
        'home' => '/',
        'de-vereniging' => '/de-vereniging-2/',
        'leden' => '/leden/',
        'bestuur-directie' => '/bestuur-directie/',
        'contact' => '/contact/',
        'privacybeleid' => '/privacybeleid/',
    ];

    public function handle(): int
    {
        $this->info('Starting import from nederlandcrowdfunding.nl...');
        $this->newLine();

        $this->importPages();
        $this->populateHomePageBlocks();
        $this->populateLedenBlocks();
        $this->populateBestuurBlocks();
        $this->importBlogPosts();
        $this->downloadPdfFiles();

        $this->newLine();
        $this->info('Import completed successfully!');

        return self::SUCCESS;
    }

    private function importPages(): void
    {
        $this->info('Importing pages...');

        // Create "Over ons" parent page
        $overOns = Page::updateOrCreate(
            ['slug' => 'over-ons'],
            [
                'title' => 'Over ons',
                'content' => '<p>Informatie over de branchevereniging Nederland Crowdfunding.</p>',
                'status' => PageStatus::Published,
                'sort_order' => 2,
                'published_at' => now(),
            ]
        );
        $this->line('  Created parent page: Over ons');

        foreach ($this->pageUrls as $slug => $path) {
            $html = $this->fetchHtml(self::BASE_URL . $path);

            if ($html === null) {
                $this->warn("  Skipped {$slug}: could not fetch HTML");
                continue;
            }

            $crawler = new Crawler($html);
            $content = $this->extractMainContent($crawler, $slug);
            $title = $this->extractTitle($crawler);

            $parentId = match ($slug) {
                'de-vereniging', 'leden', 'bestuur-directie' => $overOns->id,
                default => null,
            };

            $sortOrder = match ($slug) {
                'home' => 0,
                'de-vereniging' => 0,
                'leden' => 1,
                'bestuur-directie' => 2,
                'contact' => 3,
                'privacybeleid' => 4,
                default => 10,
            };

            Page::updateOrCreate(
                ['slug' => $slug],
                [
                    'title' => $title,
                    'parent_id' => $parentId,
                    'content' => $content,
                    'status' => PageStatus::Published,
                    'sort_order' => $sortOrder,
                    'published_at' => now(),
                ]
            );

            $this->line("  Imported page: {$title}");
        }

        // Create gedragscode placeholder
        Page::updateOrCreate(
            ['slug' => 'gedragscode'],
            [
                'title' => 'Gedragscode',
                'parent_id' => $overOns->id,
                'content' => '<p>De gedragscode wordt binnenkort gepubliceerd.</p>',
                'status' => PageStatus::Draft,
                'sort_order' => 3,
                'published_at' => null,
            ]
        );
        $this->line('  Created placeholder: Gedragscode');

        $this->info('Pages imported: ' . Page::count());
    }

    private function populateHomePageBlocks(): void
    {
        $this->info('Populating homepage blocks...');

        $homePage = Page::where('slug', 'home')->first();

        if ($homePage === null) {
            $this->warn('  Home page not found, skipping blocks.');
            return;
        }

        $blocks = [
            'hero' => [
                'title' => 'Branchevereniging met impact op de Nederlandse economie',
                'subtitle' => 'Nederland Crowdfunding is de branchevereniging voor beleggings- en financieringsplatformen met een ECSP vergunning actief in Nederland.',
                'cta_text' => 'Laatste nieuws',
                'cta_url' => '/actueel',
                'cta2_text' => 'Neem contact op',
                'cta2_url' => '/contact',
            ],
            'cards' => [
                [
                    'title' => 'Branchevereniging met impact op Nederlandse economie',
                    'icon' => 'fa-solid fa-building-columns',
                    'content' => '<p>Nederland Crowdfunding is een branchevereniging voor beleggings- en financieringsplatformen met een ECSP vergunning actief in Nederland.</p><p>De belangrijkste partijen zijn verenigd in de branchevereniging. De leden bestaan uit:</p><ul><li>Broccoli</li><li>Collin Crowdfund</li><li>Crowdrealestate</li><li>Geldvoorelkaar</li><li>Invesdor</li><li>Lendahand</li><li>Mogelijk Vastgoedfinancieringen</li><li>NL Investeert</li><li>NPEX</li><li>Samen in geld</li><li>Waardevoorjegeld</li><li>Zonhub</li></ul><p>Gezamenlijk financieren deze leden ongeveer voor 1 miljard euro per jaar en verwachten sinds hun oprichting in 2025 voor ongeveer 5 miljard gefinancierd aan voornamelijk het Nederlandse MKB.</p>',
                ],
                [
                    'title' => 'Professionele platforms',
                    'icon' => 'fa-solid fa-shield-halved',
                    'content' => '<p>Nederland Crowdfunding draagt graag bij aan de professionalisering van de sector.</p><p>Sinds november 2023 is het verplicht in Europa om in het bezit te zijn van een ECSP vergunning als je actief wil zijn in het digitaal aanbod tot bemiddelen van investeringen aan 1 of meerdere investeerders.</p><p>De meest gangbare vorm is een website, maar dit kan ook een emaillijst zijn of gebruikmakend van social media.</p><p>Op de website van de <a href="https://www.afm.nl/nl-nl/sector/registers/vergunningenregisters/crowdfundingplatformen">AFM</a> is de laatste lijst te vinden van Nederlandse platformen en buitenlandse platformen die zijn aangemeld voor de Nederlandse markt.</p>',
                ],
                [
                    'title' => 'Versterken ruimte financiering MKB',
                    'icon' => 'fa-solid fa-chart-line',
                    'content' => '<p>Nederland Crowdfunding wil meer ruimte voor financiering van MKB ondernemers. Daarom werken wij aan:</p><ol><li><strong>Behoud van aandacht voor MKB-financiering</strong><br>De inzet van het kabinet via het Nationaal Convenant en de Stichting MKB Financiering heeft geleid tot waardevolle initiatieven zoals de financieringsgids en het keurmerk Erkend MKB Financier. Deze inspanningen verdienen voortzetting.</li><li><strong>Gelijk speelveld voor alle financiers</strong><br>Beleidsvorming is nog te vaak gebaseerd op traditionele financieringsstructuren. Nieuwe vormen van financiering, zoals crowdfunding, moeten volwaardig worden meegenomen in wet- en regelgeving.</li><li><strong>Toegang tot de BMKB-regeling voor crowdfundplatforms</strong><br>Professionele crowdfunders met een ECSPR-vergunning zouden toegang moeten krijgen tot de Borgstelling MKB-kredieten (BMKB). Deze regeling sluit momenteel onvoldoende aan bij de financieringspraktijk van vandaag.</li></ol>',
                ],
            ],
            'about' => [
                'title' => 'Over de branchevereniging',
                'content' => '<p>Nederland Crowdfunding is de branchevereniging van crowdfundingplatforms voor bedrijfsfinanciering. De branchevereniging is in 2014 opgericht en heeft ten doel de ontwikkeling van crowdfunding als instrument voor aanvullend financieren en investeren, verder te versterken en te verduurzamen.</p><p>De branchevereniging is actief op het gebied van kennisdeling en promotie van crowdfunding, door aanwezigheid op evenementen en in het publieke debat. Tot slot is de branchevereniging actief op het gebied van stimulering van crowdfunding en gaat zij regelmatig in gesprek met de AFM als financiële toezichthouder en overheden om samen met hen te werken aan een gunstig crowdfunding klimaat.</p>',
                'link_text' => 'Lees meer over de vereniging',
                'link_url' => '/over-ons/de-vereniging',
            ],
            'stats' => [
                'title' => 'Nederland Crowdfunding',
                'subtitle' => 'versterkt het klimaat voor MKB-financiering',
                'items' => [
                    ['value' => '12', 'label' => 'Leden'],
                    ['value' => '€1 mrd', 'label' => 'Per jaar'],
                    ['value' => '2014', 'label' => 'Opgericht'],
                    ['value' => '€5 mrd', 'label' => 'Totaal gefinancierd'],
                ],
            ],
            'faq' => [
                'title' => 'Veelgestelde vragen',
                'subtitle' => 'Crowdfunding is meer dan financiering',
                'items' => [
                    [
                        'question' => 'Wat is crowdfunding?',
                        'answer' => '<p>Crowdfunding is een vorm van bedrijfsfinanciering. Een grote groep investeerders maakt het voor MKB ondernemers mogelijk nieuwe activiteiten te realiseren. Het crowdfundingplatform verbindt ondernemingen die op zoek zijn naar financiering, met mensen en organisaties die willen investeren.</p>',
                    ],
                    [
                        'question' => 'Wat is het verschil tussen sparen via de bank en crowdfunding?',
                        'answer' => '<p>Er zijn vier grote verschillen</p><p>a) Uw invloed. Bij crowdfunding investeert u in een onderneming van uw keuze. U weet dus precies waar uw investering heen gaat en kunt kiezen voor de onderneming die het best bij u past: bijvoorbeeld een duurzame onderneming, een bedrijf bij u in de buurt of een bedrijf waarvan u het hoogste rendement verwacht. U kunt zelf beslissen.</p><p>b) De looptijd. Bij crowdfunding staat uw geld \'vast\' gedurende de vooraf bepaalde looptijd van het crowdfundingproject: u kunt uw investering niet terugroepen als u ineens geld nodig hebt. Bij spaarrekeningen van de bank kan dat vaak wel – al zijn er ook spaarrekeningen waarbij uw geld voor een bepaalde periode of tot een bepaald bedrag \'vast\' staat.</p><p>c) De opbrengst. Bij een spaarrekening bestaat uw opbrengst uit de spaarrente. Die is gekoppeld aan het rentetarief van (meestal) de Europese Centrale Bank, die momenteel historisch laag is. Om hun crowdfundingproject aantrekkelijk te maken voor investeerders, bieden crowdfundende ondernemers u meestal een opbrengst die hoger is dan die spaarrente: een hoger rentepercentage, een aantrekkelijke winstdelingsregeling of aandelen in het bedrijf etc. Let u wel goed op: het is niet zeker dat de voorgespiegelde opbrengst ook wordt gehaald bij uw investering.</p><p>d) De risico\'s. Bij crowdfunding investeert u direct in een onderneming. Daar zijn risico\'s aan verbonden. Het is niet zeker dat de ondernemer de voorgespiegelde opbrengst realiseert. Ook is er geen gegarandeerde teruggave van het geld dat u heeft ingelegd. Bij sparen via de bank loopt u nauwelijks risico dat u uw spaarinleg kwijtraakt, mede dankzij het <a href="http://www.dnb.nl/over-dnb/de-consument-en-dnb/de-consument-en-toezicht/depositogarantiestelsel/">depositogarantiestelsel</a> van De Nederlandsche Bank.</p><p>Belangrijk: investeer alleen geld dat u niet op korte termijn nodig heeft en dat u eventueel kunt missen. Spreid uw inleg over meerdere crowdfundingprojecten. Staar u niet blind op de voorgespiegelde opbrengst van uw investering, maar verdiep u goed in de risico\'s.</p>',
                    ],
                    [
                        'question' => 'Wat doet een crowdfundingplatform?',
                        'answer' => '<p>Crowdfundingplatforms slaan de brug tussen (potentiële) investeerders en ondernemers die kapitaal zoeken, in de vorm van een concreet investeringsaanbod waarop u online kunt intekenen. Verder controleren ze de identiteit van de ondernemer en regelen ze het betalingsverkeer. Platforms screenen alle projecten die ze aanbieden en verbinden er een (al dan niet door een onafhankelijke partij opgestelde) risico-indicatie aan.</p>',
                    ],
                    [
                        'question' => 'Is er wettelijk toezicht op de crowdfundingplatforms?',
                        'answer' => '<p>De financiële toezichthouders AFM (Autoriteit Financiële Markten) en DNB (De Nederlandsche Bank) houden toezicht op de crowdfundingplatforms. Een crowdfund platform dat actief is in de Nederlandse markt moet hiervoor over <a href="https://www.afm.nl/nl-nl/consumenten/themas/zelf-beleggen/crowdfunding">een vergunning</a> beschikken.</p>',
                    ],
                    [
                        'question' => 'Waar kan ik terecht met een klacht over mijn crowdfund platform?',
                        'answer' => '<p>Als u investeerder bent en u heeft een klacht over het crowdfund platform waar u investeert, dan kunt u eerst bij het platform zelf terecht. Mocht uw klacht niet naar tevredenheid zijn behandeld, dan is er mogelijkheid uw klacht te melden bij het <a href="https://www.kifid.nl/">Kifid</a>. Al onze leden zijn aangesloten bij dit onafhankelijk klachtenloket voor mensen met een financiële klacht.</p>',
                    ],
                ],
            ],
        ];

        $homePage->update(['blocks' => $blocks]);
        $this->info('  Homepage blocks populated with content from old site.');
    }

    private function populateLedenBlocks(): void
    {
        $this->info('Populating Leden page blocks...');

        $ledenPage = Page::where('slug', 'leden')->first();

        if ($ledenPage === null) {
            $this->warn('  Leden page not found, skipping.');
            return;
        }

        $blocks = [
            'members' => [
                'intro' => 'Met diverse leden is Nederland Crowdfunding een belangrijke gesprekspartner voor ministeries, overheden en andere stakeholders. De leden zijn verspreid over heel Nederland te vinden.',
                'items' => [
                    [
                        'name' => 'Collin Crowdfund',
                        'url' => 'https://www.collincrowdfund.nl',
                        'description' => '<p>Via haar online platform slaat Collin een brug tussen investeerders die op zoek zijn naar meer rendement en ambitieuze MKB-bedrijven met een financieringsbehoefte. Bedrijven met een financieringsbehoefte van € 50.000 tot € 2.500.000 kunnen bij Collin terecht.</p>',
                    ],
                    [
                        'name' => 'Samen in Geld',
                        'url' => 'https://www.sameningeld.nl',
                        'description' => '<p>Samen in Geld biedt hypothecaire zekerheid waarbij de investeerder het eerste recht op teruggave heeft wanneer er financiële onzekerheid ontstaat. De geldlener dient bij Samen in Geld zelf minimaal 20% in te leggen.</p>',
                    ],
                    [
                        'name' => 'Waardevoorjegeld',
                        'url' => 'https://www.waardevoorjegeld.nl',
                        'description' => '<p>Waardevoorjegeld is een platform voor financieringen aan het MKB bedrijf. Hierbij koppelen zij een ondernemer aan een groep investeerders, waarbij persoonlijke benadering en transparantie belangrijke uitgangspunten zijn.</p>',
                    ],
                    [
                        'name' => 'Mogelijk Vastgoedfinancieringen',
                        'url' => 'https://www.mogelijk.nl',
                        'description' => '<p>Als Mogelijk financieren zij sinds 2016 zakelijk vastgoed. Onder andere door het aanbieden van financieringsopties, 1 op 1 aan private investeerders. Investeren met een aantrekkelijk rendement, direct gedekt door zakelijk vastgoed.</p>',
                    ],
                    [
                        'name' => 'NL Investeert',
                        'url' => 'https://www.nlinvesteert.nl',
                        'description' => '<p>NLInvesteert is een platform dat zich richt op het bieden van bereikbare financiering voor het midden- en kleinbedrijf (MKB) in Nederland. Het platform combineert verschillende kapitaalsoorten tot de best passende oplossing voor ondernemers.</p>',
                    ],
                    [
                        'name' => 'Invesdor',
                        'url' => 'https://www.invesdor.com',
                        'description' => '<p>Invesdor is een toonaangevend investerings- en crowdfundingplatform dat is ontstaan uit een fusie met het Nederlandse platform Oneplanetcrowd in 2023. Het platform biedt investeerders de mogelijkheid om te investeren in duurzame initiatieven.</p>',
                    ],
                    [
                        'name' => 'Geldvoorelkaar',
                        'url' => 'https://www.geldvoorelkaar.nl',
                        'description' => '<p>Het eerste crowdfundingplatform van Nederland, opgericht in 2011. Het platform verbindt investeerders en ondernemers, waardoor financiering en rendement toegankelijk worden voor iedereen. Meer dan 2.537 succesvolle projecten gefinancierd.</p>',
                    ],
                    [
                        'name' => 'NPEX',
                        'url' => 'https://www.npex.nl',
                        'description' => '<p>NPEX, opgericht in 2008, is een effectenbeurs die ondernemers en beleggers samenbrengt voor financiering, impact en rendement. Het platform beschikt over een MTF-vergunning van de AFM.</p>',
                    ],
                    [
                        'name' => 'Zonhub',
                        'url' => 'https://www.zonhub.nl',
                        'description' => '<p>ZonHub faciliteert de energietransitie door investeerders en duurzame energieprojecten samen te brengen. Investeerders kunnen direct bijdragen aan zon/wind en batterijprojecten in Nederland en daarbuiten.</p>',
                    ],
                    [
                        'name' => 'Crowdrealestate',
                        'url' => 'https://www.crowdrealestate.com',
                        'description' => '<p>Crowdrealestate is een online investerings- en financieringsplatform dat zich richt op het aanbieden van hoogwaardige en zorgvuldig geselecteerde vastgoedprojecten in Nederland, België en Duitsland.</p>',
                    ],
                    [
                        'name' => 'Lendahand',
                        'url' => 'https://www.lendahand.com',
                        'description' => '<p>Lendahand is een impactinvesteringsplatform dat wereldwijde kansenongelijkheid verkleint door betaalbare leningen te verstrekken aan ondernemers in opkomende landen.</p>',
                    ],
                    [
                        'name' => 'Broccoli',
                        'url' => 'https://www.joinbroccoli.com',
                        'description' => '<p>Bij Broccoli word je aandeelhouder in duurzame bedrijven door te investeren via het platform, waar prestatie en doel samenkomen. Met bijna 15.000 investeerders bouwen zij samen aan een duurzamere toekomst.</p>',
                    ],
                ],
            ],
        ];

        $ledenPage->update([
            'blocks' => $blocks,
            'content' => '',
        ]);
        $this->info('  Leden page blocks populated.');
    }

    private function populateBestuurBlocks(): void
    {
        $this->info('Populating Bestuur page blocks...');

        $bestuurPage = Page::where('slug', 'bestuur-directie')->first();

        if ($bestuurPage === null) {
            $this->warn('  Bestuur page not found, skipping.');
            return;
        }

        $blocks = [
            'team' => [
                'intro' => 'Het bestuur van branchevereniging Nederland Crowdfunding wordt gevormd door een afvaardiging van de aangesloten platforms. De directie wordt gevoerd door Robbert Loos.',
                'items' => [
                    [
                        'name' => 'Folkert Eggink',
                        'role' => 'Voorzitter',
                        'company' => 'Mogelijk Vastgoedfinancieringen',
                        'bio' => '<p>Folkert Eggink is naast voorzitter werkzaam als algemeen directeur van Mogelijk Vastgoedfinancieringen. Hiervoor werkzaam bij Funding Circle (UK crowdfunder), Nationale Nederlanden en ING. Sinds februari 2025 is hij bestuurslid van de branchevereniging.</p>',
                    ],
                    [
                        'name' => 'Ruilof van Putten',
                        'role' => 'Bestuurslid',
                        'company' => 'NL Investeert',
                        'bio' => '<p>Ruilof van Putten is directeur en mede-oprichter van het platform NL Investeert. Sinds februari 2025 is hij bestuurslid van de branchevereniging.</p>',
                    ],
                    [
                        'name' => 'Johan van Buuren',
                        'role' => 'Bestuurslid',
                        'company' => 'Waarde voor je geld',
                        'bio' => '<p>Johan is naast zijn rol als bestuurslid actief als oprichter en algemeen directeur van Waarde voor je geld. Sinds februari 2025 is hij bestuurslid van de branchevereniging.</p>',
                    ],
                    [
                        'name' => 'Ellen Hensbergen',
                        'role' => 'Bestuurslid',
                        'company' => 'Invesdor',
                        'bio' => '<p>Ellen is sinds naast bestuurslid de algemeen directeur van Invesdor in Nederland. Hiervoor was ze werkzaam als algemeen directeur van Munt Hypotheken. Sinds februari 2025 is zij bestuurslid van de branchevereniging.</p>',
                    ],
                    [
                        'name' => 'Robbert Loos',
                        'role' => 'Directeur',
                        'company' => 'Nederland Crowdfunding',
                        'bio' => '<p>Robbert Loos is directeur van branchevereniging Nederland Crowdfunding en verantwoordelijk voor de dagelijkse leiding van de organisatie.</p>',
                    ],
                ],
            ],
        ];

        $bestuurPage->update([
            'blocks' => $blocks,
            'content' => '',
        ]);
        $this->info('  Bestuur page blocks populated.');
    }

    private function importBlogPosts(): void
    {
        $this->info('Importing blog posts...');

        $totalImported = 0;

        for ($pageNum = 1; $pageNum <= 4; $pageNum++) {
            $url = $pageNum === 1
                ? self::BASE_URL . '/actueel/'
                : self::BASE_URL . '/actueel/page/' . $pageNum . '/';

            $html = $this->fetchHtml($url);

            if ($html === null) {
                $this->warn("  Could not fetch blog page {$pageNum}");
                continue;
            }

            $crawler = new Crawler($html);
            $articles = $crawler->filter('article, .post, .entry-content h2');

            if ($articles->count() === 0) {
                // Try to extract posts from h2 elements in the main content
                $articles = $crawler->filter('.entry-content h2, .post-content h2, #content h2');
            }

            // Extract individual blog post entries from the listing page
            $postData = $this->extractBlogPostsFromListing($crawler);

            foreach ($postData as $data) {
                $slug = Str::slug($data['title']);

                if (empty($slug) || mb_strlen($slug) < 3) {
                    continue;
                }

                // Truncate slug if too long
                $slug = Str::limit($slug, 200, '');

                Post::updateOrCreate(
                    ['slug' => $slug],
                    [
                        'title' => $data['title'],
                        'excerpt' => Str::limit(strip_tags($data['content']), 300),
                        'content' => $data['content'],
                        'status' => PageStatus::Published,
                        'published_at' => $data['date'] ?? now(),
                    ]
                );

                $totalImported++;
                $this->line("  Imported post: " . Str::limit($data['title'], 60));
            }
        }

        $this->info("Blog posts imported: {$totalImported}");
    }

    private function downloadPdfFiles(): void
    {
        $this->info('Downloading PDF files...');

        Storage::disk('public')->makeDirectory('uploads');

        // Search for PDF links across all imported pages and posts
        $pdfLinks = collect();

        Page::all()->each(function (Page $page) use ($pdfLinks): void {
            if ($page->content === null) {
                return;
            }
            $crawler = new Crawler($page->content);
            $crawler->filter('a[href$=".pdf"]')->each(function (Crawler $link) use ($pdfLinks): void {
                $href = $link->attr('href');
                if ($href !== null) {
                    $pdfLinks->push($href);
                }
            });
        });

        Post::all()->each(function (Post $post) use ($pdfLinks): void {
            $crawler = new Crawler($post->content);
            $crawler->filter('a[href$=".pdf"]')->each(function (Crawler $link) use ($pdfLinks): void {
                $href = $link->attr('href');
                if ($href !== null) {
                    $pdfLinks->push($href);
                }
            });
        });

        $unique = $pdfLinks->unique();

        foreach ($unique as $pdfUrl) {
            try {
                $fullUrl = str_starts_with($pdfUrl, 'http')
                    ? $pdfUrl
                    : self::BASE_URL . '/' . ltrim($pdfUrl, '/');

                $response = Http::timeout(30)->get($fullUrl);

                if (! $response->successful()) {
                    $this->warn("  Failed to download: {$pdfUrl}");
                    continue;
                }

                $filename = basename(parse_url($pdfUrl, PHP_URL_PATH) ?? 'document.pdf');
                $path = 'uploads/' . $filename;

                Storage::disk('public')->put($path, $response->body());

                FileUpload::updateOrCreate(
                    ['path' => $path],
                    [
                        'disk' => 'public',
                        'original_name' => $filename,
                        'mime_type' => 'application/pdf',
                        'size' => strlen($response->body()),
                    ]
                );

                $this->line("  Downloaded: {$filename}");
            } catch (\Throwable $e) {
                $this->warn("  Error downloading {$pdfUrl}: {$e->getMessage()}");
            }
        }

        $this->info('Files downloaded: ' . FileUpload::count());
    }

    private function fetchHtml(string $url): ?string
    {
        try {
            $response = Http::timeout(30)
                ->withUserAgent('Mozilla/5.0 (compatible; NLCFImporter/1.0)')
                ->get($url);

            if ($response->successful()) {
                return $response->body();
            }
        } catch (\Throwable $e) {
            $this->warn("  HTTP error: {$e->getMessage()}");
        }

        return null;
    }

    private function extractTitle(Crawler $crawler): string
    {
        try {
            $h1 = $crawler->filter('h1.entry-title, h1');
            if ($h1->count() > 0) {
                return trim($h1->first()->text());
            }
        } catch (\Throwable) {
            // ignore
        }

        try {
            $title = $crawler->filter('title');
            if ($title->count() > 0) {
                $text = $title->first()->text();
                // Remove site name suffix
                return trim(explode(' - ', $text)[0] ?? $text);
            }
        } catch (\Throwable) {
            // ignore
        }

        return 'Untitled';
    }

    private function extractMainContent(Crawler $crawler, string $slug): string
    {
        $selectors = [
            '.entry-content',
            '.post-content',
            '#content .content-area',
            'article',
            '.page-content',
            'main',
        ];

        foreach ($selectors as $selector) {
            try {
                $content = $crawler->filter($selector);
                if ($content->count() > 0) {
                    $html = $content->first()->html();
                    return $this->cleanHtml($html);
                }
            } catch (\Throwable) {
                continue;
            }
        }

        return '<p>Content niet beschikbaar.</p>';
    }

    private function cleanHtml(string $html): string
    {
        // Remove script and style tags
        $html = preg_replace('/<script\b[^>]*>.*?<\/script>/si', '', $html) ?? $html;
        $html = preg_replace('/<style\b[^>]*>.*?<\/style>/si', '', $html) ?? $html;

        // Remove inline styles
        $html = preg_replace('/\s*style="[^"]*"/i', '', $html) ?? $html;

        // Remove class attributes
        $html = preg_replace('/\s*class="[^"]*"/i', '', $html) ?? $html;

        // Remove data attributes
        $html = preg_replace('/\s*data-[a-z-]+="[^"]*"/i', '', $html) ?? $html;

        // Rewrite internal links
        $html = str_replace(
            ['href="' . self::BASE_URL . '/de-vereniging-2/', 'href="/de-vereniging-2/'],
            ['href="/over-ons/de-vereniging', 'href="/over-ons/de-vereiniging'],
            $html
        );
        $html = str_replace(
            ['href="' . self::BASE_URL . '/leden/', 'href="/leden/'],
            ['href="/over-ons/leden', 'href="/over-ons/leden'],
            $html
        );
        $html = str_replace(
            ['href="' . self::BASE_URL . '/bestuur-directie/', 'href="/bestuur-directie/'],
            ['href="/over-ons/bestuur-directie', 'href="/over-ons/bestuur-directie'],
            $html
        );
        $html = str_replace(
            ['href="' . self::BASE_URL . '/contact/', 'href="/contact/'],
            ['href="/contact', 'href="/contact'],
            $html
        );
        $html = str_replace(
            ['href="' . self::BASE_URL . '/actueel/', 'href="/actueel/'],
            ['href="/actueel', 'href="/actueel'],
            $html
        );
        $html = str_replace(
            ['href="' . self::BASE_URL . '/privacybeleid/', 'href="/privacybeleid/'],
            ['href="/privacybeleid', 'href="/privacybeleid'],
            $html
        );

        // Clean up excess whitespace
        $html = preg_replace('/\n\s*\n/', "\n", $html) ?? $html;

        return trim($html);
    }

    /** @return list<array{title: string, content: string, date: ?\DateTimeInterface}> */
    private function extractBlogPostsFromListing(Crawler $crawler): array
    {
        $posts = [];

        // Try extracting from article elements first
        $articles = $crawler->filter('article');

        if ($articles->count() > 0) {
            $articles->each(function (Crawler $article) use (&$posts): void {
                $title = '';
                $content = '';
                $date = null;

                try {
                    $h2 = $article->filter('h2');
                    if ($h2->count() > 0) {
                        $title = trim($h2->first()->text());
                    }
                } catch (\Throwable) {
                    // ignore
                }

                if (empty($title)) {
                    return;
                }

                try {
                    $content = $this->cleanHtml($article->html());
                } catch (\Throwable) {
                    $content = '<p>' . $title . '</p>';
                }

                // Try to extract date
                try {
                    $timeEl = $article->filter('time, .date, .entry-date');
                    if ($timeEl->count() > 0) {
                        $dateStr = $timeEl->first()->attr('datetime') ?? $timeEl->first()->text();
                        $date = \Carbon\Carbon::parse($dateStr);
                    }
                } catch (\Throwable) {
                    // ignore
                }

                $posts[] = [
                    'title' => $title,
                    'content' => $content,
                    'date' => $date,
                ];
            });

            return $posts;
        }

        // Fallback: extract from h2 headings in main content area
        $contentArea = $crawler->filter('.entry-content, #content, main');

        if ($contentArea->count() === 0) {
            return $posts;
        }

        $h2Elements = $contentArea->filter('h2');

        $h2Elements->each(function (Crawler $h2) use (&$posts): void {
            $title = trim($h2->text());

            if (empty($title) || mb_strlen($title) < 5) {
                return;
            }

            // Skip navigation/pagination headings
            if (in_array($title, ['Berichten paginering', 'Recente berichten', 'Contact', 'Informatie'], true)) {
                return;
            }

            // Collect content: all siblings until next h2
            $contentParts = ['<h2>' . htmlspecialchars($title) . '</h2>'];
            $sibling = $h2;

            try {
                $parentNode = $h2->getNode(0)?->parentNode;
                if ($parentNode === null) {
                    return;
                }

                $nextSibling = $h2->getNode(0)?->nextSibling;
                while ($nextSibling !== null) {
                    if ($nextSibling->nodeName === 'h2') {
                        break;
                    }
                    if ($nextSibling instanceof \DOMElement) {
                        $doc = new \DOMDocument();
                        $imported = $doc->importNode($nextSibling, true);
                        $doc->appendChild($imported);
                        $contentParts[] = $doc->saveHTML();
                    }
                    $nextSibling = $nextSibling->nextSibling;
                }
            } catch (\Throwable) {
                // ignore
            }

            $content = implode("\n", $contentParts);

            // Try extracting date from nearby text
            $date = null;
            $datePattern = '/(\d{2}-\d{2}-\d{4})/';
            if (preg_match($datePattern, $content, $matches)) {
                try {
                    $date = \Carbon\Carbon::createFromFormat('d-m-Y', $matches[1]);
                } catch (\Throwable) {
                    // ignore
                }
            }

            $posts[] = [
                'title' => $title,
                'content' => $this->cleanHtml($content),
                'date' => $date,
            ];
        });

        return $posts;
    }
}