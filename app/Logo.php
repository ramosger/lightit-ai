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
            '  <fg=white;options=bold>  ⚡ AI Stack Installer</> <fg=gray>v1.0.0</>',
            '  <fg=gray>  Bootstrap your AI coding environment in minutes</>',
            '',
        ]);
    }
}
