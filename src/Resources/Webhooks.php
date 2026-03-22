<?php

namespace ReavaPay\Resources;

class Webhooks
{
    public function verify(string $payload, string $signature, string $secret): bool
    {
        if (empty($signature) || empty($secret)) {
            return false;
        }

        $computed = hash_hmac('sha256', $payload, $secret);

        return hash_equals($computed, $signature);
    }
}
