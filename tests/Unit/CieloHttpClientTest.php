<?php

namespace TestApp\Unit;

use Cielo\API30\Http\CieloHttpClient;
use Cielo\API30\Http\CieloHttpResponse;
use PHPUnit\Framework\TestCase;

/**
 * Expõe os métodos protegidos de mascaramento para verificação nos testes.
 */
final class MaskableHttpClient extends CieloHttpClient
{
    /**
     * @param string[] $headers
     *
     * @return string[]
     */
    public function exposeMaskHeaders(array $headers): array
    {
        return $this->maskHeaders($headers);
    }

    public function exposeMaskBody(string $body): string
    {
        return $this->maskBody($body);
    }

    /**
     * @param array<string|int, string> $headers
     *
     * @return string[]
     */
    public function exposeFormatHeaders(array $headers): array
    {
        return $this->formatHeaders($headers);
    }
}

final class CieloHttpClientTest extends TestCase
{
    public function testResponseHoldsStatusAndBody(): void
    {
        $response = new CieloHttpResponse(201, '{"foo":"bar"}');

        $this->assertSame(201, $response->getStatusCode());
        $this->assertSame('{"foo":"bar"}', $response->getBody());
    }

    public function testResponseJsonDecodesBody(): void
    {
        $response = new CieloHttpResponse(200, '{"foo":"bar"}');

        $decoded = $response->json(true);

        $this->assertIsArray($decoded);
        $this->assertSame(['foo' => 'bar'], $decoded);
    }

    public function testResponseJsonReturnsNullForEmptyBody(): void
    {
        $response = new CieloHttpResponse(204, '');

        $this->assertNull($response->json());
    }

    public function testMaskAuthorizationHeaderKeepingScheme(): void
    {
        $client = new MaskableHttpClient();

        $masked = $client->exposeMaskHeaders([
            'MerchantId: abc',
            'Authorization: Basic dXNlcjpwYXNz',
        ]);

        $this->assertSame('MerchantId: abc', $masked[0]);
        $this->assertSame('Authorization: Basic ******', $masked[1]);
    }

    public function testMaskBearerAuthorizationHeader(): void
    {
        $client = new MaskableHttpClient();

        $masked = $client->exposeMaskHeaders(['Authorization: Bearer eyJ0eXAiOiJKV1Qi']);

        $this->assertSame('Authorization: Bearer ******', $masked[0]);
    }

    public function testMaskBodyHidesCardNumberKeepingBinAndLast4(): void
    {
        $client = new MaskableHttpClient();

        $masked = $client->exposeMaskBody('{"CardNumber":"1234567890123456","Holder":"John"}');

        $this->assertStringNotContainsString('1234567890123456', $masked);
        $this->assertStringNotContainsString('"Holder":"John"', $masked);
        $this->assertStringContainsString('123456******3456', $masked);
    }

    public function testMaskBodyHidesSecurityCodeAndAccessToken(): void
    {
        $client = new MaskableHttpClient();

        $masked = $client->exposeMaskBody('{"SecurityCode":"123","access_token":"secret-token"}');

        $this->assertStringNotContainsString('"123"', $masked);
        $this->assertStringNotContainsString('secret-token', $masked);
        $this->assertStringContainsString('"SecurityCode":"***"', $masked);
        $this->assertStringContainsString('"access_token":"***"', $masked);
    }

    public function testMaskBodyReturnsEmptyStringForEmptyBody(): void
    {
        $client = new MaskableHttpClient();

        $this->assertSame('', $client->exposeMaskBody(''));
    }

    public function testFormatHeadersWithAssociativeKeys(): void
    {
        $client = new MaskableHttpClient();

        $formatted = $client->exposeFormatHeaders([
            'MerchantId' => 'abc',
            'Authorization' => 'Basic xyz',
        ]);

        $this->assertSame(['MerchantId: abc', 'Authorization: Basic xyz'], $formatted);
    }

    public function testFormatHeadersWithNumericKeysKeepsPreformattedLine(): void
    {
        $client = new MaskableHttpClient();

        $formatted = $client->exposeFormatHeaders([
            'RequestId: 123',
            'Content-Type: application/json',
        ]);

        $this->assertSame(['RequestId: 123', 'Content-Type: application/json'], $formatted);
    }

    public function testFormatHeadersSkipsEmptyEntries(): void
    {
        $client = new MaskableHttpClient();

        $formatted = $client->exposeFormatHeaders(['', 'X-Foo: bar']);

        $this->assertSame(['X-Foo: bar'], $formatted);
    }
}
