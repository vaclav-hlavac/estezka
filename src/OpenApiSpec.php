<?php

namespace App;
require_once __DIR__ . '/../vendor/autoload.php';

use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 * name="user",
 * description="User related operations"
 * )
 * @OA\Info(
 * version="1.0",
 * title="Example API",
 * description="Example info",
 * @OA\Contact(name="Swagger API Team")
 * )
 * @OA\Server(
 * url="https://example.localhost",
 * description="API server"
 * )
 */
class OpenApiSpec {}