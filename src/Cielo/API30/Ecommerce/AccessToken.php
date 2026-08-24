<?php

namespace Cielo\API30\Ecommerce;

class AccessToken implements \JsonSerializable, CieloSerializable
{
    private ?int $expiresAt = null;

    public function __construct(
        private ?string $accessToken = null,

        private ?string $tokenType = null,

        private ?int $expiresIn = null,
    ) {
    }

    public static function fromJson(string $json): self
    {
        $accessToken = new AccessToken();
        $accessToken->populate(\json_decode($json));

        return $accessToken;
    }

    public function populate(\stdClass $data)
    {
        $this->accessToken = $data->access_token ?? null;
        $this->tokenType = $data->token_type ?? null;
        $this->expiresIn = isset($data->expires_in) ? (int) $data->expires_in : null;
        $this->calculateExpiresAt();
    }

    public function jsonSerialize(): mixed
    {
        return get_object_vars($this);
    }

    public function calculateExpiresAt(): static
    {
        if ($this->expiresIn > 0) {
            $this->expiresAt = time() + $this->expiresIn;
        }

        return $this;
    }

    public function isValid(): bool
    {
        if (empty($this->accessToken)) {
            return false;
        }

        if (null !== $this->expiresAt && time() >= $this->expiresAt) {
            return false;
        }

        return true;
    }

    // gets and sets
    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    public function setAccessToken(?string $accessToken): static
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    public function getTokenType(): ?string
    {
        return $this->tokenType;
    }

    public function setTokenType(?string $tokenType): static
    {
        $this->tokenType = $tokenType;

        return $this;
    }

    public function getExpiresIn(): ?int
    {
        return $this->expiresIn;
    }

    public function setExpiresIn(?int $expiresIn): static
    {
        $this->expiresIn = $expiresIn;

        return $this;
    }

    public function getExpiresAt(): ?int
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(?int $expiresAt): static
    {
        $this->expiresAt = $expiresAt;

        return $this;
    }
}
