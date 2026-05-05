<?php

namespace App;

class Logo
{
    public static function render(): string
    {
        $spark = self::spark();
        $width = 57;

        $wordmark = [
            '  ██╗     ██╗ ██████╗ ██╗  ██╗████████╗    ██╗████████╗',
            '  ██║     ██║██╔════╝ ██║  ██║╚══██╔══╝    ██║╚══██╔══╝',
            '  ██║     ██║██║  ███╗███████║   ██║       ██║   ██║    ',
            '  ██║     ██║██║   ██║██╔══██║   ██║       ██║   ██║    ',
            '  ███████╗██║╚██████╔╝██║  ██║   ██║       ██║   ██║    ',
            '  ╚══════╝╚═╝ ╚═════╝ ╚═╝  ╚═╝   ╚═╝       ╚═╝   ╚═╝   ',
        ];

        $lines = [];
        foreach ($spark as $i => $sparkLine) {
            $wIdx = $i - 3;
            if ($wIdx >= 0 && $wIdx < count($wordmark)) {
                $text = $wordmark[$wIdx];
                $pad  = str_repeat(' ', max(0, $width - mb_strwidth($text)));
                $lines[] = "  <fg=#794DFC>{$text}{$pad}</> <fg=#794DFC;options=bold>{$sparkLine}</>";
            } else {
                $lines[] = '  ' . str_repeat(' ', $width + 1) . "<fg=#794DFC;options=bold>{$sparkLine}</>";
            }
        }

        return implode("\n", [
            '',
            ...$lines,
            '',
            '  <fg=white;options=bold>  ⚡ Light-It AI Stack Installer</> <fg=gray>v1.0.0</>',
            '',
        ]);
    }

    public static function spark(): array
    {
        return [
            '                  ',
            '        ▓▓        ',
            '      ▓▓█▓        ',
            '    ▓▓███▓        ',
            '    ▓████▓▓▓▓    ',
            '    ▓█████████▓   ',
            '    ▓█████████▓   ',
            '         ▓████▓   ',
            '         ▓███▓    ',
            '         ▓█▓      ',
            '                  ',
        ];
    }
}
