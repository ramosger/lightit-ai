<?php

namespace App\Commands;

use App\Screens\MainMenuScreen;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class MainCommand extends Command
{
    protected $signature = 'main';

    protected $description = 'Launch the Light-it AI Stack installer TUI';

    public function handle(MainMenuScreen $menu): void
    {
        $menu->render($this);
    }

    public function schedule(Schedule $schedule): void {}
}
