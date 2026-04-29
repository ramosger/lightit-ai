<?php

namespace App;

class Logo
{
    public static function render(): string
    {
        return implode("\n", [
            '',
            '  <fg=magenta>  ██╗     ██╗ ██████╗ ██╗  ██╗████████╗    ██╗████████╗</>',
            '  <fg=magenta>  ██║     ██║██╔════╝ ██║  ██║╚══██╔══╝    ██║╚══██╔══╝</>',
            '  <fg=magenta>  ██║     ██║██║  ███╗███████║   ██║       ██║   ██║   </>',
            '  <fg=magenta>  ██║     ██║██║   ██║██╔══██║   ██║       ██║   ██║   </>',
            '  <fg=magenta>  ███████╗██║╚██████╔╝██║  ██║   ██║       ██║   ██║   </>',
            '  <fg=magenta>  ╚══════╝╚═╝ ╚═════╝ ╚═╝  ╚═╝   ╚═╝       ╚═╝   ╚═╝   </>',
            '',
            '  <fg=white;options=bold>  ⚡ Light-It AI Stack Installer</> <fg=gray>v1.0.0</>',
            '',
        ]);
    }
}
