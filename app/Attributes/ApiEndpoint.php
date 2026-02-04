<?php

namespace App\Attributes;

#[\Attribute]
class ApiEndpoint
{
    public function __construct(
        public string $summary,
        public string $method = 'GET',
        public string $path = '',
        public array $tags = [],
        public string $description = '',
    ) {}
}
