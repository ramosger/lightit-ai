<?php

namespace App\Screens;

use App\Configurators\ClaudeCodeConfigurator;
use App\Installers\Contracts\InstallerInterface;
use App\Support\StateManager;
use App\Prompts\BackableMultiSelectPrompt;
use App\Theme;
use LaravelZero\Framework\Commands\Command;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\pause;
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
        $c = Theme::PRIMARY;
        $toolConfig = config('tools');
        $choices = [];
        $alreadyInstalled = [];

        foreach ($this->installers as $key => $installer) {
            $name = $toolConfig[$key]['name'];
            if ($installer->isInstalled() || $this->state->isInstalled($key)) {
                $alreadyInstalled[$key] = $name;
            } else {
                $choices[$key] = $name.' — '.$toolConfig[$key]['description'];
            }
        }

        if (empty($choices)) {
            $command->line("  <fg=$c;options=bold>All tools are already installed</>");
            $command->newLine();
            pause();

            return;
        }

        do {
            passthru('clear');

            if ($alreadyInstalled) {
                $command->line("  <fg=$c;options=bold>Already installed</>");
                foreach ($alreadyInstalled as $name) {
                    $command->line("  <fg=green>✓</> <fg=$c>{$name}</>");
                }
                $command->newLine();
            }

            $prompt = new BackableMultiSelectPrompt(
                label: 'Which Stack tools would you like to install?',
                options: $choices,
                hint: Theme::NAV_HINT_MULTI,
            );

            $selected = $prompt->prompt();

            if ($prompt->cancelled) {
                return;
            }
        } while (empty($selected));

        $engramInstaller = $this->installers['engram'] ?? null;
        $engramAlreadyInstalled = $engramInstaller
            && ($engramInstaller->isInstalled() || $this->state->isInstalled('engram'));

        $configClaude = ! $engramAlreadyInstalled && confirm(
            label: 'Configure Claude Code with Engram MCP? (non-destructive — merges with your existing config)',
            default: true,
        );

        $command->newLine();
        $anyFailed = false;

        foreach ($selected as $key) {
            $installer = $this->installers[$key];
            $name = $toolConfig[$key]['name'];

            $success = spin(
                callback: fn () => $installer->install(),
                message: "Installing {$name}...",
            );

            if ($success) {
                $command->line("  <fg=green>✓ {$name} installed successfully.</>");
            } else {
                $anyFailed = true;
                $command->line("  <fg=red>✗ {$name} installation failed.</>");
                $error = $installer->getLastError();
                if ($error) {
                    $clean = preg_replace('/\x1b\[[0-9;]*[A-Za-z]/', '', $error);
                    foreach (explode("\n", trim($clean)) as $errorLine) {
                        $errorLine = trim($errorLine);
                        if ($errorLine === '') {
                            continue;
                        }
                        $safe = htmlspecialchars($errorLine, ENT_XML1);
                        $command->line("    <fg=red>{$safe}</>");
                    }
                }
            }
        }

        if ($configClaude) {
            $command->newLine();

            $mcpOk = spin(
                callback: fn () => $this->claudeConfigurator->configureEngram(),
                message: 'Configuring Claude Code — MCP server...',
            );
            $command->line($mcpOk
                ? '  <fg=green>✓ Engram MCP server added to ~/.claude/settings.json</>'
                : '  <fg=red>✗ Failed to configure ~/.claude/settings.json</>');

            $mdOk = spin(
                callback: fn () => $this->claudeConfigurator->configureMemoryInstructions(),
                message: 'Configuring Claude Code — CLAUDE.md...',
            );
            $command->line($mdOk
                ? '  <fg=green>✓ Memory instructions added to ~/.claude/CLAUDE.md</>'
                : '  <fg=red>✗ Failed to update ~/.claude/CLAUDE.md</>');

            if ($mcpOk && $mdOk) {
                $this->state->markClaudeCodeConfigured();
            }
        }

        $command->newLine();
        if (! $anyFailed) {
            $command->line("  <fg=$c;options=bold>Installation complete!</>");
        }
        $command->newLine();
        pause();
    }
}
