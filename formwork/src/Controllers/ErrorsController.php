<?php

namespace Formwork\Controllers;

use Formwork\Http\JsonResponse;
use Formwork\Http\Response;
use Formwork\Http\ResponseStatus;
use Throwable;

class ErrorsController extends AbstractController implements ErrorsControllerInterface
{
    /**
     * ErrorsController@error action
     */
    public function error(ResponseStatus $responseStatus = ResponseStatus::InternalServerError, ?Throwable $throwable = null): Response
    {
        Response::cleanOutputBuffers();

        $response = $this->request->isXmlHttpRequest()
            ? JsonResponse::error('Error', $responseStatus)
            : new Response($this->view(
                'errors.error',
                [
                    'status'    => $responseStatus->code(),
                    'message'   => $responseStatus->message(),
                    'throwable' => $throwable,
                ]
            ), $responseStatus);

        if ($throwable !== null) {
            error_log(sprintf(
                "Uncaught %s: %s in %s:%s\nStack trace:\n%s\n",
                $throwable::class,
                $throwable->getMessage(),
                $throwable->getFile(),
                $throwable->getLine(),
                $throwable->getTraceAsString()
            ));
        }

        return $response;
    }

    /**
     * ErrorsController@notFound action
     */
    public function notFound(): Response
    {
        return $this->error(ResponseStatus::NotFound);
    }

    /**
     * ErrorsController@internalServerError action
     */
    public function internalServerError(Throwable $throwable): Response
    {
        return $this->error(ResponseStatus::InternalServerError, $throwable);
    }

    /**
     * ErrorsController@forbidden action
     */
    public function forbidden(): Response
    {
        return $this->error(ResponseStatus::Forbidden);
    }
}
