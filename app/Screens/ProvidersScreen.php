<?php

namespace App\Screens;

use App\Prompts\BackableSelectPrompt;
use App\Screens\Providers\ClaudeProviderScreen;
use App\Screens\Providers\OpenCodeProviderScreen;
use App\Theme;
use LaravelZero\Framework\Commands\Command;

class ProvidersScreen
{
    public function __construct(
        private readonly ClaudeProviderScreen $claude,
        private readonly OpenCodeProviderScreen $openCode,
    ) {}

    public function render(Command $command): void
    {
        $prompt = new BackableSelectPrompt(
            label: 'Select your provider',
            options: [
                'claude' => 'Claude',
                'opencode' => 'OpenCode',
            ],
            hint: Theme::NAV_HINT_SUB,
        );

        $choice = $prompt->prompt();

        if ($prompt->cancelled) {
            return;
        }

        passthru('clear');
        match ($choice) {
            'claude' => $this->claude->render($command),
            'opencode' => $this->openCode->render($command),
        };
    }
}
