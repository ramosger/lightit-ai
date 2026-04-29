<?php

namespace App\Support;

class BrewRunner
{
    public function tap(string $tap): bool
    {
        $result = $this->exec("brew tap {$tap}");

        return $result['exit'] === 0;
    }

    public function install(string $package): bool
    {
        $result = $this->exec("brew install {$package}");

        return $result['exit'] === 0;
    }

    public function uninstall(string $package): bool
    {
        $result = $this->exec("brew uninstall {$package}");

        return $result['exit'] === 0;
    }

    public function upgrade(string $package): bool
    {
        $result = $this->exec("brew upgrade {$package}");

        return $result['exit'] === 0;
    }

    public function isInstalled(string $package): bool
    {
        $result = $this->exec("brew list --formula {$package}");

        return $result['exit'] === 0;
    }

    public function installedVersion(string $package): ?string
    {
        $result = $this->exec("brew list --versions {$package}");
        if ($result['exit'] !== 0 || empty(trim($result['output']))) {
            return null;
        }
        $parts = explode(' ', trim($result['output']));

        return $parts[1] ?? null;
    }

    public function latestVersion(string $package): ?string
    {
        $result = $this->exec("brew info --json {$package}");
        if ($result['exit'] !== 0) {
            return null;
        }
        $info = json_decode($result['output'], true);

        return $info[0]['versions']['stable'] ?? null;
    }

    /** @return array{exit: int, output: string} */
    public function exec(string $cmd): array
    {
        $output = [];
        $exit = 0;
        exec($cmd.' 2>&1', $output, $exit);

        return ['exit' => $exit, 'output' => implode("\n", $output)];
    }
}
