<?php

namespace App\Screens;

use App\Installers\Contracts\InstallerInterface;
use App\Theme;
use LaravelZero\Framework\Commands\Command;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\pause;
use function Laravel\Prompts\spin;

class UpdateScreen
{
    /** @param array<string, InstallerInterface> $installers */
    public function __construct(
        private readonly array $installers,
    ) {}

    public function render(Command $command): void
    {
        $c = Theme::PRIMARY;
        $toolConfig = config('tools');
        $installed = array_keys(array_filter(
            $this->installers,
            fn ($installer) => $installer->isInstalled(),
        ));

        if (empty($installed)) {
            $command->line("  <fg=$c>No tools are currently installed.</>");
            $command->newLine();
            pause();

            return;
        }

        $updates = spin(
            callback: function () use ($installed) {
                $results = [];
                foreach ($installed as $key) {
                    $results[$key] = $this->installers[$key]->checkUpdate();
                }

                return $results;
            },
            message: 'Checking for updates...',
        );

        $command->newLine();

        $rows = [];
        $updatable = [];

        foreach ($installed as $key) {
            $name = $toolConfig[$key]['name'] ?? $key;
            $update = $updates[$key];

            $statusLabel = match (true) {
                $update['hasUpdate'] => "⬆  Update available",
                $update['current'] !== null => "✓  Up to date",
                default => "–  Unknown",
            };

            $statusColor = match (true) {
                $update['hasUpdate'] => $c,
                $update['current'] !== null => 'green',
                default => 'gray',
            };

            $rows[] = [
                'name'    => $name,
                'current' => $update['current'] ?? '–',
                'latest'  => $update['latest'] ?? '–',
                'status'  => $statusLabel,
                'color'   => $statusColor,
            ];

            if ($update['hasUpdate']) {
                $updatable[] = $key;
            }
        }

        $this->renderTable($command, $rows);

        if (empty($updatable)) {
            $command->line("  <fg=green;options=bold>✓ All tools are up to date.</>");
            $command->newLine();
            pause();

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
        $command->line("  <fg=$c;options=bold>Updates completed</>");
        $command->newLine();
        pause();
    }

    /** @param array<int, array{name: string, current: string, latest: string, status: string, color: string}> $rows */
    private function renderTable(Command $command, array $rows): void
    {
        $c = Theme::PRIMARY;

        $colWidths = [
            'name'    => max(4, ...array_map(fn ($r) => mb_strlen($r['name']), $rows)),
            'current' => max(9, ...array_map(fn ($r) => mb_strlen($r['current']), $rows)),
            'latest'  => max(6, ...array_map(fn ($r) => mb_strlen($r['latest']), $rows)),
            'status'  => max(6, ...array_map(fn ($r) => mb_strlen($r['status']), $rows)),
        ];

        $pad = fn (string $val, int $width) => $val . str_repeat(' ', max(0, $width - mb_strlen($val)));

        $top    = "  <fg=$c>┌─" . str_repeat('─', $colWidths['name'])    . "─┬─" . str_repeat('─', $colWidths['current']) . "─┬─" . str_repeat('─', $colWidths['latest']) . "─┬─" . str_repeat('─', $colWidths['status']) . "─┐</>";
        $div    = "  <fg=$c>├─" . str_repeat('─', $colWidths['name'])    . "─┼─" . str_repeat('─', $colWidths['current']) . "─┼─" . str_repeat('─', $colWidths['latest']) . "─┼─" . str_repeat('─', $colWidths['status']) . "─┤</>";
        $bottom = "  <fg=$c>└─" . str_repeat('─', $colWidths['name'])    . "─┴─" . str_repeat('─', $colWidths['current']) . "─┴─" . str_repeat('─', $colWidths['latest']) . "─┴─" . str_repeat('─', $colWidths['status']) . "─┘</>";

        $headerLine = "  <fg=$c>│</> <options=bold>" . $pad('Tool', $colWidths['name']) . "</> <fg=$c>│</> <options=bold>" . $pad('Installed', $colWidths['current']) . "</> <fg=$c>│</> <options=bold>" . $pad('Latest', $colWidths['latest']) . "</> <fg=$c>│</> <options=bold>" . $pad('Status', $colWidths['status']) . "</> <fg=$c>│</>";

        $command->line($top);
        $command->line($headerLine);
        $command->line($div);

        foreach ($rows as $row) {
            $line = "  <fg=$c>│</> " . $pad($row['name'], $colWidths['name'])
                . " <fg=$c>│</> " . $pad($row['current'], $colWidths['current'])
                . " <fg=$c>│</> " . $pad($row['latest'], $colWidths['latest'])
                . " <fg=$c>│</> <fg={$row['color']}>" . $pad($row['status'], $colWidths['status']) . "</>"
                . " <fg=$c>│</>";
            $command->line($line);
        }

        $command->line($bottom);
    }
}
