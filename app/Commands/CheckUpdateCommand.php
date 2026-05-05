<?php

namespace App\Commands;

use LaravelZero\Framework\Commands\Command;

class CheckUpdateCommand extends Command
{
    protected $signature = 'check-update {key}';

    protected $description = 'Check for updates for a single installer (internal use)';

    public function isHidden(): bool
    {
        return true;
    }

    public function handle(): int
    {
        $key = $this->argument('key');
        $installers = app('installers');

        if (! isset($installers[$key])) {
            $this->line(json_encode(['current' => null, 'latest' => null, 'hasUpdate' => false]));

            return 0;
        }

        try {
            $result = $installers[$key]->checkUpdate();
        } catch (\Throwable) {
            $result = ['current' => null, 'latest' => null, 'hasUpdate' => false];
        }

        $this->line(json_encode($result));

        return 0;
    }
}
