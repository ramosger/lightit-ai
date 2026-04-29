<?php

namespace App\Themes;

use App\Theme;
use Laravel\Prompts\ConfirmPrompt;
use Laravel\Prompts\Themes\Default\ConfirmPromptRenderer as BaseRenderer;

class ConfirmPromptRenderer extends BaseRenderer
{
    public function __invoke(ConfirmPrompt $prompt): string
    {
        $label = $this->truncate($prompt->label, $prompt->terminal()->cols() - 6);

        return match ($prompt->state) {
            'submit' => $this->box(
                $this->dim($label),
                $prompt->value() ? 'Yes' : 'No',
            ),

            'cancel' => $this->box($label, $this->renderOptions($prompt), color: 'red')
                ->error($prompt->cancelMessage),

            'error' => $this->box($label, $this->renderOptions($prompt), color: 'yellow')
                ->warning($this->truncate($prompt->error, $prompt->terminal()->cols() - 5)),

            default => $this->box(
                "\e[38;2;208;191;254m{$label}\e[0m",
                $this->renderOptions($prompt),
            )->when(
                $prompt->hint,
                fn () => $this->hint($prompt->hint),
                fn () => $this->newLine()
            ),
        };
    }

    protected function renderOptions(ConfirmPrompt $prompt): string
    {
        return implode(' / ', [
            $prompt->value() === true
                ? "\e[38;2;208;191;254m● Yes\e[0m"
                : $this->dim('○ Yes'),
            $prompt->value() === false
                ? "\e[38;2;208;191;254m● No\e[0m"
                : $this->dim('○ No'),
        ]);
    }
}