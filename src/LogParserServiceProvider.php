<?php

namespace LogParser;

use Illuminate\Support\ServiceProvider;

class LogParserServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('log_parser', function($app) {
            return new LogParser();
        });
    }
}
