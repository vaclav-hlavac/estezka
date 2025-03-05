<?php

namespace App\Middleware;

use App\Utils\JsonResponseHelper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use Slim\Interfaces\ErrorHandlerInterface;
use Slim\Psr7\Response;
use Throwable;

class ErrorHandlerMiddleware implements ErrorHandlerInterface
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function __invoke(Request $request, Throwable $exception, bool $displayErrorDetails, bool $logErrors, bool $logErrorDetails): ResponseInterface
    {
        $statusCode = $exception->getCode();

        if ($statusCode < 100 || $statusCode > 599) {
            $statusCode = 500;
        }

        // Logging
        if ($logErrors) {
            $this->logger->error($exception->getMessage(), ['exception' => $exception]);
        }

        return JsonResponseHelper::jsonResponse([
            'error'   => true,
            'message' => $exception->getMessage(),
            'code'    => $statusCode
        ], $statusCode, new Response());
    }
}