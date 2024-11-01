<?php

namespace Formwork\Controllers;

use Formwork\Http\FileResponse;
use Formwork\Router\RouteParams;
use Formwork\Utils\Exceptions\FileNotFoundException;
use Formwork\Utils\FileSystem;

class AssetsController extends AbstractController
{
    public function asset(RouteParams $routeParams): FileResponse
    {
        $path = FileSystem::joinPaths($this->config->get('system.images.processPath'), $routeParams->get('id'), $routeParams->get('name'));

        if (FileSystem::isFile($path)) {
            return new FileResponse($path, headers: ['Cache-Control' => 'private, max-age=31536000, immutable'], autoEtag: true, autoLastModified: true);
        }

        throw new FileNotFoundException('Cannot find asset');
    }

    public function template(RouteParams $routeParams): FileResponse
    {
        $path = FileSystem::joinPaths($this->config->get('system.templates.path'), 'assets', $routeParams->get('file'));

        if (FileSystem::isFile($path)) {
            $headers = $this->request->query()->has('v')
                ? ['Cache-Control' => 'private, max-age=31536000, immutable']
                : [];
            return new FileResponse($path, headers: $headers, autoEtag: true, autoLastModified: true);
        }

        throw new FileNotFoundException('Cannot find asset');
    }
}
