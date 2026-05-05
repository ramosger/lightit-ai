<?php

namespace App\Screens;

use App\Installers\Contracts\InstallerInterface;
use App\Prompts\BackableMultiSelectPrompt;
use App\Support\StateManager;
use App\Theme;
use LaravelZero\Framework\Commands\Command;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\spin;

class UninstallScreen
{
    public function __construct(
        private readonly array $installers,
        private readonly StateManager $state,
    ) {}

    public function render(Command $command): void
    {
        $c = Theme::PRIMARY;
        $toolConfig = config('tools');
        $tracked = $this->state->getInstalled();

        $choices = [];
        foreach ($this->installers as $key => $installer) {
            if (! isset($tracked[$key]) && ! $installer->isInstalled()) {
                continue;
            }
            $name = $toolConfig[$key]['name'] ?? $key;
            $version = $tracked[$key]['version'] ?? $installer->checkUpdate()['current'] ?? '?';
            $choices[$key] = "{$name} (v{$version})";
        }

        if (empty($choices)) {
            $command->line("  <fg=$c>No tools are currently installed.</>");

            return;
        }

        do {
            passthru('clear');
            $prompt = new BackableMultiSelectPrompt(
                label: 'Which tools would you like to uninstall?',
                options: $choices,
                hint: Theme::NAV_HINT_MULTI,
            );

            $selected = $prompt->prompt();

            if ($prompt->cancelled) {
                return;
            }
        } while (empty($selected));

        $confirmed = confirm(
            label: 'Are you sure you want to uninstall the selected tools?',
            default: false,
        );

        if (! $confirmed) {
            $command->line("  <fg=$c>Uninstall cancelled.</>");

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
        $command->line("  <fg=$c;options=bold>Done.</>");
    }
}
