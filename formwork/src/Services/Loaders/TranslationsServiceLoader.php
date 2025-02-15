<?php

namespace Formwork\Services\Loaders;

use Formwork\Config\Config;
use Formwork\Languages\Languages;
use Formwork\Services\Container;
use Formwork\Services\ResolutionAwareServiceLoaderInterface;
use Formwork\Translations\Translations;

final class TranslationsServiceLoader implements ResolutionAwareServiceLoaderInterface
{
    public function __construct(
        private Config $config,
        private Languages $languages,
    ) {}

    public function load(Container $container): object
    {
        return $container->build(Translations::class);
    }

    /**
     * @param Translations $service
     */
    public function onResolved(object $service, Container $container): void
    {
        $service->loadFromPath($this->config->get('system.translations.paths.system'));
        $service->loadFromPath($this->config->get('system.translations.paths.site'));
        $service->setCurrent($this->languages->current() ?? $this->config->get('system.translations.fallback'));
    }
}
