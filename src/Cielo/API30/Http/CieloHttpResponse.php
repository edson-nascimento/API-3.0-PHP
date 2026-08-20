<?php

namespace Cielo\API30\Http;

class CieloHttpResponse
{
    public function __construct(private int $statusCode, private string $body)
    {
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function isSuccess(): bool
    {
        return $this->statusCode >= 200 && $this->statusCode < 300;
    }

    public function json(bool $associative = false)
    {
        if ($this->body === '') {
            return null;
        }

        return json_decode($this->body, $associative);
    }
}
