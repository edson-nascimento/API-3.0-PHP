<?php

namespace TestApp\Unit;

use Cielo\API30\Ecommerce\Environment;
use PHPUnit\Framework\TestCase;

final class EnvironmentTest extends TestCase
{
    public function testSandboxUrls(): void
    {
        $environment = Environment::sandbox();

        $this->assertSame('https://apisandbox.cieloecommerce.cielo.com.br/', $environment->getApiUrl());
        $this->assertSame('https://apiquerysandbox.cieloecommerce.cielo.com.br/', $environment->getApiQueryUrl());
        $this->assertSame('https://mpisandbox.braspag.com.br/', $environment->getMpiUrl());
    }

    public function testProductionUrls(): void
    {
        $environment = Environment::production();

        $this->assertSame('https://api.cieloecommerce.cielo.com.br/', $environment->getApiUrl());
        $this->assertSame('https://apiquery.cieloecommerce.cielo.com.br/', $environment->getApiQueryUrl());
        $this->assertSame('https://mpi.braspag.com.br/', $environment->getMpiUrl());
    }
}
