<?php

namespace App\Support;

final readonly class ModuleAccessResult
{
    public function __construct(
        public bool $available,
        public bool $entitled,
        public bool $enabled,
        public bool $permitted,
        public bool $allowed,
        public string $reason,
    ) {
    }

    public function publicState(): array
    {
        return [
            'available' => $this->available,
            'entitled' => $this->entitled,
            'enabled' => $this->enabled,
            'permitted' => $this->permitted,
            'accessible' => $this->allowed,
        ];
    }
}
