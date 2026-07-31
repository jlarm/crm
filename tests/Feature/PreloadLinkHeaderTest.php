<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Vite preload "Link" header size
|--------------------------------------------------------------------------
|
| AddLinkHeadersForPreloadedAssets emits a single "Link" header listing every
| preloaded Vite asset. Left uncapped it grew past nginx's fastcgi buffers on
| the origin, which dropped the response and returned a 502 for every route
| that renders the root Blade view -- login and forgot-password included.
|
| These tests pin the cap so the header cannot silently creep back up as pages
| and chunks are added.
|
*/

/**
 * Header bytes we allow ourselves. nginx's default fastcgi_buffer_size is 4k and
 * the header that caused the incident was ~4840 bytes. The capped header measures
 * roughly 400 bytes, so this leaves room for longer asset paths while still
 * tripping if the cap is ever removed.
 */
const MAX_LINK_HEADER_BYTES = 1024;

/**
 * Mirrors the limit passed to AddLinkHeadersForPreloadedAssets in bootstrap/app.php.
 */
const MAX_PRELOADED_ASSETS = 5;

/**
 * @return list<string>
 */
function preloadedLinks(?string $header): array
{
    return array_values(array_filter(array_map(trim(...), explode(',', (string) $header))));
}

test('the login page preload header stays within nginx fastcgi buffers', function (): void {
    $response = $this->get('/login');

    $response->assertOk();

    $header = $response->headers->get('Link');

    expect($header)->not->toBeNull()
        ->and(mb_strlen((string) $header))->toBeLessThan(MAX_LINK_HEADER_BYTES);
});

test('the preload header is capped to a fixed number of assets', function (): void {
    $response = $this->get('/login');

    $response->assertOk();

    expect(preloadedLinks($response->headers->get('Link')))
        ->toHaveCount(MAX_PRELOADED_ASSETS);
});

test('every guest page that renders vite assets stays within the buffer', function (string $uri): void {
    $response = $this->get($uri);

    $response->assertOk();

    expect(mb_strlen((string) $response->headers->get('Link')))
        ->toBeLessThan(MAX_LINK_HEADER_BYTES);
})->with([
    'login' => '/login',
    'forgot password' => '/forgot-password',
]);
