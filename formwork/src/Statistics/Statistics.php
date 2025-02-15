<?php

namespace Formwork\Statistics;

use Formwork\Config\Config;
use Formwork\Http\Request;
use Formwork\Http\Utils\IpAnonymizer;
use Formwork\Http\Utils\Visitor;
use Formwork\Log\Registry;
use Formwork\Translations\Translation;
use Formwork\Utils\Arr;
use Formwork\Utils\Date;
use Formwork\Utils\FileSystem;
use Formwork\Utils\Str;
use Formwork\Utils\Uri;
use Generator;

final class Statistics
{
    /**
     * Date format
     */
    private const string DATE_FORMAT = 'Ymd';

    /**
     * Delay between visits from the same visitor
     */
    private const int VISITS_DELAY = 15;

    /**
     * Probability of visitor data cleanup
     */
    private const int CLEANUP_PROBABILITY = 5;

    /**
     * Time to live for visitor data
     */
    private const int CLEANUP_TTL = 60 * 60 * 48;

    /**
     * Number of days displayed in the statistics chart
     */
    private const int CHART_LIMIT = 7;

    /**
     * Visits registry filename
     */
    private const string VISITS_FILENAME = 'visits.json';

    /**
     * Unique visits registry filename
     */
    private const string UNIQUE_VISITS_FILENAME = 'uniqueVisits.json';

    /**
     * Visitors registry filename
     */
    private const string VISITORS_FILENAME = 'visitors.json';

    /**
     * Page views registry filename
     */
    private const string PAGE_VIEWS_FILENAME = 'pageViews.json';

    /**
     * Sources registry filename
     */
    private const string SOURCES_FILENAME = 'sources.json';

    /**
     * Devices registry filename
     */
    private const string DEVICES_FILENAME = 'devices.json';

    /**
     * Visits registry
     */
    private Registry $visitsRegistry;

    /**
     * Unique visits registry
     */
    private Registry $uniqueVisitsRegistry;

    /**
     * Visitors registry
     */
    private Registry $visitorsRegistry;

    /**
     * Page views registry
     */
    private Registry $pageViewsRegistry;

    /**
     * Sources registry
     */
    private Registry $sourcesRegistry;

    /**
     * Devices registry
     */
    private Registry $devicesRegistry;

    public function __construct(
        string $path,
        private Config $config,
        private Request $request,
        private Translation $translation,
    ) {
        if (!FileSystem::exists($path)) {
            FileSystem::createDirectory($path);
        }

        $this->visitsRegistry = new Registry(FileSystem::joinPaths($path, self::VISITS_FILENAME));
        $this->uniqueVisitsRegistry = new Registry(FileSystem::joinPaths($path, self::UNIQUE_VISITS_FILENAME));
        $this->visitorsRegistry = new Registry(FileSystem::joinPaths($path, self::VISITORS_FILENAME));
        $this->pageViewsRegistry = new Registry(FileSystem::joinPaths($path, self::PAGE_VIEWS_FILENAME));
        $this->sourcesRegistry = new Registry(FileSystem::joinPaths($path, self::SOURCES_FILENAME));
        $this->devicesRegistry = new Registry(FileSystem::joinPaths($path, self::DEVICES_FILENAME));
    }

