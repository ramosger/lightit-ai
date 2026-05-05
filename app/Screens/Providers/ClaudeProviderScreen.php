<?php

namespace App\Screens\Providers;

use App\Prompts\BackableSelectPrompt;
use App\Theme;
use LaravelZero\Framework\Commands\Command;

class ClaudeProviderScreen
{
    public function render(Command $command): void
    {
        $prompt = new BackableSelectPrompt(
            label: 'Claude — extras',
            options: [
                'statusline' => 'Configure statusline (ccstatusline)',
            ],
            hint: Theme::NAV_HINT_SUB,
        );

        $action = $prompt->prompt();

        if ($prompt->cancelled) {
            return;
        }

        match ($action) {
            'statusline' => $this->configureStatusline($command),
        };
    }

    private function configureStatusline(Command $command): void
    {
        passthru('clear');
        $command->line('  Launching ccstatusline configurator...');
        $command->newLine();
        passthru('npx -y ccstatusline@latest');
    }
}
