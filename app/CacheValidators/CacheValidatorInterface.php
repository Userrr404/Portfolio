<?php
namespace app\CacheValidators;

interface CacheValidatorInterface
{
    /**
     * Decide if this validator applies to a cache key
     */
    public function supports(string $key): bool;

    /**
     * Validate payload schema + semantics
     */
    public function validate(array $payload): ?string;
}