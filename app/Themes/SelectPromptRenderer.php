<?php

namespace App\Themes;

use App\Theme;
use Laravel\Prompts\SelectPrompt;
use Laravel\Prompts\Themes\Default\SelectPromptRenderer as BaseRenderer;

class SelectPromptRenderer extends BaseRenderer
{
    public function __invoke(SelectPrompt $prompt): string
    {
        $c = Theme::PRIMARY;
        $label = $this->truncate($prompt->label, $prompt->terminal()->cols() - 6);

        return match ($prompt->state) {
            'submit' => $this->box(
                $this->dim($label),
                $this->truncate($prompt->label(), $prompt->terminal()->cols() - 6),
            ),

            'cancel' => $this->box($label, $this->renderOptions($prompt), color: 'red')
                ->error($prompt->cancelMessage),

            'error' => $this->box($label, $this->renderOptions($prompt), color: 'yellow')
                ->warning($this->truncate($prompt->error, $prompt->terminal()->cols() - 5)),

            default => $this->box(
                "\e[38;2;208;191;254m{$label}\e[0m",
                $this->renderOptions($prompt),
                info: $prompt->infoText(),
            )->when(
                $prompt->hint,
                fn () => $this->hint($prompt->hint),
                fn () => $this->newLine()
            ),
        };
    }

    protected function renderOptions(SelectPrompt $prompt): string
    {
        return implode(PHP_EOL, $this->scrollbar(
            array_values(array_map(function ($label, $key) use ($prompt) {
                $label = $this->truncate($label, $prompt->terminal()->cols() - 12);

                $index = array_search($key, array_keys($prompt->options));

                if ($prompt->state === 'cancel') {
                    return $this->dim($prompt->highlighted === $index
                        ? "▶ {$this->strikethrough($label)}"
                        : "  {$this->strikethrough($label)}"
                    );
                }

                return $prompt->highlighted === $index
                    ? "\e[38;2;208;191;254m▶ {$label}\e[0m"
                    : "  {$this->dim($label)}";
            }, $visible = $prompt->visible(), array_keys($visible))),
            $prompt->firstVisible,
            $prompt->scroll,
            count($prompt->options),
            min($this->longest($prompt->options, padding: 6), $prompt->terminal()->cols() - 6),
            $prompt->state === 'cancel' ? 'dim' : 'cyan'
        ));
    }
}
