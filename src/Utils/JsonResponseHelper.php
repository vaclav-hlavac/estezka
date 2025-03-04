<?php

namespace App\Utils;

use JsonSerializable;
use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Response;

class JsonResponseHelper
{
    /**
     * @param string|array|object $data if string in $data, then string is converted to ['message' => $data] array
     * @param int $status
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public static function jsonResponse(string|array|object $data, int $status, ResponseInterface $response): ResponseInterface
    {
        if ($data instanceof JsonSerializable) {
            $data = $data->jsonSerialize();
        } else if (is_string($data)) {
            $data = ['message' => $data];
        }

        $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
    }
}