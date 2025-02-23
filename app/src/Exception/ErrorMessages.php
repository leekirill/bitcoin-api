<?php

namespace App\Exception;

class ErrorMessages
{
    public const UNDEFINED_ERROR = 'Undefined error';
    public const INVALID_RANGE = 'Invalid range';
    public const INVALID_PARAMETERS = 'Invalid parameters';
    public const FROM = [
        'INVALID_FORMAT' => 'Invalid date format `from=`. The expected format is 2025-01-01T00:00:00Z',
        'BLANK' => 'The `from` parameter cannot be empty'
    ];
    public const TO = [
        'INVALID_FORMAT' => 'Invalid date format `to=`. The expected format is 2025-01-01T00:00:00Z',
        'BLANK' => 'The `to` parameter cannot be empty'
    ];
    
}