    /**
     * Track a visit
     */
    public function trackVisit(): void
    {
        if ($this->request->isLocalhost() && !$this->config->get('system.statistics.trackLocalhost')) {
            return;
        }

        if (Visitor::isBot($this->request) || !$this->request->ip()) {
            return;
        }

        $ip = IpAnonymizer::anonymize($this->request->ip());
        $uri = Str::append(Uri::make(['query' => '', 'fragment' => ''], $this->request->uri()), '/');

        // Prefer speed over security for hashing, as it's not a security-critical operation
        $hash = hash('xxh3', $ip . '@' . $uri);

        $timestamp = time();

        if ($this->visitorsRegistry->has($hash) && $timestamp - $this->visitorsRegistry->get($hash) < self::VISITS_DELAY) {
            return;
        }

        if (random_int(1, 100) <= self::CLEANUP_PROBABILITY) {
            $this->cleanupVisitorData();
        }

        $this->visitorsRegistry->set($hash, $timestamp);
        $this->visitorsRegistry->save();

        $date = date(self::DATE_FORMAT, $timestamp);

        $todayVisits = $this->visitsRegistry->has($date) ? (int) $this->visitsRegistry->get($date) : 0;
        $this->visitsRegistry->set($date, $todayVisits + 1);
        $this->visitsRegistry->save();

        $todayUniqueVisits = $this->uniqueVisitsRegistry->has($date) ? (int) $this->uniqueVisitsRegistry->get($date) : 0;
        if (!$this->visitorsRegistry->has($ip) || date(self::DATE_FORMAT, $this->visitorsRegistry->get($ip)) !== $date) {
            $this->uniqueVisitsRegistry->set($date, $todayUniqueVisits + 1);
            $this->uniqueVisitsRegistry->save();
        }

        $this->visitorsRegistry->set($ip, $timestamp);

        $pageViews = $this->pageViewsRegistry->has($uri) ? (int) $this->pageViewsRegistry->get($uri) : 0;
        $this->pageViewsRegistry->set($uri, $pageViews + 1);
        $this->pageViewsRegistry->save();

        if (($referer = $this->request->referer()) === null || ($source = Uri::host($referer)) !== $this->request->host()) {
            $source ??= '';
            $sourceVisits = $this->sourcesRegistry->has($source) ? (int) $this->sourcesRegistry->get($source) : 0;
            $this->sourcesRegistry->set($source, $sourceVisits + 1);
            $this->sourcesRegistry->save();
        }

        $device = Visitor::getDeviceType($this->request)->value;
        $deviceVisits = $this->devicesRegistry->has($device) ? (int) $this->devicesRegistry->get($device) : 0;
        $this->devicesRegistry->set($device, $deviceVisits + 1);
        $this->devicesRegistry->save();
    }

    /**
     * Return chart data
     *
     * @return array{labels: array<string>, series: list<list<int>>}
     */
    public function getChartData(int $limit = self::CHART_LIMIT): array
    {

        $visits = $this->getVisits($limit);
        $uniqueVisits = $this->getUniqueVisits($limit);

        $labels = Arr::map(
            iterator_to_array($this->generateDays($limit)),
            fn(string $day): string => Date::formatTimestamp(Date::toTimestamp($day, self::DATE_FORMAT), "D\nj M", $this->translation)
        );

        return [
            'labels' => $labels,
            'series' => [
                array_values($visits),
                array_values($uniqueVisits),
            ],
        ];
    }

    /**
     * Return page views
     *
     * @return array<string, int>
     */
    public function getPageViews(): array
    {
        return Arr::sort($this->pageViewsRegistry->toArray(), SORT_DESC);
    }

    /**
     * Return visits by source
     *
     * @return array<string, int>
     */
    public function getSources(): array
    {
        return Arr::sort($this->sourcesRegistry->toArray(), SORT_DESC);
    }

    /**
     * Return visits by devices
     *
     * @return array<string, int>
     */
    public function getDevices(): array
    {
        return Arr::sort($this->devicesRegistry->toArray(), SORT_DESC);
    }

    /**
     * Return visits
     *
     * @return array<string, int>
     */
    public function getVisits(int $limit = self::CHART_LIMIT): array
    {
        return $this->interpolateVisits($this->visitsRegistry->toArray(), $limit);
    }

    /**
     * Return unique visits
     *
     * @return array<string, int>
     */
    public function getUniqueVisits(int $limit = self::CHART_LIMIT): array
    {
        return $this->interpolateVisits($this->uniqueVisitsRegistry->toArray(), $limit);
    }

    /**
     * Interpolate visits
     *
     * @param array<string, int> $visits
     *
     * @return array<string, int>
     */
    private function interpolateVisits(array $visits, int $limit): array
    {
        $result = [];
        foreach ($this->generateDays($limit) as $day) {
            $result[$day] = $visits[$day] ?? 0;
        }
        return $result;
    }

    /**
     * Generate days
     *
     * @return Generator<int, string>
     */
    private function generateDays(int $limit): Generator
    {
        $low = time() - ($limit - 1) * 86400;
        for ($i = 0; $i < $limit; $i++) {
            yield date(self::DATE_FORMAT, $low + $i * 86400);
        }
    }

    /**
     * Cleanup visitor data prior to CLEANUP_TTL
     */
    private function cleanupVisitorData(): void
    {
        $time = time();
        foreach ($this->visitorsRegistry->toArray() as $key => $timestamp) {
            if ($time - $timestamp > self::CLEANUP_TTL) {
                $this->visitorsRegistry->remove($key);
            }
        }
    }
}
