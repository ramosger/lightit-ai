<?php

namespace App\Screens;

use LaravelZero\Framework\Commands\Command;

use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

class EngramScreen
{
    public function render(Command $command): void
    {
        $action = select(
            label:   'Engram — what would you like to do?',
            options: [
                'tui'    => '🧠  Launch Engram TUI (interactive memory browser)',
                'search' => '🔍  Search memories',
                'stats'  => '📊  Memory statistics',
                'back'   => '← Back to main menu',
            ],
        );

        match ($action) {
            'tui'    => $this->launchTui($command),
            'search' => $this->search($command),
            'stats'  => $this->stats($command),
            'back'   => null,
        };
    }

    private function launchTui(Command $command): void
    {
        $command->newLine();
        $command->line('  <fg=gray>Launching Engram TUI — press q or Ctrl+C to return...</>');
        $command->newLine();
        passthru('engram tui');
    }

    private function search(Command $command): void
    {
        $query = text(
            label:    'Search query',
            required: true,
        );

        $command->newLine();
        passthru('engram search ' . escapeshellarg($query));
    }

    private function stats(Command $command): void
    {
        $command->newLine();
        passthru('engram stats');
    }
}
