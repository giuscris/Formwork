<?php

namespace Formwork\Templates;

use Formwork\App;
use Formwork\Assets;
use Formwork\Config\Config;
use Formwork\Schemes\Schemes;
use Formwork\Security\CsrfToken;
use Formwork\Services\Container;
use Formwork\Utils\FileSystem;

class TemplateFactory
{
    public function __construct(protected Container $container, protected App $app, protected Config $config, protected Schemes $schemes)
    {
    }

    public function make(string $name): Template
    {
        $path = $this->config->get('system.templates.path');

        $assets = new Assets(
            FileSystem::joinPaths($path, 'assets'),
            $this->app->site()->uri('/site/templates/assets/', includeLanguage: false)
        );

        return $this->container->build(Template::class, [
            'name'    => $name,
            'path'    => $path,
            'methods' => [
                'assets' => fn () => $assets,
            ],
            'vars' => [
                'router'    => $this->app->router(),
                'site'      => $this->app->site(),
                'csrfToken' => $this->app->getService(CsrfToken::class),
            ],
            'scheme' => $this->schemes->get('pages.' . $name),
        ]);
    }
}
