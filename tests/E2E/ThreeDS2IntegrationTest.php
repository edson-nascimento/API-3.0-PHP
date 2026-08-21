<?php

namespace TestApp\E2E;

use Cielo\API30\Ecommerce\AccessToken;
use PHPUnit\Framework\Attributes\Group;

#[Group('payment')]
final class ThreeDS2IntegrationTest extends AbstractE2ETestCase
{
    public function testCreateAccessTokenAndVerifyExpiration(): void
    {
        $clientId = (string) getenv('CIELO_CLIENT_ID_3DS');
        $clientSecret = (string) getenv('CIELO_CLIENT_SECRET_3DS');

        if ($clientId === '' || $clientSecret === '') {
            throw new \RuntimeException('Você precisa informar Client ID e Client Secret 3DS para rodar os testes');
        }

        $token = $this->getCieloEcommerce()->create3DSAccessToken(
            clientId: $clientId,
            clientSecret: $clientSecret,
            establishmentCode: 1006993068,
            merchantName: 'Loja Exemplo Ltda',
            mcc: 5999,
        );

        $this->assertNotEmpty($token->getAccessToken());
        $this->assertSame('bearer', strtolower((string) $token->getTokenType()));
        $this->assertGreaterThan(0, (int) $token->getExpiresIn());
        $this->assertNotNull($token->getExpiresAt());
        $this->assertGreaterThan(time(), $token->getExpiresAt());
        $this->assertEqualsWithDelta(
            time() + (int) $token->getExpiresIn(),
            $token->getExpiresAt(),
            5
        );
        $this->assertTrue($token->isValid());
    }
}
