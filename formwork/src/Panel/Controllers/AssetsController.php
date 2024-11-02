<?php

namespace Formwork\Panel\Controllers;

use Formwork\Http\FileResponse;
use Formwork\Router\RouteParams;
use Formwork\Utils\Exceptions\FileNotFoundException;
use Formwork\Utils\FileSystem;

class AssetsController extends AbstractController
{
    public function asset(RouteParams $routeParams): FileResponse
    {
        $path = FileSystem::joinPaths($this->config->get('system.panel.paths.assets'), $routeParams->get('type'), $routeParams->get('file'));

        if (FileSystem::isFile($path)) {
            $headers = ($this->request->query()->has('v') || $routeParams->get('type') === 'icons')
                ? ['Cache-Control' => 'private, max-age=31536000, immutable']
                : [];
            return new FileResponse($path, headers: $headers, autoEtag: true, autoLastModified: true);
        }

        throw new FileNotFoundException('Cannot find asset');
    }
}
