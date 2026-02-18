<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\MenuLocation;
use App\Enums\PageStatus;
use App\Models\FileUpload;
use App\Models\MenuItem;
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
        $this->downloadMemberLogos();
        $this->populateBestuurBlocks();
        $this->importBlogPosts();
        $this->downloadPdfFiles();
        $this->seedMenuItems();

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

    /** @var array<string, string> */
    private const array MEMBER_LOGO_URLS = [
        'Collin Crowdfund' => 'https://nederlandcrowdfunding.nl/wp-content/uploads/2018/11/Collin-Crowdfund.png',
        'Samen in Geld' => 'https://nederlandcrowdfunding.nl/wp-content/uploads/2023/11/sameningeld-logo-300x236.png',
        'Waardevoorjegeld' => 'https://nederlandcrowdfunding.nl/wp-content/uploads/2020/08/waarde.png',
        'Mogelijk Vastgoedfinancieringen' => 'https://nederlandcrowdfunding.nl/wp-content/uploads/2025/06/Mogelijk_Logo_RGBPayoff.jpg',
        'NL Investeert' => 'https://nederlandcrowdfunding.nl/wp-content/uploads/2025/02/NL-Investeert.jpg',
        'Invesdor' => 'https://nederlandcrowdfunding.nl/wp-content/uploads/2025/02/Invesdor.jpg',
        'Geldvoorelkaar' => 'https://nederlandcrowdfunding.nl/wp-content/uploads/2025/02/Geld-voor-Elkaar.jpg',
        'NPEX' => 'https://nederlandcrowdfunding.nl/wp-content/uploads/2025/02/NPEX.jpg',
        'Zonhub' => 'https://nederlandcrowdfunding.nl/wp-content/uploads/2025/10/zonhub.png',
        'Crowdrealestate' => 'https://nederlandcrowdfunding.nl/wp-content/uploads/2025/06/Crowdrealestate-logo-lichte-achtergrond.png',
        'Lendahand' => 'https://nederlandcrowdfunding.nl/wp-content/uploads/2026/01/Logo-Lendahand.png',
        'Broccoli' => 'https://nederlandcrowdfunding.nl/wp-content/uploads/2026/01/broccoli-scaled.png',
    ];

    private function downloadMemberLogos(): void
    {
        $this->info('Downloading member logos...');

        Storage::disk('public')->makeDirectory('leden-logos');

        $ledenPage = Page::where('slug', 'leden')->first();

        if ($ledenPage === null) {
            $this->warn('  Leden page not found, skipping logos.');
            return;
        }

        $blocks = $ledenPage->blocks ?? [];
        $items = $blocks['members']['items'] ?? [];
        $updated = false;

        foreach ($items as $i => $member) {
            $name = $member['name'];
            $logoUrl = self::MEMBER_LOGO_URLS[$name] ?? null;

            if ($logoUrl === null) {
                $this->warn("  No logo URL mapped for: {$name}");
                continue;
            }

            if (! empty($member['logo']) && Storage::disk('public')->exists($member['logo'])) {
                $this->line("  Already exists: {$name}");
                continue;
            }

            try {
                $response = Http::timeout(30)
                    ->withUserAgent('Mozilla/5.0 (compatible; NLCFImporter/1.0)')
                    ->get($logoUrl);

                if (! $response->successful()) {
                    $this->warn("  Failed to download logo for {$name}");
                    continue;
                }

                $ext = pathinfo(parse_url($logoUrl, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION) ?: 'png';
                $filename = Str::slug($name) . '.' . $ext;
                $path = 'leden-logos/' . $filename;

                Storage::disk('public')->put($path, $response->body());
                $items[$i]['logo'] = $path;
                $updated = true;

                $this->line("  Downloaded: {$name} -> {$path}");
            } catch (\Throwable $e) {
                $this->warn("  Error downloading logo for {$name}: {$e->getMessage()}");
            }
        }

        if ($updated) {
            $blocks['members']['items'] = $items;
            $ledenPage->update(['blocks' => $blocks]);
            $this->info('  Leden page blocks updated with logo paths.');
        }
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

    /** @var list<string> Blog post URL paths (date/slug format) */
    private const array BLOG_POST_URLS = [
        '2026/01/15/2026-start-met-twee-nieuwe-leden-voor-de-branchevereniging',
        '2025/11/10/crowdfinance-ruim-23mld-aan-spaargeld-actief-in-de-nederlandse-economie',
        '2025/10/28/eerste-week-van-de-crowdfinance-van-10-tot-met-14-november-2025',
        '2025/10/28/nieuw-lid-voor-de-branchevereniging',
        '2025/09/16/stappenplan-afm-nu-ook-gepubliceerd',
        '2025/07/17/branchevereniging-nederland-crowdfunding-roept-op-tot-structurele-beleidsaandacht-in-verkiezingsprogrammas',
        '2025/06/26/nieuwe-leden-voor-de-branchevereniging',
        '2025/05/06/fd-over-crowdfunding',
        '2025/02/07/zoeken-op-naam-in-het-kadaster-weer-mogelijk',
        '2025/02/04/vijf-nieuwe-leden',
        '2024/04/05/crowdfundplatforms-verzoeken-toegang-tot-regelingen-van-essentieel-belang-voor-een-financieel-gezond-mkb',
        '2023/11/13/afm-vergunning',
        '2021/09/13/activeer-uw-spaargeld-sept21',
        '2021/06/24/activeer-uw-spaargeld-jun21',
        '2021/05/04/activeer-uw-spaargeld-apr21',
        '2021/03/29/crowdfundplatforms-bieden-investeerders-transparante-informatie',
        '2021/03/17/opinie-activeer-uw-spaargeld',
        '2021/03/15/activeer-uw-spaargeld-mrt21',
        '2021/02/10/activeer-uw-spaargeld-feb21',
        '2021/01/18/activeer-uw-spaargeld-jan21',
        '2020/11/17/activeer-uw-spaargeld',
        '2020/10/17/betrek-investerende-particulier-bij-herstel-nederlandse-mkb',
        '2020/10/15/activeer-uw-spaargeld-okt20',
        '2020/10/07/eu-parlement-stemt-in-met-crowdfund-regelgeving',
        '2020/07/20/ledenmutaties-matchingcapital-en-waardevoorjegeld-nieuwe-leden',
        '2020/01/28/financieringsmonitor-bevestigt-belangrijke-rol-crowdfunding-in-nederlands-financieringslandschap',
        '2019/10/07/crowdfundingscan-helpt-ondernemers-financiering-te-vinden',
        '2019/05/09/leden-nederland-crowdfunding-presenteren-reele-netto-rendementscijfers',
        '2019/04/02/robbert-loos-directeur-nederland-crowdfunding',
        '2019/02/28/crowdfundingplatformen-verbeteren-hun-informatieverstrekking',
        '2019/01/28/complementaire-financiering-wint-terrein',
        '2019/01/01/crowdfunding-groeit-hard-door-in-2018',
        '2018/11/28/europees-parlement-stemt-over-crowdfunding-regelgeving',
    ];

    private const array BLOG_ALLOWED_TAGS = [
        'p', 'br', 'strong', 'b', 'em', 'i', 'u', 'a',
        'ul', 'ol', 'li',
        'h2', 'h3', 'h4', 'h5', 'h6',
        'blockquote', 'pre', 'code',
        'table', 'thead', 'tbody', 'tr', 'th', 'td',
        'img', 'figure', 'figcaption',
        'hr', 'sup', 'sub', 'span',
    ];

    private function importBlogPosts(): void
    {
        $this->info('Importing blog posts...');

        Storage::disk('public')->makeDirectory('posts');
        $totalImported = 0;

        foreach (self::BLOG_POST_URLS as $urlPath) {
            $url = self::BASE_URL . '/' . $urlPath . '/';
            $this->line("  Scraping: {$url}");

            $html = $this->fetchHtml($url);

            if ($html === null) {
                $this->warn("  Failed to fetch: {$url}");
                continue;
            }

            $doc = new \DOMDocument();
            @$doc->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_NOERROR);
            $xpath = new \DOMXPath($doc);

            // Extract date from URL path
            $publishedAt = now();
            if (preg_match('#^(\d{4})/(\d{2})/(\d{2})/#', $urlPath, $m)) {
                $publishedAt = "{$m[1]}-{$m[2]}-{$m[3]} 12:00:00";
            }

            // Extract title from h1
            $title = 'Untitled';
            $titleNodes = $xpath->query('//h1[contains(@class, "entry-title")] | //header//h1 | //h1');
            if ($titleNodes && $titleNodes->length > 0) {
                $t = trim($titleNodes->item(0)->textContent);
                if (! empty($t)) {
                    $title = $t;
                }
            }

            // Derive slug from URL path
            $slugFromUrl = Str::slug(basename($urlPath));
            if (empty($slugFromUrl) || mb_strlen($slugFromUrl) < 3) {
                $slugFromUrl = Str::limit(Str::slug($title), 200, '');
            }

            // Extract content from entry-content
            $contentHtml = '';
            $contentNodes = $xpath->query('//div[contains(@class, "entry-content")] | //div[contains(@class, "post-content")]');
            if ($contentNodes && $contentNodes->length > 0) {
                $contentNode = $contentNodes->item(0);
                $innerHtml = '';
                foreach ($contentNode->childNodes as $child) {
                    $innerHtml .= $contentNode->ownerDocument->saveHTML($child);
                }

                $contentHtml = $this->downloadPostImages($innerHtml, $slugFromUrl);
                $contentHtml = $this->cleanBlogHtml($contentHtml);
            }

            if (mb_strlen($contentHtml) < 50) {
                $this->warn("    Content too short, skipping");
                continue;
            }

            $excerpt = $this->cleanBlogExcerpt(Str::limit(strip_tags($contentHtml), 300), $title);

            Post::updateOrCreate(
                ['slug' => $slugFromUrl],
                [
                    'title' => $title,
                    'excerpt' => $excerpt,
                    'content' => $contentHtml,
                    'status' => PageStatus::Published,
                    'published_at' => $publishedAt,
                ],
            );

            $totalImported++;
            $this->info("    Saved: {$title} ({$publishedAt})");
        }

        $this->info("Blog posts imported: {$totalImported}");
    }

    private function downloadPostImages(string $html, string $slug): string
    {
        return (string) preg_replace_callback(
            '/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i',
            function (array $matches) use ($slug): string {
                $originalTag = $matches[0];
                $imgSrc = $matches[1];

                if (str_starts_with($imgSrc, 'data:') || str_ends_with($imgSrc, '.svg')) {
                    return $originalTag;
                }

                if (str_starts_with($imgSrc, '/')) {
                    $imgSrc = self::BASE_URL . $imgSrc;
                } elseif (! str_starts_with($imgSrc, 'http')) {
                    return $originalTag;
                }

                if (! str_contains($imgSrc, 'nederlandcrowdfunding.nl') && ! str_contains($imgSrc, 'wp-content')) {
                    return $originalTag;
                }

                $this->line("    Downloading image: " . basename($imgSrc));

                try {
                    $response = Http::timeout(30)->get($imgSrc);

                    if (! $response->successful()) {
                        $this->warn("    Failed to download image");
                        return $originalTag;
                    }

                    $ext = pathinfo(parse_url($imgSrc, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION) ?: 'jpg';
                    $filename = Str::slug($slug) . '-' . Str::random(6) . '.' . $ext;
                    $path = 'posts/' . $filename;

                    Storage::disk('public')->put($path, $response->body());
                    $newUrl = Storage::disk('public')->url($path);

                    $alt = '';
                    if (preg_match('/alt=["\']([^"\']*)["\']/', $originalTag, $altMatch)) {
                        $alt = $altMatch[1];
                    }

                    return '<img src="' . htmlspecialchars($newUrl) . '" alt="' . htmlspecialchars($alt) . '">';
                } catch (\Throwable $e) {
                    $this->warn("    Image download error: {$e->getMessage()}");
                    return $originalTag;
                }
            },
            $html,
        );
    }

    private function cleanBlogHtml(string $html): string
    {
        // Remove script/style/noscript
        $html = (string) preg_replace('#<(script|style|noscript)[^>]*>.*?</\1>#si', '', $html);

        // Remove WordPress wrapper elements
        $html = (string) preg_replace('/<header[^>]*>.*?<\/header>/si', '', $html);
        $html = (string) preg_replace('/<div\s+itemprop="author"[^>]*>.*?<\/div>/si', '', $html);
        $html = (string) preg_replace('/<div\s+itemprop="publisher"[^>]*>.*?<\/div>/si', '', $html);
        $html = (string) preg_replace('/<meta[^>]*\/?>/si', '', $html);

        // Remove WordPress share/button divs
        $html = (string) preg_replace('#<div[^>]*class="[^"]*sharedaddy[^"]*"[^>]*>.*?</div>#si', '', $html);
        $html = (string) preg_replace('#<div[^>]*class="[^"]*wp-block-buttons[^"]*"[^>]*>.*?</div>#si', '', $html);
        $html = (string) preg_replace('#<div[^>]*class="[^"]*sd-content[^"]*"[^>]*>.*?</div>#si', '', $html);

        // Remove comments
        $html = (string) preg_replace('/<!--.*?-->/s', '', $html);

        // Extract content from articleBody div if present
        if (preg_match('/<div\s+itemprop="articleBody"[^>]*>(.*?)<\/div>/si', $html, $matches)) {
            $html = $matches[1];
        }

        // Remove entry-content wrapper
        $html = (string) preg_replace('/<div[^>]*class="[^"]*entry-content[^"]*"[^>]*>(.*?)<\/div>/si', '$1', $html);

        // Remove bare date strings
        $html = (string) preg_replace('/^\s*\d{2}-\d{2}-\d{4}\s*/m', '', $html);

        // Remove itemprop/itemscope attributes
        $html = (string) preg_replace('/\s*(itemprop|itemscope|itemtype)\s*=\s*"[^"]*"/i', '', $html);
        $html = (string) preg_replace('/\s*itemscope\b/i', '', $html);

        // Strip to allowed tags
        $allowedTagStr = implode('', array_map(fn (string $tag): string => "<{$tag}>", self::BLOG_ALLOWED_TAGS));
        $html = strip_tags($html, $allowedTagStr);

        // Remove empty paragraphs
        $html = (string) preg_replace('#<p>\s*(&nbsp;|\xC2\xA0)?\s*</p>#i', '', $html);

        // Normalize whitespace
        $html = (string) preg_replace('#\n{3,}#', "\n\n", $html);

        // Remove target="_self"
        $html = str_replace(' target="_self"', '', $html);

        // Rewrite old domain links to relative
        $html = str_replace('https://nederlandcrowdfunding.nl', '', $html);

        return trim($html);
    }

    private function cleanBlogExcerpt(string $excerpt, string $title): string
    {
        $excerpt = str_replace($title, '', $excerpt);
        $excerpt = (string) preg_replace('/^\s*\d{2}-\d{2}-\d{4}\s*/m', '', $excerpt);
        $excerpt = trim((string) preg_replace('/\s+/', ' ', $excerpt));

        if (mb_strlen($excerpt) > 300) {
            $excerpt = mb_substr($excerpt, 0, 297) . '...';
        }

        return $excerpt;
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

    private function seedMenuItems(): void
    {
        $this->info('Seeding menu items...');

        // --- Navbar ---
        MenuItem::updateOrCreate(
            ['location' => MenuLocation::Navbar, 'label' => 'Home', 'parent_id' => null],
            ['url' => '/', 'sort_order' => 0, 'is_active' => true, 'is_highlighted' => false],
        );

        $overOns = MenuItem::updateOrCreate(
            ['location' => MenuLocation::Navbar, 'label' => 'Over ons', 'parent_id' => null],
            ['url' => '/over-ons', 'sort_order' => 1, 'is_active' => true, 'is_highlighted' => false],
        );

        MenuItem::updateOrCreate(
            ['location' => MenuLocation::Navbar, 'label' => 'De vereniging', 'parent_id' => $overOns->id],
            ['url' => '/over-ons/de-vereniging', 'sort_order' => 0, 'is_active' => true, 'icon' => 'fa-solid fa-building-columns'],
        );
        MenuItem::updateOrCreate(
            ['location' => MenuLocation::Navbar, 'label' => 'Leden', 'parent_id' => $overOns->id],
            ['url' => '/over-ons/leden', 'sort_order' => 1, 'is_active' => true, 'icon' => 'fa-solid fa-users'],
        );
        MenuItem::updateOrCreate(
            ['location' => MenuLocation::Navbar, 'label' => 'Bestuur & directie', 'parent_id' => $overOns->id],
            ['url' => '/over-ons/bestuur-directie', 'sort_order' => 2, 'is_active' => true, 'icon' => 'fa-solid fa-user-tie'],
        );

        MenuItem::updateOrCreate(
            ['location' => MenuLocation::Navbar, 'label' => 'Actueel', 'parent_id' => null],
            ['url' => '/actueel', 'sort_order' => 2, 'is_active' => true, 'is_highlighted' => false],
        );

        MenuItem::updateOrCreate(
            ['location' => MenuLocation::Navbar, 'label' => 'Contact', 'parent_id' => null],
            ['url' => '/contact', 'sort_order' => 3, 'is_active' => true, 'is_highlighted' => true, 'icon' => 'fa-solid fa-envelope'],
        );

        // --- Footer Pages ---
        MenuItem::updateOrCreate(
            ['location' => MenuLocation::FooterPages, 'label' => 'Home'],
            ['url' => '/', 'sort_order' => 0, 'is_active' => true],
        );
        MenuItem::updateOrCreate(
            ['location' => MenuLocation::FooterPages, 'label' => 'Actueel'],
            ['url' => '/actueel', 'sort_order' => 1, 'is_active' => true],
        );
        MenuItem::updateOrCreate(
            ['location' => MenuLocation::FooterPages, 'label' => 'Contact'],
            ['url' => '/contact', 'sort_order' => 2, 'is_active' => true],
        );

        // --- Footer About ---
        MenuItem::updateOrCreate(
            ['location' => MenuLocation::FooterAbout, 'label' => 'De vereniging'],
            ['url' => '/over-ons/de-vereniging', 'sort_order' => 0, 'is_active' => true],
        );
        MenuItem::updateOrCreate(
            ['location' => MenuLocation::FooterAbout, 'label' => 'Leden'],
            ['url' => '/over-ons/leden', 'sort_order' => 1, 'is_active' => true],
        );
        MenuItem::updateOrCreate(
            ['location' => MenuLocation::FooterAbout, 'label' => 'Bestuur & directie'],
            ['url' => '/over-ons/bestuur-directie', 'sort_order' => 2, 'is_active' => true],
        );
        MenuItem::updateOrCreate(
            ['location' => MenuLocation::FooterAbout, 'label' => 'Privacybeleid'],
            ['url' => '/privacybeleid', 'sort_order' => 3, 'is_active' => true],
        );

        MenuItem::clearMenuCache();
        $this->info('  Menu items seeded: ' . MenuItem::count());
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

}