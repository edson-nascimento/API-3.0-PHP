<?php

namespace TestApp\Unit;

use Cielo\API30\Ecommerce\AccessToken;
use PHPUnit\Framework\TestCase;

final class AccessTokenTest extends TestCase
{
    public function testPopulateFromSnakeCaseKeys(): void
    {
        $data = new \stdClass();
        $data->access_token = 'eyJ0eXAiOiJKV1Qi...';
        $data->token_type = 'bearer';
        $data->expires_in = 86399;

        $accessToken = new AccessToken();
        $accessToken->populate($data);

        $this->assertSame('eyJ0eXAiOiJKV1Qi...', $accessToken->getAccessToken());
        $this->assertSame('bearer', $accessToken->getTokenType());
        $this->assertSame(86399, $accessToken->getExpiresIn());
    }

    public function testFromJson(): void
    {
        $json = json_encode([
            'access_token' => 'eyJ0eXAiOiJKV1Qi...',
            'token_type' => 'bearer',
            'expires_in' => '86399',
        ]);

        $accessToken = AccessToken::fromJson($json);

        $this->assertSame('eyJ0eXAiOiJKV1Qi...', $accessToken->getAccessToken());
        $this->assertSame('bearer', $accessToken->getTokenType());
        $this->assertSame(86399, $accessToken->getExpiresIn());
        $this->assertNotNull($accessToken->getExpiresAt());
        $this->assertTrue($accessToken->isValid());
    }

    public function testDefaultsToNullWhenMissing(): void
    {
        $accessToken = new AccessToken();
        $accessToken->populate(new \stdClass());

        $this->assertNull($accessToken->getAccessToken());
        $this->assertNull($accessToken->getTokenType());
        $this->assertNull($accessToken->getExpiresIn());
        $this->assertNull($accessToken->getExpiresAt());
        $this->assertFalse($accessToken->isValid());
    }

    public function testFluentSetters(): void
    {
        $accessToken = new AccessToken('abc', 'bearer', 86399);

        $this->assertSame('abc', $accessToken->getAccessToken());
        $this->assertSame('bearer', $accessToken->getTokenType());
        $this->assertSame(86399, $accessToken->getExpiresIn());
        $this->assertNull($accessToken->getExpiresAt());
        $this->assertTrue($accessToken->isValid());
    }

    public function testIsValid(): void
    {
        $accessToken = new AccessToken('abc', 'bearer', 60);
        $accessToken->setExpiresAt(time() - 1);
        $this->assertFalse($accessToken->isValid());

        $accessToken->setExpiresIn(600);
        $accessToken->calculateExpiresAt();
        $this->assertTrue($accessToken->isValid());
    }
}
