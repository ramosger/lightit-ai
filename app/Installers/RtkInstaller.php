<?php

namespace App\Installers;

use App\Installers\Contracts\InstallerInterface;
use App\Support\BrewRunner;
use App\Support\StateManager;

class RtkInstaller implements InstallerInterface
{
    public function __construct(
        private readonly BrewRunner $brew,
        private readonly StateManager $state,
    ) {}

    public function install(): bool
    {
        $success = $this->brew->install('rtk');

        if (! $success) {
            // Fallback: run official install script
            $output = [];
            $exit   = 0;
            exec('curl -fsSL https://raw.githubusercontent.com/rtk-ai/rtk/refs/heads/master/install.sh | sh 2>&1', $output, $exit);
            $success = $exit === 0;
        }

        if (! $success) {
            return false;
        }

        $version = $this->resolveVersion();
        $this->state->markInstalled('rtk', $version);

        return true;
    }

    public function uninstall(): bool
    {
        $result = $this->brew->exec('brew list --formula rtk');
        if ($result['exit'] === 0) {
            if (! $this->brew->uninstall('rtk')) {
                return false;
            }
        } else {
            // Installed via script — remove binary from known locations
            foreach (['/usr/local/bin/rtk', '/opt/homebrew/bin/rtk', $this->homeDir() . '/.local/bin/rtk'] as $bin) {
                if (file_exists($bin)) {
                    unlink($bin);
                }
            }
        }

        $this->state->markUninstalled('rtk');

        return true;
    }

    public function checkUpdate(): array
    {
        $current = $this->resolveVersion();
        $latest  = $this->brew->latestVersion('rtk');

        return [
            'current'   => $current ?: null,
            'latest'    => $latest,
            'hasUpdate' => $current !== null && $latest !== null && $current !== $latest,
        ];
    }

    public function isInstalled(): bool
    {
        $output = [];
        exec('which rtk 2>/dev/null', $output);
        return ! empty($output);
    }

    private function resolveVersion(): string
    {
        $output = [];
        exec('rtk --version 2>/dev/null', $output);
        $raw = trim(implode('', $output));
        // "rtk X.Y.Z" → "X.Y.Z"
        return preg_replace('/^rtk\s+/i', '', $raw) ?: 'unknown';
    }

    private function homeDir(): string
    {
        return $_SERVER['HOME'] ?? posix_getpwuid(posix_getuid())['dir'];
    }
}
