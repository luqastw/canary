<?php

namespace App\Services;

class EvaluationContext
{
    public function __construct(
        public readonly string $userId,
        public readonly string $role,
        public readonly array $metadata = []
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            userId: $data['user_id'],
            role: $data['role'],
            metadata: $data['metadata'] ?? []
        );
    }

    public function toArray(): array
    {
        return [
            'user_id' => $this->userId,
            'role' => $this->role,
            'metadata' => $this->metadata,
        ];
    }
}
