<?php

namespace App\Installers\Concerns;

trait FetchesGithubRelease
{
    private function fetchGithubLatestTag(string $owner, string $repo): ?string
    {
        $ctx = stream_context_create([
            'http' => [
                'header' => "User-Agent: lightit-ai\r\n",
                'timeout' => 5,
                'follow_location' => 1,
                'max_redirects' => 5,
            ],
        ]);

        $url = "https://github.com/{$owner}/{$repo}/releases/latest";
        $html = @file_get_contents($url, false, $ctx);

        if ($html === false) {
            return null;
        }

        foreach ($http_response_header ?? [] as $header) {
            if (stripos($header, 'Location:') === 0) {
                $location = trim(substr($header, 9));
                if (preg_match('~/releases/tag/v?([^\s"\']+)$~', $location, $m)) {
                    return $m[1];
                }
            }
        }

        if (preg_match('~releases/tag/v?([0-9][^\s"\']+)~', $html, $m)) {
            return $m[1];
        }

        return null;
    }
}
