<?php

namespace Baleeghuddin\RedirectChecker;

class Result
{
    public function __construct(
        private string $originalUrl,
        private array $hops,
        private ?string $loopUrl = null
    ) {
    }

    public function originalUrl(): string
    {
        return $this->originalUrl;
    }

    /** @return Hop[] */
    public function hops(): array
    {
        return $this->hops;
    }

    public function finalUrl(): string
    {
        if (empty($this->hops)) {
            return $this->originalUrl;
        }

        return end($this->hops)->url();
    }

    public function hopCount(): int
    {
        return max(0, count($this->hops) - 1);
    }

    public function isLoop(): bool
    {
        return $this->loopUrl !== null;
    }

    public function loopUrl(): ?string
    {
        return $this->loopUrl;
    }
}
