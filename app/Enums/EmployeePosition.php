<?php

namespace App\Enums;

enum EmployeePosition: string
{
    case Developer = 'Developer';
    case Manager = 'Manager';
    case Engineer = 'Engineer';
    case Designer = 'Designer';
}
