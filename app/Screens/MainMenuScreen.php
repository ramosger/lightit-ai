<?php

namespace App\Screens;

use App\Logo;
use LaravelZero\Framework\Commands\Command;

use function Laravel\Prompts\select;

class MainMenuScreen
{
    public function __construct(
        private readonly InstallScreen $install,
        private readonly UninstallScreen $uninstall,
        private readonly UpdateScreen $update,
        private readonly EngramScreen $engram,
    ) {}

    public function render(Command $command): void
    {
        while (true) {
            // Re-render logo and menu each loop iteration for a clean feel
            $command->line(Logo::render());

            $choice = select(
                label: 'What would you like to do?',
                options: [
                    'install' => '📦  Install tools',
                    'uninstall' => '🗑   Uninstall tools',
                    'update' => '🔄  Check for updates',
                    'engram' => '🧠  Engram memory',
                    'exit' => '✖   Exit',
                ],
            );

            $command->newLine();

            if ($choice === 'exit') {
                $command->line('  <fg=gray>Goodbye!</>');
                $command->newLine();

                return;
            }

            match ($choice) {
                'install' => $this->install->render($command),
                'uninstall' => $this->uninstall->render($command),
                'update' => $this->update->render($command),
                'engram' => $this->engram->render($command),
            };

            $command->newLine();
        }
    }
}
