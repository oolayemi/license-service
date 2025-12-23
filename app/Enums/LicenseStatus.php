<?php

namespace App\Enums;

enum LicenseStatus: string
{
    case VALID = 'valid';
    case SUSPENDED = 'suspended';
    case CANCELLED = 'cancelled';
    case EXPIRED = 'expired';
}
