<?php

namespace App\Screens;

use App\Logo;
use App\Prompts\QuitableSelectPrompt;
use App\Theme;
use LaravelZero\Framework\Commands\Command;

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
            $command->line(Logo::render());

            $prompt = new QuitableSelectPrompt(
                label: 'Menu',
                options: [
                    'install' => 'Install Stack',
                    'update' => 'Update tools',
                    'uninstall' => 'Uninstall tools',
                    'engram' => 'Engram memory',
                    'exit' => 'Exit',
                ],
                hint: Theme::NAV_HINT,
            );

            $choice = $prompt->prompt();

            $command->newLine();

            if ($choice === 'exit' || $prompt->quitted) {
                passthru('clear');

                return;
            }

            match ($choice) {
                'install' => $this->install->render($command),
                'uninstall' => $this->uninstall->render($command),
                'update' => $this->update->render($command),
                'engram' => $this->engram->render($command),
            };

            passthru('clear');
        }
    }
}
