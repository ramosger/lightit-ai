<?php

namespace App\Installers;

use App\Installers\Contracts\InstallerInterface;
use App\Support\BrewRunner;
use App\Support\StateManager;

class LeannInstaller implements InstallerInterface
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
        if (! $this->ensureUv()) {
            return false;
        }

        foreach (['libomp', 'boost', 'protobuf', 'zeromq', 'pkgconf'] as $pkg) {
            $this->brew->install($pkg);
        }

        $venv = $this->venvPath();

        $output = [];
        $exit = 0;
        exec("uv venv {$venv} 2>&1", $output, $exit);
        if ($exit !== 0) {
            $this->lastError = implode("\n", $output);

            return false;
        }

        exec("uv pip install leann --python {$venv}/bin/python 2>&1", $output, $exit);
        if ($exit !== 0) {
            $this->lastError = implode("\n", $output);

            return false;
        }

        $this->state->markInstalled('leann', $this->resolveVersion());

        return true;
    }

    public function uninstall(): bool
    {
        $venv = $this->venvPath();
        $output = [];
        $exit = 0;
        exec("rm -rf {$venv} 2>&1", $output, $exit);

        if ($exit !== 0) {
            return false;
        }

        $this->state->markUninstalled('leann');

        return true;
    }

    public function checkUpdate(): array
    {
        $current = $this->resolveVersion();
        $venv = $this->venvPath();

        $output = [];
        exec("uv pip list --outdated --python {$venv}/bin/python 2>/dev/null", $output);
        $latest = null;
        foreach ($output as $line) {
            if (preg_match('/^leann\s+([\d.]+)\s+([\d.]+)/i', $line, $m)) {
                $latest = $m[2];
                break;
            }
        }

        if ($latest === null) {
            $json = @file_get_contents('https://pypi.org/pypi/leann/json');
            if ($json !== false) {
                $data = json_decode($json, true);
                $releases = array_keys($data['releases'] ?? []);
                if (! empty($releases)) {
                    usort($releases, 'version_compare');
                    $latest = end($releases);
                }
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
        $venv = $this->venvPath();
        if (! is_dir($venv)) {
            return false;
        }

        $output = [];
        exec("uv pip show leann --python {$venv}/bin/python 2>/dev/null", $output, $exit);

        return $exit === 0;
    }

    private function venvPath(): string
    {
        $home = $_SERVER['HOME'] ?? posix_getpwuid(posix_getuid())['dir'];

        return $home.'/.lightit-ai/leann-venv';
    }

    private function ensureUv(): bool
    {
        $output = [];
        exec('which uv 2>/dev/null', $output);

        if (! empty($output)) {
            return true;
        }

        $installOutput = [];
        $exit = 0;
        exec('curl -LsSf https://astral.sh/uv/install.sh | sh 2>&1', $installOutput, $exit);

        if ($exit !== 0) {
            $this->lastError = implode("\n", $installOutput);

            return false;
        }

        return true;
    }

    private function resolveVersion(): string
    {
        $venv = $this->venvPath();
        $output = [];
        exec("uv pip show leann --python {$venv}/bin/python 2>/dev/null", $output);
        foreach ($output as $line) {
            if (preg_match('/^Version:\s*(.+)/', $line, $m)) {
                return trim($m[1]);
            }
        }

        return 'unknown';
    }
}