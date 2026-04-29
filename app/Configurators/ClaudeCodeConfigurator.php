<?php

namespace App\Configurators;

use App\Support\JsonMerger;

class ClaudeCodeConfigurator
{
    private string $claudeDir;

    public function __construct(
        private readonly JsonMerger $merger,
    ) {
        $home = $_SERVER['HOME'] ?? posix_getpwuid(posix_getuid())['dir'];
        $this->claudeDir = $home.'/.claude';
    }

    /**
     * Merge Engram MCP server entry into ~/.claude/settings.json.
     * Never touches any existing keys.
     */
    public function configureEngram(): bool
    {
        $settingsPath = $this->claudeDir.'/settings.json';

        return $this->merger->mergeIntoFile($settingsPath, [
            'mcpServers' => [
                'engram' => [
                    'command' => 'engram',
                    'args' => ['mcp'],
                ],
            ],
        ]);
    }

    /**
     * Append Engram memory instructions to ~/.claude/CLAUDE.md.
     * Idempotent: skips if the lightit-ai:engram marker is already present.
     */
    public function configureMemoryInstructions(): bool
    {
        $claudeMdPath = $this->claudeDir.'/CLAUDE.md';
        $marker = '<!-- lightit-ai:engram -->';

        if (file_exists($claudeMdPath) && str_contains(file_get_contents($claudeMdPath), $marker)) {
            return true; // Already configured
        }

        $block = <<<MD


{$marker}
## Memory
You have access to Engram persistent memory via MCP tools (mem_save, mem_search, mem_session_summary, etc.).
- Save proactively after significant work — don't wait to be asked.
- After any compaction or context reset, call `mem_context` to recover session state before continuing.
<!-- /lightit-ai:engram -->
MD;

        $dir = $this->claudeDir;
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        return file_put_contents($claudeMdPath, $block, FILE_APPEND) !== false;
    }

    public function isEngramConfigured(): bool
    {
        $settingsPath = $this->claudeDir.'/settings.json';
        if (! file_exists($settingsPath)) {
            return false;
        }
        $data = json_decode(file_get_contents($settingsPath), true);

        return isset($data['mcpServers']['engram']);
    }
}
