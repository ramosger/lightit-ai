<?php

use App\Configurators\ClaudeCodeConfigurator;
use App\Support\JsonMerger;

beforeEach(function () {
    $this->tmpDir = sys_get_temp_dir() . '/claude-test-' . uniqid();
    mkdir($this->tmpDir, 0755, true);
    $_SERVER['HOME'] = $this->tmpDir;

    $this->configurator = new ClaudeCodeConfigurator(new JsonMerger());
});

afterEach(function () {
    $files = [
        $this->tmpDir . '/.claude/settings.json',
        $this->tmpDir . '/.claude/CLAUDE.md',
    ];
    foreach ($files as $f) {
        if (file_exists($f)) {
            unlink($f);
        }
    }
    @rmdir($this->tmpDir . '/.claude');
    @rmdir($this->tmpDir);
});

it('creates settings.json with engram mcp block when file does not exist', function () {
    $result = $this->configurator->configureEngram();

    expect($result)->toBeTrue();

    $path = $this->tmpDir . '/.claude/settings.json';
    expect(file_exists($path))->toBeTrue();

    $data = json_decode(file_get_contents($path), true);
    expect($data['mcpServers']['engram']['command'])->toBe('engram')
        ->and($data['mcpServers']['engram']['args'])->toBe(['mcp']);
});

it('merges engram into existing settings without overwriting other keys', function () {
    $claudeDir = $this->tmpDir . '/.claude';
    mkdir($claudeDir, 0755, true);
    file_put_contents($claudeDir . '/settings.json', json_encode([
        'theme'      => 'dark',
        'mcpServers' => ['other' => ['command' => 'other-server']],
    ]));

    $this->configurator->configureEngram();

    $data = json_decode(file_get_contents($claudeDir . '/settings.json'), true);

    expect($data['theme'])->toBe('dark')
        ->and($data['mcpServers']['other']['command'])->toBe('other-server')
        ->and($data['mcpServers']['engram']['command'])->toBe('engram');
});

it('appends memory instructions to CLAUDE.md when absent', function () {
    $result = $this->configurator->configureMemoryInstructions();

    expect($result)->toBeTrue();

    $path    = $this->tmpDir . '/.claude/CLAUDE.md';
    $content = file_get_contents($path);

    expect($content)->toContain('<!-- lightit-ai:engram -->')
        ->and($content)->toContain('mem_save');
});

it('does not duplicate memory instructions when run twice', function () {
    $this->configurator->configureMemoryInstructions();
    $this->configurator->configureMemoryInstructions();

    $path    = $this->tmpDir . '/.claude/CLAUDE.md';
    $content = file_get_contents($path);

    expect(substr_count($content, '<!-- lightit-ai:engram -->'))->toBe(1);
});

it('preserves existing CLAUDE.md content when appending', function () {
    $claudeDir = $this->tmpDir . '/.claude';
    mkdir($claudeDir, 0755, true);
    file_put_contents($claudeDir . '/CLAUDE.md', "# My existing instructions\n\nDo not remove this.\n");

    $this->configurator->configureMemoryInstructions();

    $content = file_get_contents($claudeDir . '/CLAUDE.md');

    expect($content)->toContain('My existing instructions')
        ->and($content)->toContain('Do not remove this.')
        ->and($content)->toContain('<!-- lightit-ai:engram -->');
});
