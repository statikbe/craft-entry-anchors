<?php

/**
 * Every message in the entry-anchors category, in PHP (Craft::t) and Twig (|t), has a translation in every language.
 */
function entryAnchorsMessages(): array
{
    $patterns = [
        'php' => "/Craft::t\\('entry-anchors', '((?:[^'\\\\]|\\\\.)*)'/",
        'twig' => "/'((?:[^'\\\\]|\\\\.)*)'\\|t\\('entry-anchors'\\)/",
    ];
    $messages = [];
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator(dirname(__DIR__, 2) . '/src')) as $file) {
        $pattern = $patterns[$file->getExtension()] ?? null;
        if ($pattern && !str_contains($file->getPathname(), '/translations/')) {
            preg_match_all($pattern, file_get_contents($file->getPathname()), $matches);
            array_push($messages, ...$matches[1]);
        }
    }
    return array_values(array_unique($messages));
}

$messages = entryAnchorsMessages();

it('finds the messages', function() use ($messages) {
    expect(count($messages))->toBeGreaterThanOrEqual(7);
});

it('translates every message', function(string $language) use ($messages) {
    $translations = require dirname(__DIR__, 2) . "/src/translations/{$language}/entry-anchors.php";
    expect(array_values(array_diff($messages, array_keys($translations))))->toBe([]);
})->with(['nl', 'fr']);
