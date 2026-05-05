<?php

namespace App\Screens\Providers;

use LaravelZero\Framework\Commands\Command;

class OpenCodeProviderScreen
{
    public function render(Command $command): void
    {
        passthru('clear');
        $command->line('  <fg=yellow>OpenCode extras — coming soon.</>');
        $command->newLine();
        $command->ask('Press enter to go back');
    }
}
