<?php

namespace App;

class GoogleMapsLocation
{
    private const EarthRadiusInKilometres = 6371.0;

    /**
     * Straight-line (great-circle) distance between two points, using the haversine formula.
     *
     * @param  array{latitude: float, longitude: float}  $from
     * @param  array{latitude: float, longitude: float}  $to
     */
    public static function distanceInKilometres(array $from, array $to): float
    {
        $latitudeDelta = deg2rad($to['latitude'] - $from['latitude']);
        $longitudeDelta = deg2rad($to['longitude'] - $from['longitude']);

        $a = sin($latitudeDelta / 2) ** 2
            + cos(deg2rad($from['latitude'])) * cos(deg2rad($to['latitude'])) * sin($longitudeDelta / 2) ** 2;

        return self::EarthRadiusInKilometres * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    /**
     * @return array{latitude: float, longitude: float}|null
     */
    public static function coordinatesFromUrl(?string $url): ?array
    {
        if (! static::isSupportedUrl($url)) {
            return null;
        }

        $path = (string) parse_url((string) $url, PHP_URL_PATH);
        $query = (string) parse_url((string) $url, PHP_URL_QUERY);
        parse_str($query, $parameters);

        $candidates = [
            static::coordinatesFromMatch($path, '/@(-?\d{1,2}(?:\.\d+)?),(-?\d{1,3}(?:\.\d+)?)/'),
            static::coordinatesFromMatch($path, '/!3d(-?\d{1,2}(?:\.\d+)?)!4d(-?\d{1,3}(?:\.\d+)?)/'),
            static::coordinatesFromMatch($path, '/(?:place|search)\/(-?\d{1,2}(?:\.\d+)?),(-?\d{1,3}(?:\.\d+)?)/'),
            static::coordinatesFromValue($parameters['query'] ?? $parameters['q'] ?? null),
        ];

        foreach ($candidates as $coordinates) {
            if ($coordinates !== null) {
                return $coordinates;
            }
        }

        return null;
    }

    public static function isSupportedUrl(?string $url): bool
    {
        if (! is_string($url) || filter_var($url, FILTER_VALIDATE_URL) === false) {
            return false;
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        $path = (string) parse_url($url, PHP_URL_PATH);

        if ($scheme !== 'https') {
            return false;
        }

        if ($host === 'maps.app.goo.gl') {
            return true;
        }

        return in_array($host, ['google.com', 'www.google.com', 'maps.google.com'], true)
            && ($host === 'maps.google.com' || str_starts_with($path, '/maps'));
    }

    /**
     * @return array{latitude: float, longitude: float}|null
     */
    private static function coordinatesFromMatch(string $value, string $pattern): ?array
    {
        if (preg_match($pattern, $value, $matches) !== 1) {
            return null;
        }

        return static::coordinates((float) $matches[1], (float) $matches[2]);
    }

    /**
     * @return array{latitude: float, longitude: float}|null
     */
    private static function coordinatesFromValue(mixed $value): ?array
    {
        if (! is_string($value) || preg_match('/^\s*(-?\d{1,2}(?:\.\d+)?)\s*,\s*(-?\d{1,3}(?:\.\d+)?)\s*$/', $value, $matches) !== 1) {
            return null;
        }

        return static::coordinates((float) $matches[1], (float) $matches[2]);
    }

    /**
     * @return array{latitude: float, longitude: float}|null
     */
    private static function coordinates(float $latitude, float $longitude): ?array
    {
        if ($latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
            return null;
        }

        return compact('latitude', 'longitude');
    }
}
