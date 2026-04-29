<?php

namespace App\Prompts;

use Laravel\Prompts\Key;
use Laravel\Prompts\SelectPrompt;

class QuitableSelectPrompt extends SelectPrompt
{
    public bool $quitted = false;

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
            if ($key === 'q') {
                $this->quitted = true;
                $this->submit();
            }
        });
    }
}
