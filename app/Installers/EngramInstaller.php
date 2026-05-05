<?php

namespace App\Installers;

use App\Installers\Concerns\FetchesGithubRelease;
use App\Installers\Contracts\InstallerInterface;
use App\Support\BrewRunner;
use App\Support\StateManager;

class EngramInstaller implements InstallerInterface
{
    use FetchesGithubRelease;
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

        $result = $this->brew->exec('brew install gentleman-programming/tap/engram');
        if ($result['exit'] !== 0) {
            if (str_contains($result['output'], 'xcode-select')) {
                $this->lastError = "Xcode Command Line Tools are required.\n  Run: xcode-select --install";
            } else {
                $this->lastError = $result['output'];
            }

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
        $current = $this->resolveInstalledVersion();
        $latest = $this->fetchLatestVersion();

        return [
            'current' => $current,
            'latest' => $latest,
            'hasUpdate' => $current !== null && $latest !== null && $current !== $latest,
        ];
    }

    private function resolveInstalledVersion(): ?string
    {
        $output = [];
        exec('engram --version 2>/dev/null', $output);
        $raw = trim(implode('', $output));
        $raw = preg_replace('/^engram\s+/i', '', $raw);

        return $raw !== '' ? ltrim($raw, 'v') : null;
    }

    private function fetchLatestVersion(): ?string
    {
        return $this->fetchGithubLatestTag('Gentleman-Programming', 'engram');
    }

    public function isInstalled(): bool
    {
        $output = [];
        exec('which engram 2>/dev/null', $output);

        return ! empty($output);
    }
}
