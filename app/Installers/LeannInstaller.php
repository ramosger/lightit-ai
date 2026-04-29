<?php

namespace App\Installers;

use App\Installers\Contracts\InstallerInterface;
use App\Support\StateManager;

class LeannInstaller implements InstallerInterface
{
    public function __construct(
        private readonly StateManager $state,
    ) {}

    public function install(): bool
    {
        $output = [];
        $exit = 0;
        exec('pip3 install leann-py 2>&1', $output, $exit);

        if ($exit !== 0) {
            // Try pip as fallback
            exec('pip install leann-py 2>&1', $output, $exit);
        }

        if ($exit !== 0) {
            return false;
        }

        $this->state->markInstalled('leann', $this->resolveVersion());

        return true;
    }

    public function uninstall(): bool
    {
        $output = [];
        $exit = 0;
        exec('pip3 uninstall leann-py -y 2>&1', $output, $exit);

        if ($exit !== 0) {
            exec('pip uninstall leann-py -y 2>&1', $output, $exit);
        }

        if ($exit !== 0) {
            return false;
        }

        $this->state->markUninstalled('leann');

        return true;
    }

    public function checkUpdate(): array
    {
        $current = $this->resolveVersion();

        $output = [];
        exec('pip3 index versions leann-py 2>/dev/null', $output);
        $latest = null;
        foreach ($output as $line) {
            if (preg_match('/Available versions:\s*([\d.]+)/', $line, $m)) {
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
        exec('python3 -c "import leann" 2>/dev/null', $output, $exit);

        return $exit === 0;
    }

    private function resolveVersion(): string
    {
        $output = [];
        exec('python3 -c "import leann; print(leann.__version__)" 2>/dev/null', $output);

        return trim(implode('', $output)) ?: 'unknown';
    }
}
