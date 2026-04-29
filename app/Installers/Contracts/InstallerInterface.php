<?php

namespace App\Installers\Contracts;

interface InstallerInterface
{
    public function install(): bool;

    public function uninstall(): bool;

    /**
     * @return array{current: string|null, latest: string|null, hasUpdate: bool}
     */
    public function checkUpdate(): array;

    public function isInstalled(): bool;
}
