<?php

namespace Formwork\Panel\Controllers;

use Formwork\Controllers\ErrorsControllerInterface;
use Formwork\Http\JsonResponse;
use Formwork\Http\Response;
use Formwork\Http\ResponseStatus;
use Throwable;

final class ErrorsController extends AbstractController implements ErrorsControllerInterface
{
    /**
     * ErrorsController@error action
     */
    public function error(ResponseStatus $responseStatus = ResponseStatus::InternalServerError, ?Throwable $throwable = null): Response
    {
        return $this->makeErrorResponse($responseStatus, 'internalServerError', [
            'href'  => $this->makeGitHubIssueUri($throwable),
            'label' => $this->translate('panel.errors.action.reportToGithub'),
        ], ['throwable' => $throwable]);
    }

    /**
     * ErrorsController@notFound action
     */
    public function notFound(): Response
    {
        return $this->makeErrorResponse(ResponseStatus::NotFound, 'notFound', [
            'href'  => $this->panel->uri('/dashboard/'),
            'label' => $this->translate('panel.errors.action.returnToDashboard'),
        ]);
    }

    /**
     * ErrorsController@internalServerError action
     */
    public function internalServerError(Throwable $throwable): Response
    {
        return $this->makeErrorResponse(ResponseStatus::InternalServerError, 'internalServerError', [
            'href'  => $this->makeGitHubIssueUri($throwable),
            'label' => $this->translate('panel.errors.action.reportToGithub'),
        ], ['throwable' => $throwable]);
    }

    /**
     * ErrorsController@forbidden action
     */
    public function forbidden(): Response
    {
        return $this->makeErrorResponse(ResponseStatus::Forbidden, 'forbidden', [
            'href'  => $this->panel->uri('/dashboard/'),
            'label' => $this->translate('panel.errors.action.returnToDashboard'),
        ]);
    }

    /**
     * Make error response with error description
     *
     * @param array<mixed>         $action
     * @param array<string, mixed> $data
     */
    private function makeErrorResponse(ResponseStatus $responseStatus, string $name, array $action, array $data = []): Response
    {
        Response::cleanOutputBuffers();

        if ($this->request->isXmlHttpRequest()) {
            return JsonResponse::error('Error', $responseStatus);
        }

        return new Response($this->view('errors.error', [
            'title'       => $this->translate('panel.errors.error.' . $name . '.status'),
            'code'        => $responseStatus->code(),
            'status'      => $this->translate('panel.errors.error.' . $name . '.status'),
            'heading'     => $this->translate('panel.errors.error.' . $name . '.heading'),
            'description' => $this->translate('panel.errors.error.' . $name . '.description'),
            'action'      => $action,
            ...$data,
        ]), $responseStatus);
    }

    /**
     * Make a URI to a new GitHub issue with pre-filled data from an (uncaught) exception
     */
    private function makeGitHubIssueUri(?Throwable $throwable): string
    {
        if ($throwable === null) {
            return 'https://github.com/getformwork/formwork/issues/';
        }

        $query = http_build_query([
            'labels' => 'bug',
            'title'  => $throwable->getMessage(),
            'body'   => sprintf(
                "### Description\n\n[Please enter a description and the steps to reproduce the problem...]\n\n" .
                    "**Formwork**: %s\n**Php**: %s\n**OS**: %s\n**SAPI**: %s\n\n" .
                    "### Stack Trace\n```\nUncaught %s: %s in %s:%s\n\n%s\n",
                $this->app::VERSION,
                PHP_VERSION,
                PHP_OS_FAMILY,
                PHP_SAPI,
                $throwable::class,
                $throwable->getMessage(),
                $throwable->getFile(),
                $throwable->getLine(),
                $throwable->getTraceAsString()
            ),
        ]);

        return 'https://github.com/getformwork/formwork/issues/new/?' . $query;
    }
}
