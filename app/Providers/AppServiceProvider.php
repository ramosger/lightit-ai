<?php

namespace App\Providers;

use App\Configurators\ClaudeCodeConfigurator;
use App\Installers\EngramInstaller;
use App\Installers\LeannInstaller;
use App\Installers\PaoInstaller;
use App\Installers\RtkInstaller;
use App\Screens\EngramScreen;
use App\Screens\InstallScreen;
use App\Screens\MainMenuScreen;
use App\Screens\UninstallScreen;
use App\Screens\UpdateScreen;
use App\Support\BrewRunner;
use App\Support\JsonMerger;
use App\Support\StateManager;
use App\Prompts\BackableMultiSelectPrompt;
use App\Prompts\BackableSelectPrompt;
use App\Prompts\QuitableSelectPrompt;
use App\Themes\ConfirmPromptRenderer;
use App\Themes\MultiSelectPromptRenderer;
use App\Themes\SelectPromptRenderer;
use Illuminate\Support\ServiceProvider;
use Laravel\Prompts\ConfirmPrompt;
use Laravel\Prompts\MultiSelectPrompt;
use Laravel\Prompts\Prompt;
use Laravel\Prompts\SelectPrompt;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(StateManager::class);
        $this->app->singleton(BrewRunner::class);
        $this->app->singleton(JsonMerger::class);

        $this->app->singleton(ClaudeCodeConfigurator::class, function ($app) {
            return new ClaudeCodeConfigurator($app->make(JsonMerger::class));
        });

        $this->app->singleton('installers', function ($app) {
            $brew = $app->make(BrewRunner::class);
            $state = $app->make(StateManager::class);

            return [
                'engram' => new EngramInstaller($brew, $state),
                'pao' => new PaoInstaller($state),
                'rtk' => new RtkInstaller($brew, $state),
                'leann' => new LeannInstaller($brew, $state),
            ];
        });

        $this->app->singleton(InstallScreen::class, function ($app) {
            return new InstallScreen(
                $app->make('installers'),
                $app->make(StateManager::class),
                $app->make(ClaudeCodeConfigurator::class),
            );
        });

        $this->app->singleton(UninstallScreen::class, function ($app) {
            return new UninstallScreen(
                $app->make('installers'),
                $app->make(StateManager::class),
            );
        });

        $this->app->singleton(UpdateScreen::class, function ($app) {
            return new UpdateScreen(
                $app->make('installers'),
            );
        });

        $this->app->singleton(EngramScreen::class);

        $this->app->singleton(MainMenuScreen::class, function ($app) {
            return new MainMenuScreen(
                $app->make(InstallScreen::class),
                $app->make(UninstallScreen::class),
                $app->make(UpdateScreen::class),
                $app->make(EngramScreen::class),
            );
        });
    }

    public function boot(): void
    {
        Prompt::addTheme('lightit', [
            SelectPrompt::class => SelectPromptRenderer::class,
            MultiSelectPrompt::class => MultiSelectPromptRenderer::class,
            BackableMultiSelectPrompt::class => MultiSelectPromptRenderer::class,
            QuitableSelectPrompt::class => SelectPromptRenderer::class,
            BackableSelectPrompt::class => SelectPromptRenderer::class,
            ConfirmPrompt::class => ConfirmPromptRenderer::class,
        ]);

        Prompt::theme('lightit');

        Prompt::cancelUsing(function () {
            passthru('clear');
            exit(0);
        });
    }
}
