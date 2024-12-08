<?php

namespace Formwork\Services\Loaders;

use Formwork\Config\Config;
use Formwork\Fields\Dynamic\DynamicFieldValue;
use Formwork\Fields\FieldFactory;
use Formwork\Languages\Languages;
use Formwork\Schemes\SchemeFactory;
use Formwork\Schemes\Schemes;
use Formwork\Services\Container;
use Formwork\Services\ResolutionAwareServiceLoaderInterface;

class SchemesServiceLoader implements ResolutionAwareServiceLoaderInterface
{
    public function __construct(
        protected Config $config,
        protected Languages $languages,
    ) {
    }

    public function load(Container $container): object
    {
        $container->define(SchemeFactory::class);

        $container->define(FieldFactory::class);

        DynamicFieldValue::$varsLoader = fn () => $container->call(require $this->config->get('system.fields.dynamic.vars.file'));

        return $container->build(Schemes::class);
    }

    /**
     * @param Schemes $service
     */
    public function onResolved(object $service, Container $container): void
    {
        $service->loadFromPath($this->config->get('system.schemes.paths.system'));
        $service->loadFromPath($this->config->get('system.schemes.paths.site'));
    }
}
