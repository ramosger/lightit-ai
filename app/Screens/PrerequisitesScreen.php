<?php

namespace App\Screens;

use App\Prompts\BackableSelectPrompt;
use App\Theme;
use LaravelZero\Framework\Commands\Command;

class PrerequisitesScreen
{
    private const TOOLS = [
        'brew' => '/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"',
        'curl' => 'brew install curl',
    ];

    private const DEPENDENCIES = [
        'composer' => ['composer --version', true,  'brew install composer  OR  https://getcomposer.org/download'],
        'uv'       => ['uv --version',       false, 'curl -LsSf https://astral.sh/uv/install.sh | sh'],
    ];

    public function render(Command $command): bool
    {
        $c = Theme::PRIMARY;
        $missing = [];

        $command->line("  <fg=$c;options=bold>Tools</>");

        foreach (self::TOOLS as $binary => $hint) {
            $found = $this->isOnPath($binary);
            if ($found) {
                $command->line("  {$binary}: <fg=green>found</>");
            } else {
                $missing[$binary] = $hint;
                $command->line("  {$binary}: <fg=red>not found</>");
            }
        }

        $command->newLine();

        $command->line("  <fg=$c;options=bold>Dependencies</>");

        foreach (self::DEPENDENCIES as $binary => [$versionCmd, $required, $hint]) {
            $found = $this->isOnPath($binary);

            if ($found) {
                $version = $this->detectVersion($versionCmd);
                $label   = $version !== '' ? $version : 'found';
                $suffix  = $required ? '' : ' <fg=gray>(optional)</>';
                $command->line("  {$binary}: <fg=green>{$label}</>{$suffix}");
            } else {
                $suffix = $required ? '' : ' <fg=gray>(optional)</>';
                $command->line("  {$binary}: <fg=red>not found</>{$suffix}");
                if ($required) {
                    $missing[$binary] = $hint;
                }
            }
        }

        $canContinue = empty($missing);

        if (! $canContinue) {
            $command->newLine();
            $command->line("  <fg=yellow;options=bold>Install the following before continuing:</>");
            $command->newLine();
            foreach ($missing as $binary => $hint) {
                $command->line("  <fg=$c>{$binary}</>  →  <fg=white>{$hint}</>");
            }
        }

        $command->newLine();

        $options = $canContinue
            ? ['continue' => 'Continue', 'back' => 'Back']
            : ['back' => 'Back'];

        $prompt = new BackableSelectPrompt(
            label: $canContinue ? '' : '  ✗ Missing required dependencies',
            options: $options,
            hint: Theme::NAV_HINT_SUB,
        );

        $choice = $prompt->prompt();

        return ! $prompt->cancelled && $choice === 'continue';
    }

    private function isOnPath(string $binary): bool
    {
        return trim(shell_exec("which {$binary} 2>/dev/null") ?? '') !== '';
    }

    private function detectVersion(string $cmd): string
    {
        $output = trim(shell_exec("{$cmd} 2>/dev/null") ?? '');
        if ($output === '') {
            return '';
        }

        if (preg_match('/(\d+\.\d+(?:\.\d+)?)/', $output, $m)) {
            return $m[1];
        }

        return '';
    }
}
