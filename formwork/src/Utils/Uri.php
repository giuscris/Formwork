<?php

namespace Formwork\Utils;

use Formwork\Traits\StaticClass;
use InvalidArgumentException;

class Uri
{
    use StaticClass;

    /**
     * Default ports which will not be present in generated URI
     *
     * @var array<string, int>
     */
    protected const array DEFAULT_PORTS = ['http' => 80, 'https' => 443];

    /**
     * Current URI
     */
    protected static ?string $current = null;

    /**
     * Get the scheme of current or a given URI
     */
    public static function scheme(string $uri): ?string
    {
        $scheme = static::parseComponent($uri, PHP_URL_SCHEME);
        return $scheme !== null ? strtolower((string) $scheme) : null;
    }

    /**
     * Get the host of current or a given URI
     */
    public static function host(string $uri): ?string
    {
        $host = static::parseComponent($uri, PHP_URL_HOST);
        return $host !== null ? strtolower((string) $host) : null;
    }

    /**
     * Get the port of current or a given URI
     */
    public static function port(string $uri): ?int
    {
        return static::parseComponent($uri, PHP_URL_PORT);
    }

    /**
     * Return the default port of current URI or a given scheme
     */
    public static function getDefaultPort(string $scheme): int
    {
        return self::DEFAULT_PORTS[$scheme] ?? throw new InvalidArgumentException(sprintf('Unknown scheme "%s"', $scheme));
    }

    /**
     * Return whether current or a given port is default
     */
    public static function isDefaultPort(int $port, string $scheme): bool
    {
        return $port === static::getDefaultPort($scheme);
    }

    /**
     * Get the path of current or a given URI
     */
    public static function path(string $uri): ?string
    {
        return static::parseComponent($uri, PHP_URL_PATH);
    }

    /**
     * Get the absolute path of current or a given URI
     */
    public static function absolutePath(string $uri): string
    {
        return static::base($uri) . static::path($uri);
    }

    /**
     * Get the query of current or a given URI
     */
    public static function query(string $uri): ?string
    {
        return static::parseComponent($uri, PHP_URL_QUERY);
    }

    /**
     * Get the fragment of current or a given URI
     */
    public static function fragment(string $uri): ?string
    {
        return static::parseComponent($uri, PHP_URL_FRAGMENT);
    }

    /**
     * Get the base URI (scheme://host:port) of current or a given URI
     */
    public static function base(string $uri): string
    {
        return sprintf('%s://%s%s', static::scheme($uri), static::host($uri), static::port($uri) !== null ? ':' . static::port($uri) : '');
    }

    /**
     * Convert the query of current or a given URI to array
     *
     * @return array<array<string>|string>
     */
    public static function queryToArray(string $uri): array
    {
        parse_str(static::query($uri) ?? '', $array);
        return $array;
    }

    /**
     * Parse current or a given URI and get an associative array
     * containing its scheme, host, port, path, query and fragment
     *
     * @return array{scheme: ?string, host: ?string, port: ?int, path: ?string, query: ?string, fragment: ?string}
     */
    public static function parse(string $uri): array
    {
        return [
            'scheme'   => static::scheme($uri),
            'host'     => static::host($uri),
            'port'     => static::port($uri),
            'path'     => static::path($uri),
            'query'    => static::query($uri),
            'fragment' => static::fragment($uri),
        ];
    }

    /**
     * Make a URI based on the current or a given one using an array with parts
     *
     * @param array{scheme?: string, host?: string, port?: int, path?: string, query?: array<string>|string, fragment?: string} $parts
     *
     * @see Uri::parse()
     */
    public static function make(array $parts, string $uri, bool $forcePort = false): string
    {
        $givenParts = array_keys($parts);
        $parts = [...static::parse($uri), ...$parts];
        $result = '';
        if (!empty($parts['host'])) {
            $scheme = strtolower($parts['scheme'] ?? 'http');
            $port = $parts['port'] ?? static::getDefaultPort($scheme);
            $result = $scheme . '://' . strtolower($parts['host']);
            if ($forcePort || (in_array('port', $givenParts, true) && !static::isDefaultPort($port, $scheme))) {
                $result .= ':' . $port;
            }
        }
        // Normalize path slashes (leading and trailing separators are trimmed after so that the path
        // is always considered relative and we can then add a trailing slash conditionally)
        $normalizedPath = '/' . trim(Path::normalize($parts['path'] ?? ''), '/');
        // Add trailing slash only if the trailing component is not empty or a filename
        if ($normalizedPath !== '/' && !Str::contains(basename($normalizedPath), '.')) {
            $normalizedPath .= '/';
        }
        $result .= $normalizedPath;
        if (!empty($parts['query'])) {
            $result .= '?' . (is_array($parts['query']) ? http_build_query($parts['query']) : ltrim($parts['query'], '?'));
        }
        if (!empty($parts['fragment'])) {
            $result .= '#' . ltrim($parts['fragment'], '#');
        }
        return $result;
    }

    /**
     * Normalize URI fixing required parts and slashes
     */
    public static function normalize(string $uri): string
    {
        // TODO: we should not force trailing slash, avoid this in 2.0
        return Str::append(static::make([], $uri), '/');
    }

    /**
     * Remove query from current or a given URI
     */
    public static function removeQuery(string $uri): string
    {
        return static::make(['query' => ''], $uri);
    }

    /**
     * Remove fragment from current or a given URI
     */
    public static function removeFragment(string $uri): string
    {
        return static::make(['fragment' => ''], $uri);
    }

    /**
     * Resolve a relative URI against current or a given base URI
     */
    public static function resolveRelative(string $uri, string $base): string
    {
        if (Str::startsWith($uri, '#')) {
            return static::make(['fragment' => $uri], $base);
        }
        $uriPath = (string) static::path($uri);
        $basePath = (string) static::path($base);
        if (!Str::endsWith($basePath, '/')) {
            $basePath = dirname($basePath);
        }
        return static::make(['path' => Path::resolve($uriPath, $basePath)], $base);
    }

    /**
     * Parse URI component, throwing an exception when the URI is invalid
     */
    protected static function parseComponent(string $uri, int $component): mixed
    {
        $result = parse_url($uri, $component);
        if ($result === false) {
            throw new InvalidArgumentException(sprintf('Invalid URI "%s"', $uri));
        }
        return $result;
    }
}
