<?php

namespace App\Support;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * Trims what the widget captured to known keys and fixed sizes before it's stored.
 * The widget already masks inputs and drops headers; this is the server-side backstop,
 * so a misbehaving (or hostile) client can't park megabytes of JSON in a row.
 */
class CapturedContext
{
    /** Page title, viewport, device pixel ratio, user agent, scroll position, language. */
    public static function context(?array $input): ?array
    {
        if (! $input) {
            return null;
        }

        return array_filter([
            'title' => self::string($input['title'] ?? null, 300),
            'viewport' => self::size($input['viewport'] ?? null),
            'screen' => self::size($input['screen'] ?? null),
            'dpr' => is_numeric($input['dpr'] ?? null) ? round((float) $input['dpr'], 2) : null,
            'user_agent' => self::string($input['user_agent'] ?? null, 500),
            'language' => self::string($input['language'] ?? null, 35),
            'scroll' => self::point($input['scroll'] ?? null),
        ], fn ($value) => $value !== null);
    }

    /** The element the reviewer picked: selector, a slice of its HTML and text, and where it was. */
    public static function element(?array $input): ?array
    {
        if (! $input || ! is_string($input['selector'] ?? null)) {
            return null;
        }

        $rect = $input['rect'] ?? null;

        return array_filter([
            'selector' => self::string($input['selector'], 1000),
            'html' => self::string($input['html'] ?? null, config('roxy.max_html_length')),
            'text' => self::string($input['text'] ?? null, 500),
            'rect' => is_array($rect) ? array_map(
                fn ($value) => is_numeric($value) ? round((float) $value, 1) : null,
                Arr::only($rect, ['x', 'y', 'width', 'height']),
            ) : null,
        ], fn ($value) => $value !== null);
    }

    /** Most recent console errors: level, message, source location. */
    public static function console(?array $input): ?array
    {
        return self::list($input, config('roxy.max_console_entries'), fn (array $entry) => array_filter([
            'level' => self::string($entry['level'] ?? null, 20),
            'message' => self::string($entry['message'] ?? null, 1000),
            'source' => self::string($entry['source'] ?? null, 500),
            'at' => self::string($entry['at'] ?? null, 40),
        ], fn ($value) => $value !== null));
    }

    /** Failed requests: method, URL (query string dropped), status, error. */
    public static function network(?array $input): ?array
    {
        return self::list($input, config('roxy.max_network_entries'), fn (array $entry) => array_filter([
            'method' => self::string(Str::upper((string) ($entry['method'] ?? '')), 10),
            'url' => self::string(isset($entry['url']) ? Str::before((string) $entry['url'], '?') : null, 1000),
            'status' => is_numeric($entry['status'] ?? null) ? (int) $entry['status'] : null,
            'error' => self::string($entry['error'] ?? null, 300),
            'at' => self::string($entry['at'] ?? null, 40),
        ], fn ($value) => $value !== null && $value !== ''));
    }

    private static function list(?array $input, int $max, callable $map): ?array
    {
        if (! $input) {
            return null;
        }

        $entries = array_values(array_filter($input, 'is_array'));

        return array_map($map, array_slice($entries, -$max)) ?: null;
    }

    private static function string(mixed $value, int $max): ?string
    {
        return is_scalar($value) && $value !== '' ? Str::limit((string) $value, $max, '…') : null;
    }

    private static function size(mixed $value): ?array
    {
        return is_array($value) && is_numeric($value['width'] ?? null) && is_numeric($value['height'] ?? null)
            ? ['width' => (int) $value['width'], 'height' => (int) $value['height']]
            : null;
    }

    private static function point(mixed $value): ?array
    {
        return is_array($value) && is_numeric($value['x'] ?? null) && is_numeric($value['y'] ?? null)
            ? ['x' => (int) $value['x'], 'y' => (int) $value['y']]
            : null;
    }
}
