<?php

namespace App\Commands;

use App\Support\ParallelUpdateChecker;
use LaravelZero\Framework\Commands\Command;

class CheckUpdatesSummaryCommand extends Command
{
    protected $signature = 'check-updates-summary';

    protected $description = 'Check all installed tools for updates and output a summary (internal use)';

    public function isHidden(): bool
    {
        return true;
    }

    public function handle(): int
    {
        $installers = app('installers');
        $installed = array_keys(array_filter(
            $installers,
            fn ($installer) => $installer->isInstalled(),
        ));

        if (empty($installed)) {
            $this->line(json_encode(['hasUpdates' => false, 'count' => 0]));

            return 0;
        }

        $results = (new ParallelUpdateChecker($installers))->run($installed);

        $updatable = array_filter($results, fn ($r) => $r['hasUpdate'] ?? false);

        $this->line(json_encode([
            'hasUpdates' => count($updatable) > 0,
            'count' => count($updatable),
        ]));

        return 0;
    }
}
