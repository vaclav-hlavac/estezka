<?php

namespace App\Middleware;

use App\Utils\JsonResponseHelper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use Slim\Interfaces\ErrorHandlerInterface;
use Slim\Psr7\Response;
use Throwable;

/**
 * Global error handler middleware implementing Slim's ErrorHandlerInterface.
 *
 * Catches all unhandled exceptions and returns a standardized JSON error response.
 * Optionally logs the exception via the provided PSR-3 logger.
 */
class ErrorHandlerMiddleware implements ErrorHandlerInterface
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Handles uncaught exceptions in the application and returns a JSON error response.
     *
     * @param Request $request The incoming HTTP request.
     * @param Throwable $exception The exception that was thrown.
     * @param bool $displayErrorDetails Whether to include detailed error output (not used).
     * @param bool $logErrors Whether to log the error.
     * @param bool $logErrorDetails Whether to log detailed exception info.
     * @return ResponseInterface A JSON-formatted error response.
     */
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