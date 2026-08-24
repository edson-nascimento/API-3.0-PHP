<?php

namespace TestApp\E2E;

use PHPUnit\Framework\Attributes\Group;

#[Group('payment')]
final class ServiceIntegrationTest extends AbstractE2ETestCase
{
    public function testServiceRequestCardBin(): void
    {
        $response = $this->getCieloEcommerce()->requestService()->apiQueryRequest('1/cardBin/539861');
        $this->assertSame(200, $response->getStatusCode(), $response->getBody());
        $this->assertJson($response->getBody(), $response->getBody());

        $decoded = $response->json();
        $this->assertIsObject($decoded);
        $this->assertSame('00', $decoded->Status ?? null);
        $this->assertSame('MASTERCARD', $decoded->Provider ?? null);
    }

    public function testServiceRequestZeroAuth(): void
    {
        $response = $this->getCieloEcommerce()->requestService()->apiRequest(
            method: 'POST',
            endpoint: '/1/zeroauth/',
            body: [
                // 'CardType' => 'Creditcard',
                'CardNumber' => '5502095822650000',
                'Holder' => 'Aline de Souza',
                'ExpirationDate' => '12/2035',
                'SecurityCode' => '123',
                'Brand' => 'Master',
            ],
        );
        $this->assertSame(200, $response->getStatusCode(), $response->getBody());
        $this->assertJson($response->getBody(), $response->getBody());

        $decoded = $response->json();
        $this->assertIsObject($decoded);
        $this->assertSame('00', $decoded->ReturnCode ?? null);
        $this->assertSame(true, $decoded->Valid ?? null);
        $this->assertIsString($decoded->ReturnMessage ?? null);
    }
}
