<?php

declare(strict_types=1);
namespace Tests\Unit\Models;

use PHPUnit\Framework\TestCase;
use App\Models\RefreshToken;
use InvalidArgumentException;
use Random\RandomException;

final class RefreshTokenTest extends TestCase
{
    public function testInitializationGeneratesTokenAndExpiration(): void
    {
        $data = ['id_user' => 1];
        $token = new RefreshToken($data);

        $this->assertSame(1, $token->id_user);
        $this->assertNotEmpty($token->token);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $token->token);
        $this->assertNotEmpty($token->expires_at);
    }

    public function testInitializationWithOptionalData(): void
    {
        $data = [
            'id_user' => 5,
            'id_refresh_token' => 10,
            'created_at' => '2025-04-17 10:00:00',
        ];

        $token = new RefreshToken($data);

        $this->assertSame(10, $token->id_refresh_token);
        $this->assertSame('2025-04-17 10:00:00', $token->created_at);
    }

    public function testGenerateNewTokenChangesValue(): void
    {
        $data = ['id_user' => 2];
        $token = new RefreshToken($data);
        $original = $token->token;

        $token->generateNewToken();

        $this->assertNotSame($original, $token->token);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $token->token);
    }

    public function testMissingRequiredFieldThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new RefreshToken([]);
    }

    public function testJsonSerializeIncludesExpectedFields(): void
    {
        $data = ['id_user' => 7];
        $token = new RefreshToken($data);
        $json = $token->jsonSerialize();

        $this->assertSame($token->token, $json['token']);
        $this->assertSame(7, $json['id_user']);
        $this->assertArrayHasKey('expires_at', $json);
        $this->assertArrayHasKey('created_at', $json);
        $this->assertArrayHasKey('id_refresh_token', $json);
    }

    public function testToDatabaseStructure(): void
    {
        $data = ['id_user' => 9];
        $token = new RefreshToken($data);
        $db = $token->toDatabase();

        $this->assertIsArray($db);
        $this->assertSame(9, $db['id_user']);
        $this->assertSame($token->token, $db['token']);
        $this->assertSame($token->expires_at, $db['expires_at']);
        $this->assertSame($token->created_at, $db['created_at']);
        $this->assertArrayHasKey('id_refresh_token', $db);
    }

    public function testGetIdReturnsCorrectValue(): void
    {
        $token = new RefreshToken([
            'id_user' => 10,
            'id_refresh_token' => 50
        ]);

        $this->assertSame(50, $token->getId());
    }
}