<?php

declare(strict_types=1);
namespace Tests\Unit\Models;


use PHPUnit\Framework\TestCase;
use App\Models\User;
use InvalidArgumentException;

final class UserTest extends TestCase
{

    private $testUser = [
        'nickname' => 'tester',
        'name' => 'Test',
        'surname' => 'User',
        'password' => 'securepassword',
        'email' => 'test@example.com',
        'notifications_enabled' => false,
    ];

    private $idTestUser = [
        'id_user' => 1,
        'nickname' => 'tester',
        'name' => 'Test',
        'surname' => 'User',
        'password' => 'securepassword',
        'email' => 'test@example.com',
    ];

    public function testUserInitialization(): void
    {
        $data = $this->testUser;

        $user = new User($data);

        $this->assertSame('tester', $user->nickname);
        $this->assertSame('Test', $user->name);
        $this->assertSame('User', $user->surname);
        $this->assertSame('securepassword', $user->password);
        $this->assertSame('test@example.com', $user->email);
        $this->assertFalse($user->notifications_enabled);
    }

    public function testUserToDatabaseWithId(): void
    {
        $data = $this->idTestUser;

        $user = new User($data);
        $dbData = $user->toDatabase();

        $this->assertArrayHasKey('id_user', $dbData);
        $this->assertSame(1, $dbData['id_user']);
        $this->assertSame(1, $dbData['notifications_enabled']);
    }

    public function testJsonSerialization(): void
    {
        $data = $this->idTestUser;

        $user = new User($data);
        $json = $user->jsonSerialize();

        $this->assertArrayNotHasKey('password', $json); // password should not be in JSON output

        $this->assertSame('tester', $json['nickname']);
        $this->assertSame('Test', $json['name']);
        $this->assertSame('User', $json['surname']);
        $this->assertSame(1, $json['id_user']);
    }

    public function testMissingRequiredFields(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new User([]);
    }

    public function testEmptyRequiredFields(): void
    {
        $data = [
            'nickname' => '',
            'name' => '',
            'surname' => '',
            'password' => '',
            'email' => '',
        ];

        $this->expectException(InvalidArgumentException::class);
        new User($data);
    }

    public function testGetPayload(): void
    {
        $data = [
            'id_user' => 5,
            'nickname' => 'paytester',
            'name' => 'Payload',
            'surname' => 'User',
            'password' => 'payloadpass',
            'email' => 'payload@example.com',
            'notifications_enabled' => true,
        ];

        $user = new User($data);
        $payload = $user->getPayload();

        $this->assertSame(5, $payload['id_user']);
        $this->assertSame('payload@example.com', $payload['email']);
        $this->assertArrayHasKey('exp', $payload);
        $this->assertGreaterThan(time(), $payload['exp']);
    }

    public function testGetId(): void
    {
        $data = $this->idTestUser;

        $user = new User($data);
        $payload = $user->getPayload();

        $this->assertSame(1, $payload['id_user']);
        $this->assertSame('test@example.com', $payload['email']);
        $this->assertArrayHasKey('exp', $payload);
        $this->assertGreaterThan(time(), $payload['exp']);
    }
}
