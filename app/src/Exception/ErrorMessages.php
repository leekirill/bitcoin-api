<?php

namespace App\Exception;
class ErrorMessages
{
    public const UNDEFINED_ERROR = 'Undefined error';
    public const INVALID_PARAMETERS = 'Invalid parameters';
    public const RANGE = [
        'INVALID_RANGE' => 'Invalid range',
        'BLANK' => '`range=` parameter cannot be empty',
        'INVALID_VALUE' => 'Invalid `range=` value. Only `1h` or `24h` are allowed'
    ];
    public const FROM = [
        'INVALID_FORMAT' => 'Invalid date format `from=`. The expected format is 2025-01-01T00:00:00Z',
        'BLANK' => 'The `from` parameter cannot be empty'
    ];
    public const TO = [
        'INVALID_FORMAT' => 'Invalid date format `to=`. The expected format is 2025-01-01T00:00:00Z',
        'BLANK' => 'The `to` parameter cannot be empty'
    ];
    public const BLANK_ALL = 'Please provide either `range=` or `from=&to=` parameters';
    public const SAME_TIME = '`range=` and `from=&to=` parameters cannot be used at the same time';
}
