<?php

namespace App\Services;

class EvaluationResult
{
    public const REASON_TARGETING = 'targeting';
    public const REASON_GLOBAL = 'global';
    public const REASON_DEFAULT = 'default';

    public function __construct(
        public readonly bool $enabled,
        public readonly string $reason,
        public readonly ?string $variant = null
    ) {}

    public static function targeting(bool $enabled): self
    {
        return new self(
            enabled: $enabled,
            reason: self::REASON_TARGETING
        );
    }

    public static function global(bool $enabled): self
    {
        return new self(
            enabled: $enabled,
            reason: self::REASON_GLOBAL
        );
    }

    public static function default(): self
    {
        return new self(
            enabled: false,
            reason: self::REASON_DEFAULT
        );
    }

    public function toArray(): array
    {
        return [
            'enabled' => $this->enabled,
            'reason' => $this->reason,
            'variant' => $this->variant,
        ];
    }
}
