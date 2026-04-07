<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class CircularController extends Controller
{
    private const WORDPRESS_API_BASE = 'https://chittagongclubltd.com/wp-json/wp/v2';

    public function index()
    {
        $perPage = 10;
        $page = max(1, (int) request()->query('page', 1));

        $resolvedCirculars = collect(Cache::remember(
            'circular_resolved_items_v3',
            now()->addHours(12),
            fn () => DB::table('T_CAREER')
                ->where('is_active', 1)
                ->whereNotNull('tx_title')
                ->orderBy('dtt_added', 'desc')
                ->get([
                    'id_career_key',
                    'tx_title',
                    'tx_body',
                    'tx_hash',
                    'dtt_added',
                    'dtt_ad_start',
                    'dtt_ad_close',
                    'is_online',
                ])
                ->map(fn ($row) => $this->transformCircular($row))
                ->filter(fn (array $circular) => $circular['has_image'])
                ->values()
                ->all()
        ));

        $circulars = new LengthAwarePaginator(
            $resolvedCirculars->forPage($page, $perPage)->values(),
            $resolvedCirculars->count(),
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );

        return view('pages.circulars', compact('circulars'));
    }

    private function transformCircular(object $row): array
    {
        $uploadedAt = $this->parseDate($row->dtt_added);
        $body = $this->extractBodyText($row->tx_body);
        $title = trim((string) ($row->tx_title ?: 'Circular'));
        $asset = $this->resolveCircularAsset($row, $body);

        if ($body === $title) {
            $body = null;
        }

        return [
            'id' => (int) $row->id_career_key,
            'title' => $title,
            'body' => $body,
            'image_url' => $asset['image_url'] ?? null,
            'source_url' => $asset['source_url'] ?? null,
            'uploaded_date' => $uploadedAt?->format('M d, Y') ?? 'Unknown',
            'uploaded_date_full' => $uploadedAt?->format('F d, Y') ?? 'Unknown',
            'has_image' => filled($asset['image_url']),
        ];
    }

    private function resolveCircularAsset(object $row, ?string $body = null): array
    {
        return Cache::remember(
            'circular_asset_v3_' . $row->id_career_key,
            now()->addHours(12),
            function () use ($row, $body) {
                $searchTerms = collect([
                    trim((string) ($row->tx_title ?? '')),
                    trim((string) ($body ?? '')),
                ])->filter()->unique()->values();

                $posts = $this->getWordPressCircularPosts();

                if ($posts->isEmpty()) {
                    return [
                        'image_url' => null,
                        'source_url' => null,
                    ];
                }

                $targetYear = $this->parseDate($row->dtt_added)?->format('Y');

                $bestPost = $posts
                    ->map(function (array $post) use ($searchTerms, $targetYear) {
                        $post['_score'] = $searchTerms
                            ->map(fn (string $term) => $this->scoreCircularPost($post, $this->normalizeTitle($term), $targetYear))
                            ->max() ?? 0;

                        return $post;
                    })
                    ->sortByDesc('_score')
                    ->first(fn (array $post) => ($post['_score'] ?? 0) >= 55 && filled($post['image_url'] ?? null));

                if ($bestPost) {
                    return [
                        'image_url' => $bestPost['image_url'] ?? null,
                        'source_url' => $bestPost['link'] ?? null,
                    ];
                }

                return [
                    'image_url' => null,
                    'source_url' => null,
                ];
            }
        );
    }

    private function getWordPressCircularPosts()
    {
        return Cache::remember(
            'circular_wordpress_posts_v2',
            now()->addHours(12),
            function () {
                $posts = collect();

                for ($page = 1; $page <= 3; $page++) {
                    try {
                        $response = Http::acceptJson()
                            ->connectTimeout(3)
                            ->timeout(8)
                            ->get(self::WORDPRESS_API_BASE . '/posts', [
                                'categories' => 3,
                                'page' => $page,
                                'per_page' => 50,
                                '_embed' => 'wp:featuredmedia',
                            ]);
                    } catch (\Throwable) {
                        break;
                    }

                    if (! $response->successful()) {
                        break;
                    }

                    $batch = collect($response->json())
                        ->map(function (array $post) {
                            $title = html_entity_decode((string) data_get($post, 'title.rendered', ''), ENT_QUOTES | ENT_HTML5);
                            $content = (string) data_get($post, 'content.rendered', '');
                            $embeddedImage = $post['_embedded']['wp:featuredmedia'][0]['source_url'] ?? null;

                            return [
                                'id' => (int) ($post['id'] ?? 0),
                                'title' => $title,
                                'normalized_title' => $this->normalizeTitle($title),
                                'year' => $this->parseDate((string) data_get($post, 'date'))?->format('Y'),
                                'link' => $post['link'] ?? null,
                                'image_url' => $embeddedImage ?: $this->extractFirstImageUrl($content),
                            ];
                        })
                        ->filter(fn (array $post) => $post['normalized_title'] !== '' && filled($post['image_url']));

                    $posts = $posts->concat($batch);

                    $totalPages = (int) $response->header('X-WP-TotalPages', $page);

                    if ($page >= $totalPages) {
                        break;
                    }
                }

                return $posts->values();
            }
        );
    }

    private function scoreCircularPost(array $post, string $targetTitle, ?string $targetYear): int
    {
        if ($targetTitle === '' || mb_strlen($targetTitle) < 3) {
            return 0;
        }

        $postTitle = (string) ($post['normalized_title'] ?? '');

        if ($postTitle === '') {
            return 0;
        }

        $score = 0;

        if ($postTitle === $targetTitle) {
            $score += 120;
        }

        if (Str::contains($postTitle, $targetTitle) || Str::contains($targetTitle, $postTitle)) {
            $score += 35;
        }

        similar_text($targetTitle, $postTitle, $similarity);
        $score += (int) round($similarity);

        $postYear = $post['year'] ?? null;

        if ($targetYear && $postYear === $targetYear) {
            $score += 10;
        }

        if (! empty($post['image_url'])) {
            $score += 10;
        }

        return $score;
    }

    private function extractFirstImageUrl(string $html): ?string
    {
        if ($html === '') {
            return null;
        }

        preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $html, $matches);

        return $matches[1] ?? null;
    }

    private function extractBodyText(?string $body): ?string
    {
        $body = trim((string) $body);

        if ($body === '' || $body === '?') {
            return null;
        }

        $decoded = json_decode($body, true);

        if (! is_array($decoded)) {
            return $body;
        }

        $text = collect($decoded)
            ->map(function (array $op) {
                $insert = $op['insert'] ?? null;

                return is_string($insert) ? $insert : '';
            })
            ->implode('');

        $text = trim(preg_replace("/\s+/", ' ', $text) ?? '');

        return $text !== '' ? $text : null;
    }

    private function normalizeTitle(string $value): string
    {
        return Str::of($value)
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/i', ' ')
            ->squish()
            ->value();
    }

    private function parseDate(?string $value): ?Carbon
    {
        if (! $value) {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }
}
