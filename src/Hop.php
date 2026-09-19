<?php

namespace Baleeghuddin\RedirectChecker;

class Hop
{
    public function __construct(
        private string $url,
        private int $statusCode,
        private ?string $location = null,
        private int $durationMs = 0,
        private ?string $error = null
    ) {
    }

    public function url(): string
    {
        return $this->url;
    }

    public function statusCode(): int
    {
        return $this->statusCode;
    }

    public function location(): string
    {
        return $this->location ?? '-';
    }

    public function durationMs(): int
    {
        return $this->durationMs;
    }

    public function error(): ?string
    {
        return $this->error;
    }

    public function isRedirect(): bool
    {
        return $this->statusCode >= 300 && $this->statusCode < 400;
    }
}
