<?php

use App\Installers\Contracts\InstallerInterface;
use App\Support\StateManager;

beforeEach(function () {
    $this->tmpDir = sys_get_temp_dir().'/lightit-ai-test-'.uniqid();
    mkdir($this->tmpDir, 0755, true);
    $_SERVER['HOME'] = realpath($this->tmpDir) ?: $this->tmpDir;

    $this->state = new StateManager;
});

afterEach(function () {
    $real = realpath($this->tmpDir) ?: $this->tmpDir;
    $stateDir = $real.'/.lightit-ai';

    foreach (glob($stateDir.'/*') ?: [] as $file) {
        is_dir($file) ? rmdir($file) : unlink($file);
    }
    if (is_dir($stateDir)) {
        rmdir($stateDir);
    }
    if (is_dir($real)) {
        rmdir($real);
    }
});

function makeInstaller(bool $isInstalled, bool $installResult = true, ?string $lastError = null): InstallerInterface
{
    return new class($isInstalled, $installResult, $lastError) implements InstallerInterface {
        public function __construct(
            private bool $installed,
            private bool $result,
            private ?string $error,
        ) {}

        public function isInstalled(): bool { return $this->installed; }
        public function install(): bool { return $this->result; }
        public function uninstall(): bool { return true; }
        public function checkUpdate(): array { return ['current' => null, 'latest' => null, 'hasUpdate' => false]; }
        public function getLastError(): ?string { return $this->error; }
    };
}

it('classifies installer-detected tool as already installed', function () {
    $installer = makeInstaller(isInstalled: true);

    expect($installer->isInstalled())->toBeTrue();
});

it('classifies state-tracked tool as already installed even if binary check fails', function () {
    $this->state->markInstalled('rtk', '1.0.0');

    $installer = makeInstaller(isInstalled: false);

    $isInstalled = $installer->isInstalled() || $this->state->isInstalled('rtk');

    expect($isInstalled)->toBeTrue();
});

it('classifies tool as not installed when both binary and state are absent', function () {
    $installer = makeInstaller(isInstalled: false);

    $isInstalled = $installer->isInstalled() || $this->state->isInstalled('engram');

    expect($isInstalled)->toBeFalse();
});

it('captures error from failed installer', function () {
    $installer = makeInstaller(isInstalled: false, installResult: false, lastError: "ERROR: package not found\nPermission denied");

    $result = $installer->install();

    expect($result)->toBeFalse()
        ->and($installer->getLastError())->toContain('ERROR: package not found');
});

it('returns null error on successful install', function () {
    $installer = makeInstaller(isInstalled: false, installResult: true, lastError: null);

    $installer->install();

    expect($installer->getLastError())->toBeNull();
});

it('strips ansi codes from error output', function () {
    $ansiError = "\e[31mERROR:\e[0m something went wrong";
    $clean = preg_replace('/\x1b\[[0-9;]*[A-Za-z]/', '', $ansiError);

    expect($clean)->toBe('ERROR: something went wrong');
});

it('escapes xml special chars in error output', function () {
    $errorLine = "Failed to install <package> & retry";
    $safe = htmlspecialchars($errorLine, ENT_XML1);

    expect($safe)->toBe('Failed to install &lt;package&gt; &amp; retry')
        ->and($safe)->not->toContain('<package>');
});