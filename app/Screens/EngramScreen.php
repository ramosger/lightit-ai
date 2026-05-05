<?php

namespace App\Screens;

use App\Prompts\BackableSelectPrompt;
use App\Theme;
use LaravelZero\Framework\Commands\Command;

class EngramScreen
{
    public function render(Command $command): void
    {
        while (true) {
            passthru('clear');
            $prompt = new BackableSelectPrompt(
                label: 'What would you like to do?',
                options: [
                    'tui' => 'Launch Engram TUI',
                    'stats' => 'Memory statistics',
                ],
                hint: Theme::NAV_HINT_SUB,
            );

            $action = $prompt->prompt();

            if ($prompt->cancelled) {
                return;
            }

            match ($action) {
                'tui' => $this->launchTui(),
                'stats' => $this->stats($command),
            };
        }
    }

    private function launchTui(): void
    {
        $stty = trim((string) shell_exec('stty -g 2>/dev/null'));
        system('stty sane');
        system('tput rmcup');
        system('tput clear');

        $tty = ['file', '/dev/tty', 'r+'];
        $proc = proc_open('engram tui', [$tty, $tty, $tty], $pipes);
        if (\is_resource($proc)) {
            proc_close($proc);
        }

        system('tput smcup');
        if ($stty !== '') {
            system('stty '.escapeshellarg($stty));
        }
    }

    private function stats(Command $command): void
    {
        $command->newLine();
        passthru('engram stats');
        $command->newLine();
        $command->ask('Press enter to go back');
    }
}
