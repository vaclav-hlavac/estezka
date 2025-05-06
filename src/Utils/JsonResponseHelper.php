<?php

namespace App\Utils;

use JsonSerializable;
use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Response;

/**
 * Utility class for creating standardized JSON responses for HTTP requests.
 */
class JsonResponseHelper
{
    /**
     * Creates a JSON HTTP response with the given data and status code.
     *
     * - If the input `$data` is a string, it will be converted to an array with a `message` key.
     * - If the input `$data` implements `JsonSerializable`, it will be serialized via `jsonSerialize()`.
     * - The response will include the `Content-Type: application/json` header with UTF-8 encoding.
     *
     * @param string|array|object $data The data to be returned in the JSON response.
     * @param int $status The HTTP status code to be returned.
     * @param ResponseInterface $response The original PSR-7 response object to write to.
     * @return ResponseInterface The modified response containing the JSON body and headers.
     */
    public static function jsonResponse(string|array|object $data, int $status, ResponseInterface $response): ResponseInterface
    {
        if ($data instanceof JsonSerializable) {
            $data = $data->jsonSerialize();
        } else if (is_string($data)) {
            $data = ['message' => $data];
        }

        $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json; charset=utf-8')->withStatus($status);
    }
}