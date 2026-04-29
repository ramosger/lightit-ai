<?php

namespace App\Screens;

use App\Configurators\ClaudeCodeConfigurator;
use App\Installers\Contracts\InstallerInterface;
use App\Support\StateManager;
use LaravelZero\Framework\Commands\Command;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\spin;

class InstallScreen
{
    /** @param array<string, InstallerInterface> $installers */
    public function __construct(
        private readonly array $installers,
        private readonly StateManager $state,
        private readonly ClaudeCodeConfigurator $claudeConfigurator,
    ) {}

    public function render(Command $command): void
    {
        $toolConfig = config('tools');
        $choices    = [];

        foreach ($this->installers as $key => $installer) {
            $label          = $toolConfig[$key]['name'] . ' — ' . $toolConfig[$key]['description'];
            $choices[$key]  = $label;
        }

        $selected = multiselect(
            label:    'Which tools would you like to install?',
            options:  $choices,
            default:  array_keys($choices),
            hint:     'Space to toggle, Enter to confirm',
        );

        if (empty($selected)) {
            $command->line('  <fg=yellow>No tools selected. Returning to menu.</>');
            return;
        }

        $configClaude = confirm(
            label:   'Configure Claude Code with Engram MCP? (non-destructive — merges with your existing config)',
            default: true,
        );

        $command->newLine();
        $results = [];

        foreach ($selected as $key) {
            $installer = $this->installers[$key];
            $name      = $toolConfig[$key]['name'];

            if ($installer->isInstalled() && $this->state->isInstalled($key)) {
                $command->line("  <fg=gray>✓ {$name} already installed, skipping.</>");
                $results[$key] = 'skipped';
                continue;
            }

            $success = spin(
                callback: fn () => $installer->install(),
                message:  "Installing {$name}...",
            );

            if ($success) {
                $command->line("  <fg=green>✓ {$name} installed successfully.</>");
                $results[$key] = 'installed';
            } else {
                $command->line("  <fg=red>✗ {$name} installation failed.</>");
                $results[$key] = 'failed';
            }
        }

        if ($configClaude) {
            $command->newLine();

            $mcpOk = spin(
                callback: fn () => $this->claudeConfigurator->configureEngram(),
                message:  'Configuring Claude Code — MCP server...',
            );
            $command->line($mcpOk
                ? '  <fg=green>✓ Engram MCP server added to ~/.claude/settings.json</>'
                : '  <fg=red>✗ Failed to configure ~/.claude/settings.json</>');

            $mdOk = spin(
                callback: fn () => $this->claudeConfigurator->configureMemoryInstructions(),
                message:  'Configuring Claude Code — CLAUDE.md...',
            );
            $command->line($mdOk
                ? '  <fg=green>✓ Memory instructions added to ~/.claude/CLAUDE.md</>'
                : '  <fg=red>✗ Failed to update ~/.claude/CLAUDE.md</>');

            if ($mcpOk && $mdOk) {
                $this->state->markClaudeCodeConfigured();
            }
        }

        $command->newLine();
        $command->line('  <fg=white;options=bold>Installation complete!</>');
        $command->line('  <fg=gray>Restart Claude Code to activate Engram MCP.</>');
    }
}
