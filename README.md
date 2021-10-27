# laravel-log-parser

WIP!!

This package allows you to parse your log files.

API:

```php
use \LogParser;

LogParser::getLatestErrorForUser(auth()->user()); // Gets the latest error logged for the authenticated user
LogParser::getFiles(); // Gets a collection of the log files in your logs directory. Optional: pass a directory path as an argument and it will scan that instead.
```

More to come soon.
