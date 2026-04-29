<?php

namespace App;

class Theme
{
    const PRIMARY = '#D0BFFE';

    const NAV_HINT = '↑↓: navigate • enter: select • q: quit';
    const NAV_HINT_SUB = '↑↓: navigate • enter: select • esc: back';
    const NAV_HINT_MULTI = '↑↓: navigate • space: toggle • enter: confirm • esc: back';

    public static function navHint(): string
    {
        $c = self::PRIMARY;

        return "  <fg=$c>" . self::NAV_HINT . "</>";
    }
}
