<?php

namespace App\Attributes;

#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class ApiResponse
{
    public function __construct(
        public int $status,
        public string $description,
    ) {}
}
