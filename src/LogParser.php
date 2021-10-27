<?php

namespace LogParser;

use Illuminate\Support\Collection;
use Illuminate\Foundation\Auth\User;

class LogParser
{
    public function getLatestErrorForUser(User $user): ?LogItem
    {
        $files = $this->getFiles()->sortByDesc(function ($file) {
            return $file->getMTime();
        });

        foreach($files as $file) {
            if ($item = $this->findLatestUserErrorInFile($file, $user)) {
                return $item;
            }
        }

        return null;
    }

    public function getFiles($directory = null)
    {
        $directory = $directory ?: storage_path('logs');

        return collect(\File::allFiles($directory))
            ->filter(function ($file) {
                return $file->getExtension() === 'log';
            });
    }

    public function getItems($file)
    {
        $contents = $file->getContents();

        return preg_split('/(\[[\d\-\:\s]+\])/', $contents, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
    }

    public function parseFile($file): Collection
    {
        $items = $this->getItems($file);
        $grouped = collect();

        for($i = 0; $i < count($items); $i+=2) {
            $grouped->push(new LogItem($items[$i], $items[$i + 1]));
        }

        return $grouped;
    }

    public function findLatestUserErrorInFile($file, $user)
    {
        $items = $this->parseFile($file)->filter(function($item) use ($user) {
            return $item->user_id === $user->id;
        });

        if (! $items->count()) {
            return null;
        }

        return $items->sortByDesc(function ($item) {
            return $item->logged_at->getTimestamp();
        })->first();
    }
}
