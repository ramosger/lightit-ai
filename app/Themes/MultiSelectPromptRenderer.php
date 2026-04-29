<?php

namespace App\Themes;

use App\Theme;
use Laravel\Prompts\MultiSelectPrompt;
use Laravel\Prompts\Themes\Default\MultiSelectPromptRenderer as BaseRenderer;

class MultiSelectPromptRenderer extends BaseRenderer
{
    public function __invoke(MultiSelectPrompt $prompt): string
    {
        $label = $this->truncate($prompt->label, $prompt->terminal()->cols() - 6);

        return match ($prompt->state) {
            'submit' => $this->box(
                $this->dim($label),
                $this->renderSelectedOptions($prompt),
            ),

            'cancel' => $this->box($label, $this->renderOptions($prompt), color: 'red')
                ->error($prompt->cancelMessage),

            'error' => $this->box(
                $label,
                $this->renderOptions($prompt),
                color: 'yellow',
                info: count($prompt->options) > $prompt->scroll ? (count($prompt->value()).' selected') : '',
            )->warning($this->truncate($prompt->error, $prompt->terminal()->cols() - 5)),

            default => $this->box(
                "\e[38;2;208;191;254m{$label}\e[0m",
                $this->renderOptions($prompt),
                info: $this->getInfoText($prompt),
            )->when(
                $prompt->hint,
                fn () => $this->hint($prompt->hint),
                fn () => $this->newLine()
            ),
        };
    }

    protected function renderOptions(MultiSelectPrompt $prompt): string
    {
        return implode(PHP_EOL, $this->scrollbar(
            array_values(array_map(function ($label, $key) use ($prompt) {
                $label = $this->truncate($label, $prompt->terminal()->cols() - 12);

                $index = array_search($key, array_keys($prompt->options));
                $active = $index === $prompt->highlighted;
                if (array_is_list($prompt->options)) {
                    $value = $prompt->options[$index];
                } else {
                    $value = array_keys($prompt->options)[$index];
                }
                $selected = in_array($value, $prompt->value());

                if ($prompt->state === 'cancel') {
                    return $this->dim(match (true) {
                        $active && $selected => "▶ ◼ {$this->strikethrough($label)}",
                        $active             => "▶ ◻ {$this->strikethrough($label)}",
                        $selected           => "  ◼ {$this->strikethrough($label)}",
                        default             => "  ◻ {$this->strikethrough($label)}",
                    });
                }

                return match (true) {
                    $active && $selected => "\e[38;2;208;191;254m▶ ◼ {$label}\e[0m",
                    $active             => "\e[38;2;208;191;254m▶ ◻ {$label}\e[0m",
                    $selected           => "  \e[38;2;208;191;254m◼\e[0m {$this->dim($label)}",
                    default             => "  {$this->dim('◻')} {$this->dim($label)}",
                };
            }, $visible = $prompt->visible(), array_keys($visible))),
            $prompt->firstVisible,
            $prompt->scroll,
            count($prompt->options),
            min($this->longest($prompt->options, padding: 6), $prompt->terminal()->cols() - 6),
            $prompt->state === 'cancel' ? 'dim' : 'cyan'
        ));
    }
}
