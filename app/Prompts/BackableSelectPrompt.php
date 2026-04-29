<?php

namespace App\Prompts;

use Laravel\Prompts\Key;
use Laravel\Prompts\SelectPrompt;

class BackableSelectPrompt extends SelectPrompt
{
    public bool $cancelled = false;

    public function __construct(
        string $label,
        array $options,
        int|string|null $default = null,
        int $scroll = 5,
        mixed $validate = null,
        string $hint = '',
    ) {
        parent::__construct($label, $options, $default, $scroll, $validate, $hint);

        $this->on('key', function ($key) {
            if ($key === Key::ESCAPE) {
                $this->cancelled = true;
                $this->submit();
            }
        });
    }
}
