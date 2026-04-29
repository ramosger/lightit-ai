<?php

namespace App\Screens;

use App\Installers\Contracts\InstallerInterface;
use App\Support\StateManager;
use LaravelZero\Framework\Commands\Command;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\spin;

class UninstallScreen
{
    /** @param array<string, InstallerInterface> $installers */
    public function __construct(
        private readonly array $installers,
        private readonly StateManager $state,
    ) {}

    public function render(Command $command): void
    {
        $toolConfig = config('tools');
        $installed = $this->state->getInstalled();

        if (empty($installed)) {
            $command->line('  <fg=yellow>No tools are currently installed.</>');

            return;
        }

        $choices = [];
        foreach (array_keys($installed) as $key) {
            $name = $toolConfig[$key]['name'] ?? $key;
            $version = $installed[$key]['version'] ?? '?';
            $choices[$key] = "{$name} (v{$version})";
        }

        $selected = multiselect(
            label: 'Which tools would you like to uninstall?',
            options: $choices,
            hint: 'Space to toggle, Enter to confirm',
        );

        if (empty($selected)) {
            $command->line('  <fg=yellow>No tools selected. Returning to menu.</>');

            return;
        }

        $confirmed = confirm(
            label: 'Are you sure you want to uninstall the selected tools?',
            default: false,
        );

        if (! $confirmed) {
            $command->line('  <fg=yellow>Uninstall cancelled.</>');

            return;
        }

        $command->newLine();

        foreach ($selected as $key) {
            $installer = $this->installers[$key];
            $name = $toolConfig[$key]['name'] ?? $key;

            $success = spin(
                callback: fn () => $installer->uninstall(),
                message: "Uninstalling {$name}...",
            );

            if ($success) {
                $command->line("  <fg=green>✓ {$name} uninstalled.</>");
            } else {
                $command->line("  <fg=red>✗ Failed to uninstall {$name}.</>");
            }
        }

        $command->newLine();
        $command->line('  <fg=white;options=bold>Done.</>');
    }
}
