<?php

namespace App\Installers;

use App\Installers\Contracts\InstallerInterface;
use App\Support\BrewRunner;
use App\Support\StateManager;

class EngramInstaller implements InstallerInterface
{
    public function __construct(
        private readonly BrewRunner $brew,
        private readonly StateManager $state,
    ) {}

    public function install(): bool
    {
        $this->brew->tap('gentleman-programming/tap');

        if (! $this->brew->install('engram')) {
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
        return $this->brew->isInstalled('engram');
    }
}
