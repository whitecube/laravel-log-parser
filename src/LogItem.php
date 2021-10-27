<?php

namespace LogParser;

use \Carbon\Carbon;
use JsonSerializable;

class LogItem implements JsonSerializable
{
    public bool $parsed = false;
    public string $raw_data;
    public Carbon $logged_at;
    public ?string $app_env = null;
    public ?string $log_level = null;
    public ?string $message = null;
    public ?string $user_id = null;
    protected ?array $stacktrace = null;

    public function __construct(string $logged_at, string $raw_data)
    {
        $this->raw_data = trim($raw_data);
        $this->extractUserId();
        $this->setLoggedAt($logged_at);
    }

    public function setLoggedAt(string $logged_at)
    {
        $this->logged_at = Carbon::createFromFormat('[Y-m-d H:i:s]', $logged_at);
    }

    public function extractUserId()
    {
        preg_match('/ {"userId":"([a-z\d-]+?)","exception":"\[object\] \(/', $this->raw_data, $results);

        if (! count($results)) {
            return;
        }

        $this->user_id = $results[1];
    }

    public function parse()
    {
        $this->parsed = true;

        $exploded = explode('[stacktrace]', $this->raw_data, 2);

        if (count($exploded) > 1) {
            $this->parseStacktrace($exploded[1], 2);
        }

        $exploded = explode('.', $exploded[0], 2);
        $this->app_env = $exploded[0];

        $exploded = explode(': ', $exploded[1], 2);
        $this->log_level = $exploded[0];

        $exploded = explode(' {"userId":"'.$this->user_id.'","exception":"[object] ', $exploded[1], 2);
        $this->message = $exploded[0];
        array_unshift($this->stacktrace, trim($exploded[1]));
    }

    protected function parseStacktrace($trace)
    {
        $exploded = explode(PHP_EOL, $trace);
        array_shift($exploded);
        array_pop($exploded);
        array_pop($exploded);
        $this->stacktrace = $exploded;
    }

    public function jsonSerialize()
    {
        if (! $this->parsed) {
            $this->parse();
        }

        return [
            'app_env' => $this->app_env,
            'logged_at' => $this->logged_at,
            'log_level' => $this->log_level,
            'message' => $this->message,
            'stacktrace' => $this->stacktrace
        ];
    }
}
