<?php

namespace App\Attributes;

#[\Attribute]
class ApiRequestBody
{
    public function __construct(
        public array $properties = [],
        public array $required = [],
    ) {}
}
