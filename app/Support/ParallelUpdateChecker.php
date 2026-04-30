<?php

namespace App\Support;

class ParallelUpdateChecker
{
    private const POLL_INTERVAL_US = 50_000;
    private const TIMEOUT_SECONDS = 15;

    /** @param array<string, \App\Installers\Contracts\InstallerInterface> $installers */
    public function __construct(private readonly array $installers) {}

    /**
     * @param  string[] $keys
     * @return array<string, array{current: string|null, latest: string|null, hasUpdate: bool}>
     */
    public function run(array $keys): array
    {
        $php = PHP_BINARY;
        $phar = \Phar::running(false);
        $app = $phar !== '' ? $phar : base_path('application');
        $tmpFiles = [];

        foreach ($keys as $key) {
            $tmp = tempnam(sys_get_temp_dir(), 'lightit_upd_');
            $tmpFiles[$key] = $tmp;

            $cmd = sprintf('%s %s check-update %s > %s 2>/dev/null &',
                escapeshellarg($php),
                escapeshellarg($app),
                escapeshellarg($key),
                escapeshellarg($tmp),
            );
            exec($cmd, $out, $code);
        }

        $deadline = microtime(true) + self::TIMEOUT_SECONDS;
        $done = [];

        while (count($done) < count($keys) && microtime(true) < $deadline) {
            foreach ($keys as $key) {
                if (isset($done[$key])) {
                    continue;
                }

                $content = @file_get_contents($tmpFiles[$key]);
                if ($content !== false && $content !== '') {
                    $done[$key] = $content;
                }
            }

            if (count($done) < count($keys)) {
                usleep(self::POLL_INTERVAL_US);
            }
        }

        $results = [];
        foreach ($keys as $key) {
            $decoded = json_decode($done[$key] ?? '', true);
            $results[$key] = is_array($decoded)
                ? $decoded
                : ['current' => null, 'latest' => null, 'hasUpdate' => false];

            @unlink($tmpFiles[$key]);
        }

        return $results;
    }
}
