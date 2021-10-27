<?php

namespace LogParser\Facades;

use Illuminate\Support\Facades\Facade;

class LogParser extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'log_parser';
    }
}
