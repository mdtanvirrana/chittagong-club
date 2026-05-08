<?php

namespace App\Support;

use Illuminate\Support\Str;

class PortalContent
{
    public const NOTICE_CACHE_KEY = 'notice_board_v2';

    public const NOTICE_STALE_CACHE_KEY = 'notice_board_stale_v2';

    public const CIRCULAR_CACHE_KEY = 'circular_feed_v7';

    public const CIRCULAR_STALE_CACHE_KEY = 'circular_feed_stale_v7';

    public const DASHBOARD_CIRCULAR_HIGHLIGHT_CACHE_KEY = 'dashboard_circular_highlight_v4';

    public const DASHBOARD_CIRCULAR_HIGHLIGHT_STALE_CACHE_KEY = 'dashboard_circular_highlight_stale_v4';

    public static function deltaToPlainText(?string $payload): string
    {
        $payload = trim((string) $payload);

        if ($payload === '' || $payload === '?') {
            return '';
        }

        $decoded = json_decode($payload, true);

        if (! is_array($decoded)) {
            return trim(strip_tags($payload));
        }

        $text = collect($decoded)
            ->map(function ($operation) {
                $insert = $operation['insert'] ?? null;

                return is_string($insert) ? $insert : '';
            })
            ->implode('');

        return trim(str_replace(["\r\n", "\r"], "\n", $text));
    }

    public static function plainTextToDelta(?string $text): string
    {
        $text = trim(str_replace(["\r\n", "\r"], "\n", (string) $text));

        return json_encode(
            [['insert' => ($text !== '' ? $text : '')."\n"]],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        ) ?: '[{"insert":"\n"}]';
    }

    public static function excerpt(?string $text, int $limit = 120): string
    {
        $text = trim((string) $text);

        if ($text === '') {
            return '';
        }

        $firstLine = trim(explode("\n", $text)[0] ?? $text);
        $candidate = $firstLine !== '' ? $firstLine : preg_replace('/\s+/u', ' ', $text);
        $candidate = trim((string) $candidate);

        return Str::limit($candidate, $limit, '…');
    }

    public static function optionalField(?string $value, string $fallback = '?'): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? $fallback : $value;
    }

    public static function cleanedOptionalField(?string $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '' || $value === '?') {
            return null;
        }

        return $value;
    }

    public static function clearNoticeCaches(): void
    {
        $cache = PortalCache::store();
        $cache->forget(self::NOTICE_CACHE_KEY);
        $cache->forget(self::NOTICE_STALE_CACHE_KEY);
    }

    public static function clearCircularCaches(): void
    {
        $cache = PortalCache::store();
        $cache->forget(self::CIRCULAR_CACHE_KEY);
        $cache->forget(self::CIRCULAR_STALE_CACHE_KEY);
        $cache->forget(self::DASHBOARD_CIRCULAR_HIGHLIGHT_CACHE_KEY);
        $cache->forget(self::DASHBOARD_CIRCULAR_HIGHLIGHT_STALE_CACHE_KEY);
    }
}
