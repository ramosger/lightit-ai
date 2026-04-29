<?php

namespace App\Screens;

use App\Prompts\BackableSelectPrompt;
use App\Theme;
use LaravelZero\Framework\Commands\Command;

use function Laravel\Prompts\text;

class EngramScreen
{
    public function render(Command $command): void
    {
        $prompt = new BackableSelectPrompt(
            label: 'Engram — what would you like to do?',
            options: [
                'tui' => 'Launch Engram TUI (interactive memory browser)',
                'search' => 'Search memories',
                'stats' => 'Memory statistics',
            ],
            hint: Theme::NAV_HINT_SUB,
        );

        $action = $prompt->prompt();

        if ($prompt->cancelled) {
            return;
        }

        match ($action) {
            'tui' => $this->launchTui($command),
            'search' => $this->search($command),
            'stats' => $this->stats($command),
        };

    }

    private function launchTui(Command $command): void
    {
        $c = Theme::PRIMARY;
        $command->newLine();
        $command->line("  <fg=$c>Launching Engram TUI — press q or Ctrl+C to return...</>");
        $command->newLine();
        passthru('engram tui');
    }

    private function search(Command $command): void
    {
        $query = text(
            label: 'Search query',
            required: true,
        );

        $command->newLine();
        passthru('engram search '.escapeshellarg($query));
    }

    private function stats(Command $command): void
    {
        $command->newLine();
        passthru('engram stats');
    }
}
