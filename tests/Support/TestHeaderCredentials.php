<?php

declare(strict_types=1);

namespace GrpcLiteGax\Tests\Support;

use Google\ApiCore\HeaderCredentialsInterface;

final class TestHeaderCredentials implements HeaderCredentialsInterface
{
    public bool $checkedUniverseDomain = false;

    public ?string $audience = null;

    /**
     * @param array<string, list<string>>|string|null $headers
     */
    public function __construct(private readonly array|string|null $headers)
    {
    }

    #[\Override]
    public function getAuthorizationHeaderCallback(?string $audience = null): ?callable
    {
        $this->audience = $audience;

        if ($this->headers === null) {
            return null;
        }

        return fn (): array|string => $this->headers;
    }

    #[\Override]
    public function checkUniverseDomain(): void
    {
        $this->checkedUniverseDomain = true;
    }
}
