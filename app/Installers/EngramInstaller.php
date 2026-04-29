<?php

namespace App\Installers;

use App\Installers\Contracts\InstallerInterface;
use App\Support\BrewRunner;
use App\Support\StateManager;

class EngramInstaller implements InstallerInterface
{
    private ?string $lastError = null;

    public function __construct(
        private readonly BrewRunner $brew,
        private readonly StateManager $state,
    ) {}

    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    public function install(): bool
    {
        $this->brew->tap('gentleman-programming/tap');

        $result = $this->brew->exec('brew install engram');
        if ($result['exit'] !== 0) {
            $this->lastError = $result['output'];

            return false;
        }

        $version = $this->brew->installedVersion('engram') ?? 'unknown';
        $this->state->markInstalled('engram', $version);

        return true;
    }

    public function uninstall(): bool
    {
        if (! $this->brew->uninstall('engram')) {
            return false;
        }

        $this->state->markUninstalled('engram');

        return true;
    }

    public function checkUpdate(): array
    {
        $current = $this->brew->installedVersion('engram');
        $latest = $this->brew->latestVersion('engram');

        return [
            'current' => $current,
            'latest' => $latest,
            'hasUpdate' => $current !== null && $latest !== null && $current !== $latest,
        ];
    }

    public function isInstalled(): bool
    {
        $output = [];
        exec('which engram 2>/dev/null', $output);

        return ! empty($output);
    }
}
