<?php

namespace App\Prompts;

use Laravel\Prompts\Key;
use Laravel\Prompts\MultiSelectPrompt;

class BackableMultiSelectPrompt extends MultiSelectPrompt
{
    public bool $cancelled = false;

    public function __construct(
        string $label,
        array $options,
        array $default = [],
        int $scroll = 5,
        bool|string $required = false,
        mixed $validate = null,
        string $hint = '',
    ) {
        parent::__construct($label, $options, $default, $scroll, $required, $validate, $hint);

        $this->on('key', function ($key) {
            if ($key === Key::ESCAPE) {
                $this->cancelled = true;
                $this->submit();
            }
        });
    }
}
