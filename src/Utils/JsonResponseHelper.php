<?php

namespace App\Utils;

use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Response;

class JsonResponseHelper
{
    /**
     * @param string|array $data if string in $data, then string is converted to ['message' => $data] array
     * @param int $status
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public static function jsonResponse(string|array $data, int $status, ResponseInterface $response): ResponseInterface
    {
        if (is_string($data)) {
            $data = ['message' => $data];
        }

        $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
    }
}