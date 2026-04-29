<?php

namespace App\Screens;

use App\Installers\Contracts\InstallerInterface;
use App\Support\StateManager;
use App\Theme;
use LaravelZero\Framework\Commands\Command;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\spin;

class UpdateScreen
{
    /** @param array<string, InstallerInterface> $installers */
    public function __construct(
        private readonly array $installers,
        private readonly StateManager $state,
    ) {}

    public function render(Command $command): void
    {
        $c = Theme::PRIMARY;
        $toolConfig = config('tools');
        $installed = array_keys($this->state->getInstalled());

        if (empty($installed)) {
            $command->line("  <fg=$c>No tools are currently installed.</>");

            return;
        }

        $command->line("  <fg=$c;options=bold>Checking for updates...</>");
        $command->newLine();

        $rows = [];
        $updatable = [];

        foreach ($installed as $key) {
            $name = $toolConfig[$key]['name'] ?? $key;
            $update = spin(
                callback: fn () => $this->installers[$key]->checkUpdate(),
                message: "Checking {$name}...",
            );

            $statusLabel = match (true) {
                $update['hasUpdate'] => "<fg=$c>Update available</>",
                $update['current'] !== null && ! $update['hasUpdate'] => '<fg=green>Up to date</>',
                default => "<fg=$c>Unknown</>",
            };

            $rows[] = [
                $name,
                $update['current'] ?? '-',
                $update['latest'] ?? '-',
                $statusLabel,
            ];

            if ($update['hasUpdate']) {
                $updatable[] = $key;
            }
        }

        $command->table(
            headers: ['Tool', 'Installed', 'Latest', 'Status'],
            rows: $rows,
        );

        if (empty($updatable)) {
            $command->line('  <fg=green>All tools are up to date.</>');

            return;
        }

        $command->newLine();
        $apply = confirm(
            label: 'Apply available updates now?',
            default: true,
        );

        if (! $apply) {
            return;
        }

        $command->newLine();

        foreach ($updatable as $key) {
            $name = $toolConfig[$key]['name'] ?? $key;

            $success = spin(
                callback: fn () => $this->installers[$key]->install(),
                message: "Updating {$name}...",
            );

            $command->line($success
                ? "  <fg=green>✓ {$name} updated.</>"
                : "  <fg=red>✗ Failed to update {$name}.</>");
        }

        $command->newLine();
        $command->line("  <fg=$c;options=bold>Updates complete.</>");
    }
}
