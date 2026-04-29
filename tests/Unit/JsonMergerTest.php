<?php

use App\Support\JsonMerger;

beforeEach(function () {
    $this->merger = new JsonMerger;
});

it('adds new keys from patch without overwriting base', function () {
    $base = ['existing' => 'value', 'nested' => ['a' => 1]];
    $patch = ['new' => 'added', 'nested' => ['b' => 2]];

    $result = $this->merger->merge($base, $patch);

    expect($result['existing'])->toBe('value')
        ->and($result['new'])->toBe('added')
        ->and($result['nested']['a'])->toBe(1)
        ->and($result['nested']['b'])->toBe(2);
});

it('does not overwrite existing scalar values', function () {
    $base = ['key' => 'original'];
    $patch = ['key' => 'overwrite'];

    $result = $this->merger->merge($base, $patch);

    expect($result['key'])->toBe('original');
});

it('deeply merges nested arrays', function () {
    $base = ['mcpServers' => ['other' => ['command' => 'other']]];
    $patch = ['mcpServers' => ['engram' => ['command' => 'engram', 'args' => ['mcp']]]];

    $result = $this->merger->merge($base, $patch);

    expect($result['mcpServers'])->toHaveKeys(['other', 'engram'])
        ->and($result['mcpServers']['other']['command'])->toBe('other')
        ->and($result['mcpServers']['engram']['command'])->toBe('engram');
});

it('merges into a file preserving existing content', function () {
    $tmp = tempnam(sys_get_temp_dir(), 'jm_');
    file_put_contents($tmp, json_encode(['existing' => true]));

    $this->merger->mergeIntoFile($tmp, ['new' => 'value']);

    $result = json_decode(file_get_contents($tmp), true);

    expect($result['existing'])->toBeTrue()
        ->and($result['new'])->toBe('value');

    unlink($tmp);
});

it('creates file if it does not exist', function () {
    $tmp = sys_get_temp_dir().'/jm_new_'.uniqid().'.json';

    $this->merger->mergeIntoFile($tmp, ['key' => 'value']);

    expect(file_exists($tmp))->toBeTrue();
    $result = json_decode(file_get_contents($tmp), true);
    expect($result['key'])->toBe('value');

    unlink($tmp);
});
