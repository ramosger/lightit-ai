<?php

namespace App\Screens;

use App\Installers\Contracts\InstallerInterface;
use App\Logo;
use App\Prompts\QuitableSelectPrompt;
use App\Theme;
use LaravelZero\Framework\Commands\Command;

class MainMenuScreen
{
    private ?array $updateSummary = null;
    private ?string $updateTmpFile = null;
    private bool $updateCheckLaunched = false;

    /** @param array<string, InstallerInterface> $installers */
    public function __construct(
        private readonly InstallScreen $install,
        private readonly UninstallScreen $uninstall,
        private readonly UpdateScreen $update,
        private readonly EngramScreen $engram,
        private readonly PrerequisitesScreen $prerequisites,
        private readonly array $installers,
    ) {}

    public function render(Command $command): void
    {
        system('tput smcup');

        $this->launchUpdateCheck();

        while (true) {
            system('tput clear');
            $command->line(Logo::render());

            $prompt = new QuitableSelectPrompt(
                label: '',
                options: $this->menuOptions(),
                hint: Theme::NAV_HINT,
            );

            if ($this->updateSummary === null && $this->updateTmpFile !== null) {
                $prompt->onTick(function () use ($prompt): bool {
                    if ($this->updateSummary !== null) {
                        return false;
                    }

                    $content = @file_get_contents($this->updateTmpFile ?? '');
                    if ($content === false || $content === '') {
                        return false;
                    }

                    $decoded = json_decode($content, true);
                    if (! is_array($decoded)) {
                        return false;
                    }

                    $this->updateSummary = $decoded;
                    @unlink($this->updateTmpFile);
                    $this->updateTmpFile = null;

                    $prompt->options['update'] = $this->buildUpdateLabel();

                    return true;
                });
            }

            $choice = $prompt->prompt();

            $command->newLine();

            if ($choice === 'exit' || $prompt->quitted) {
                system('tput rmcup');

                return;
            }

            if ($choice === 'install') {
                passthru('clear');
                $confirmed = $this->prerequisites->render($command);
                if ($confirmed) {
                    passthru('clear');
                    $this->install->render($command);
                }
                $this->resetUpdateCheck();
            } else {
                match ($choice) {
                    'uninstall' => $this->uninstall->render($command),
                    'update' => $this->update->render($command),
                    'engram' => $this->engram->render($command),
                };

                if ($choice === 'update') {
                    $this->resetUpdateCheck();
                }
            }
        }
    }

    private function menuOptions(): array
    {
        return [
            'install' => 'Install Stack',
            'update' => $this->buildUpdateLabel(),
            'uninstall' => 'Uninstall tools',
            'engram' => 'Engram memory',
            'exit' => 'Exit',
        ];
    }

    private function resetUpdateCheck(): void
    {
        if ($this->updateTmpFile !== null) {
            @unlink($this->updateTmpFile);
        }
        $this->updateSummary = null;
        $this->updateTmpFile = null;
        $this->updateCheckLaunched = false;
        $this->launchUpdateCheck();
    }

    private function launchUpdateCheck(): void
    {
        if ($this->updateCheckLaunched) {
            return;
        }

        $this->updateCheckLaunched = true;
        $this->updateTmpFile = tempnam(sys_get_temp_dir(), 'lightit_menu_upd_');

        $php = PHP_BINARY;
        $phar = \Phar::running(false);
        $app = $phar !== '' ? $phar : base_path('application');

        $cmd = sprintf('%s %s check-updates-summary > %s 2>/dev/null &',
            escapeshellarg($php),
            escapeshellarg($app),
            escapeshellarg($this->updateTmpFile),
        );

        exec($cmd);
    }

    private function buildUpdateLabel(): string
    {
        if ($this->updateSummary === null) {
            return 'Update tools';
        }

        if ($this->updateSummary['hasUpdates']) {
            $count = $this->updateSummary['count'];

            return "Update tools  ★  ({$count} available)";
        }

        return 'Update tools  ✓ up to date';
    }
}
