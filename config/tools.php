<?php

return [

    'engram' => [
        'name' => 'Engram',
        'description' => 'Persistent memory for AI coding agents',
        'url' => 'https://github.com/Gentleman-Programming/engram',
        'method' => 'brew',
        'brew_tap' => 'gentleman-programming/tap',
        'brew_pkg' => 'engram',
        'version_cmd' => 'engram version',
        'verify_cmd' => 'engram version',
    ],

    'pao' => [
        'name' => 'Pao',
        'description' => 'PHP agentic orchestration by Nuno Maduro',
        'url' => 'https://github.com/nunomaduro/pao',
        'method' => 'composer-global',
        'composer_pkg' => 'nunomaduro/pao',
        'version_cmd' => 'pao --version',
        'verify_cmd' => 'pao --version',
    ],

    'rtk' => [
        'name' => 'RTK',
        'description' => 'Rust Token Killer — reduces LLM token usage 60-90%',
        'url' => 'https://github.com/rtk-ai/rtk',
        'method' => 'brew',
        'brew_tap' => null,
        'brew_pkg' => 'rtk',
        'version_cmd' => 'rtk --version',
        'verify_cmd' => 'rtk gain',
        'fallback_script' => 'https://raw.githubusercontent.com/rtk-ai/rtk/refs/heads/master/install.sh',
    ],

    'leann' => [
        'name' => 'LEANN',
        'description' => 'Lightweight embedding-based approximate nearest neighbour',
        'url' => 'https://github.com/yichuan-w/LEANN',
        'method' => 'pip',
        'pip_pkg' => 'leann-py',
        'version_cmd' => 'python3 -c "import leann; print(leann.__version__)"',
        'verify_cmd' => 'python3 -c "import leann"',
    ],

];
