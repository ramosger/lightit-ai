<?php

namespace App\Installers;

use App\Installers\Contracts\InstallerInterface;
use App\Support\StateManager;

class PaoInstaller implements InstallerInterface
{
    public function __construct(
        private readonly StateManager $state,
    ) {}

    public function install(): bool
    {
        $output = [];
        $exit = 0;
        exec('composer global require nunomaduro/pao 2>&1', $output, $exit);

        if ($exit !== 0) {
            return false;
        }

        $this->state->markInstalled('pao', $this->resolveVersion());

        return true;
    }

    public function uninstall(): bool
    {
        $output = [];
        $exit = 0;
        exec('composer global remove nunomaduro/pao 2>&1', $output, $exit);

        if ($exit !== 0) {
            return false;
        }

        $this->state->markUninstalled('pao');

        return true;
    }

    public function checkUpdate(): array
    {
        $current = $this->resolveVersion();

        $output = [];
        exec('composer global show nunomaduro/pao --latest 2>/dev/null', $output);
        $latest = null;
        foreach ($output as $line) {
            if (preg_match('/latest\s*:\s*([\d.]+)/', $line, $m)) {
                $latest = $m[1];
                break;
            }
        }

        return [
            'current' => $current ?: null,
            'latest' => $latest,
            'hasUpdate' => $current !== null && $latest !== null && $current !== $latest,
        ];
    }

    public function isInstalled(): bool
    {
        $output = [];
        exec('which pao 2>/dev/null', $output);

        return ! empty($output);
    }

    private function resolveVersion(): string
    {
        $output = [];
        exec('pao --version 2>/dev/null', $output);
        $raw = trim(implode('', $output));

        return preg_replace('/^pao\s+/i', '', $raw) ?: 'unknown';
    }
}
