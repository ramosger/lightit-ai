<?php

namespace App\Installers;

use App\Installers\Contracts\InstallerInterface;
use App\Support\StateManager;

class PaoInstaller implements InstallerInterface
{
    private ?string $lastError = null;

    public function __construct(
        private readonly StateManager $state,
    ) {}

    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    public function install(): bool
    {
        $output = [];
        $exit = 0;
        exec('composer global require nunomaduro/pao 2>&1', $output, $exit);

        if ($exit !== 0) {
            $this->lastError = implode("\n", $output);

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
            if (preg_match('/latest\s*:\s*v?([\d.]+)/', $line, $m)) {
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
        if (! empty($output)) {
            return true;
        }

        $output = [];
        exec('composer global show nunomaduro/pao 2>/dev/null', $output);

        return ! empty($output);
    }

    private function resolveVersion(): string
    {
        $output = [];
        exec('composer global show nunomaduro/pao 2>/dev/null', $output);
        foreach ($output as $line) {
            if (preg_match('/^versions\s*:\s*\*?\s*v?([\d.]+)/', $line, $m)) {
                return $m[1];
            }
        }

        return 'unknown';
    }
}
