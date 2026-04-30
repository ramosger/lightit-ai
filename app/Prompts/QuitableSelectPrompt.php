<?php

namespace App\Prompts;

use Closure;
use Laravel\Prompts\SelectPrompt;
use Laravel\Prompts\Support\Result;

class QuitableSelectPrompt extends SelectPrompt
{
    public bool $quitted = false;

    /** @var Closure|null */
    private ?Closure $tickCallback = null;

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

    public function onTick(Closure $callback): static
    {
        $this->tickCallback = $callback;

        return $this;
    }

    /**
     * @param  callable(string $key): ?Result  $callable
     */
    public function runLoop(callable $callable): mixed
    {
        if ($this->tickCallback === null) {
            return parent::runLoop($callable);
        }

        $stdin = fopen('php://stdin', 'r');

        while (true) {
            $read = [$stdin];
            $write = null;
            $except = null;

            $ready = stream_select($read, $write, $except, 0, 500_000);

            if ($ready === false) {
                fclose($stdin);

                return null;
            }

            if ($ready > 0) {
                $key = fread($stdin, 1024);

                if ($key === false || $key === '') {
                    continue;
                }

                $result = $callable($key);

                if ($result instanceof Result) {
                    fclose($stdin);

                    return $result->value;
                }
            } else {
                if (($this->tickCallback)()) {
                    $this->render();
                }
            }
        }
    }
}
