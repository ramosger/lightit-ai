<?php

namespace App\Support;

class StateManager
{
    private string $path;

    /** @var array<string, mixed> */
    private array $state;

    public function __construct()
    {
        $this->path = ($_SERVER['HOME'] ?? posix_getpwuid(posix_getuid())['dir']) . '/.lightit-ai/state.json';
        $this->load();
    }

    private function load(): void
    {
        if (! file_exists($this->path)) {
            $this->state = ['installed' => [], 'claudeCodeConfigured' => false];
            return;
        }

        $decoded = json_decode(file_get_contents($this->path), true);
        $this->state = is_array($decoded) ? $decoded : ['installed' => [], 'claudeCodeConfigured' => false];
    }

    public function save(): void
    {
        $dir = dirname($this->path);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($this->path, json_encode($this->state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    public function markInstalled(string $tool, string $version): void
    {
        $this->state['installed'][$tool] = [
            'version'     => $version,
            'installedAt' => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
        ];
        $this->save();
    }

    public function markUninstalled(string $tool): void
    {
        unset($this->state['installed'][$tool]);
        $this->save();
    }

    public function isInstalled(string $tool): bool
    {
        return isset($this->state['installed'][$tool]);
    }

    public function getInstalledVersion(string $tool): ?string
    {
        return $this->state['installed'][$tool]['version'] ?? null;
    }

    /** @return array<string, array<string, string>> */
    public function getInstalled(): array
    {
        return $this->state['installed'] ?? [];
    }

    public function isClaudeCodeConfigured(): bool
    {
        return (bool) ($this->state['claudeCodeConfigured'] ?? false);
    }

    public function markClaudeCodeConfigured(): void
    {
        $this->state['claudeCodeConfigured'] = true;
        $this->save();
    }
}
