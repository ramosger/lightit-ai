<?php

namespace App\Support;

class JsonMerger
{
    /**
     * Deep-merge $patch into $base, preserving all existing keys in $base.
     * Only adds or updates keys that exist in $patch but not $base (or that are arrays to recurse into).
     *
     * @param  array<string, mixed>  $base
     * @param  array<string, mixed>  $patch
     * @return array<string, mixed>
     */
    public function merge(array $base, array $patch): array
    {
        foreach ($patch as $key => $value) {
            if (array_key_exists($key, $base) && is_array($base[$key]) && is_array($value)) {
                $base[$key] = $this->merge($base[$key], $value);
            } elseif (! array_key_exists($key, $base)) {
                $base[$key] = $value;
            }
            // If key exists and is not an array, we leave base value intact (non-destructive)
        }

        return $base;
    }

    /**
     * Read a JSON file (returns [] if missing or invalid), merge patch in, write back.
     */
    public function mergeIntoFile(string $path, mixed $patch): bool
    {
        $existing = [];

        if (file_exists($path)) {
            $decoded = json_decode(file_get_contents($path), true);
            if (is_array($decoded)) {
                $existing = $decoded;
            }
        }

        if (! is_array($patch)) {
            return false;
        }

        $merged = $this->merge($existing, $patch);
        $dir = dirname($path);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        return file_put_contents($path, json_encode($merged, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n") !== false;
    }
}
