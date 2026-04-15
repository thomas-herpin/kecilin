<?php

// # Feature: kecilin-url-shortener, Property 6: click counting accuracy

use App\Models\Click;
use App\Models\Link;
use App\Services\ClickTrackerService;
use Illuminate\Http\Request;

/**
 * Property 6: Klik valid menambah penghitung tepat 1, klik bot tidak menambah
 * Validates: Requirements 4.1, 4.4
 *
 * For every link with initial click count N:
 * - After one valid click is recorded, total_clicks must be exactly N+1.
 * - After a bot request, total_clicks must remain N.
 */
test('Property 6: click counting accuracy — valid clicks increment by exactly 1, bot clicks do not increment', function () {
    // # Feature: kecilin-url-shortener, Property 6: click counting accuracy
    // Validates: Requirements 4.1, 4.4

    $tracker = new ClickTrackerService();

    // Known bot user-agents
    $botUserAgents = [
        'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
        'Mozilla/5.0 (compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm)',
        'curl/7.88.1',
        'Wget/1.21.3',
        'Mozilla/5.0 (compatible; YandexBot/3.0; +http://yandex.com/bots)',
        'DuckDuckBot/1.0; (+http://duckduckgo.com/duckduckbot.html)',
        'Mozilla/5.0 (compatible; Baiduspider/2.0; +http://www.baidu.com/search/spider.html)',
        'Mozilla/5.0 (compatible; Slurp; http://help.yahoo.com/help/us/ysearch/slurp)',
        'facebot/1.0',
        'ia_archiver (+http://www.alexa.com/site/help/webmasters; crawler@alexa.com)',
    ];

    // Valid (human) user-agents
    $humanUserAgents = [
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/120.0.0.0 Safari/537.36',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Safari/537.36',
        'Mozilla/5.0 (X11; Linux x86_64; rv:109.0) Gecko/20100101 Firefox/121.0',
        'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 Mobile/15E148 Safari/604.1',
        'Mozilla/5.0 (Android 13; Mobile; rv:109.0) Gecko/109.0 Firefox/121.0',
        'Mozilla/5.0 (iPad; CPU OS 17_0 like Mac OS X) AppleWebKit/605.1.15 Mobile/15E148 Safari/604.1',
        'Opera/9.80 (Windows NT 6.1) Presto/2.12.388 Version/12.17',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Edg/120.0.0.0',
    ];

    $slugCounter = 0;

    for ($i = 0; $i < 100; $i++) {
        $initialClicks = random_int(0, 50);
        $isBot = (bool) random_int(0, 1);

        // Create a fresh link for each iteration
        $slug = 'prop6' . str_pad((string) $slugCounter++, 5, '0', STR_PAD_LEFT);
        $link = Link::create([
            'original_url' => 'https://example.com/page-' . $i,
            'slug'         => $slug,
            'qr_code_svg'  => '<svg></svg>',
            'total_clicks' => $initialClicks,
        ]);

        if ($isBot) {
            $ua = $botUserAgents[array_rand($botUserAgents)];
        } else {
            $ua = $humanUserAgents[array_rand($humanUserAgents)];
        }

        $request = Request::create('/' . $slug, 'GET');
        $request->headers->set('User-Agent', $ua);

        $tracker->record($link, $request);

        $freshLink = $link->fresh();

        if ($isBot) {
            // Bot: counter must remain unchanged
            expect($freshLink->total_clicks)->toBe(
                $initialClicks,
                "Iteration {$i}: bot UA '{$ua}' should NOT increment total_clicks (expected {$initialClicks}, got {$freshLink->total_clicks})"
            );

            expect(Click::where('link_id', $link->id)->count())->toBe(
                0,
                "Iteration {$i}: bot request should not create a Click record"
            );
        } else {
            // Human: counter must be exactly N+1
            expect($freshLink->total_clicks)->toBe(
                $initialClicks + 1,
                "Iteration {$i}: valid UA '{$ua}' should increment total_clicks by 1 (expected " . ($initialClicks + 1) . ", got {$freshLink->total_clicks})"
            );

            expect(Click::where('link_id', $link->id)->count())->toBe(
                1,
                "Iteration {$i}: valid click should create exactly 1 Click record"
            );
        }
    }
})->group('property');
