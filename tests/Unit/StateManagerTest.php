<?php

use App\Support\StateManager;

beforeEach(function () {
    // Point state to a temp file so tests don't pollute ~/.lightit-ai
    $this->tmpDir = sys_get_temp_dir().'/lightit-ai-test-'.uniqid();
    mkdir($this->tmpDir, 0755, true);
    $_SERVER['HOME'] = realpath($this->tmpDir) ?: $this->tmpDir;

    $this->manager = new StateManager;
});

afterEach(function () {
    $real = realpath($this->tmpDir) ?: $this->tmpDir;
    $stateDir = $real.'/.lightit-ai';
    $file = $stateDir.'/state.json';

    if (file_exists($file)) {
        unlink($file);
    }
    if (is_dir($stateDir)) {
        rmdir($stateDir);
    }
    if (is_dir($real)) {
        rmdir($real);
    }
});

it('reports tool as not installed initially', function () {
    expect($this->manager->isInstalled('engram'))->toBeFalse();
});

it('marks a tool as installed and persists it', function () {
    $this->manager->markInstalled('engram', '1.2.0');

    expect($this->manager->isInstalled('engram'))->toBeTrue()
        ->and($this->manager->getInstalledVersion('engram'))->toBe('1.2.0');

    // Re-load from disk
    $fresh = new StateManager;
    expect($fresh->isInstalled('engram'))->toBeTrue();
});

it('marks a tool as uninstalled', function () {
    $this->manager->markInstalled('rtk', '0.9.1');
    $this->manager->markUninstalled('rtk');

    expect($this->manager->isInstalled('rtk'))->toBeFalse();
});

it('tracks claude code configured flag', function () {
    expect($this->manager->isClaudeCodeConfigured())->toBeFalse();

    $this->manager->markClaudeCodeConfigured();

    expect($this->manager->isClaudeCodeConfigured())->toBeTrue();
});

it('returns all installed tools', function () {
    $this->manager->markInstalled('engram', '1.0.0');
    $this->manager->markInstalled('rtk', '0.9.0');

    expect($this->manager->getInstalled())->toHaveKeys(['engram', 'rtk']);
});
