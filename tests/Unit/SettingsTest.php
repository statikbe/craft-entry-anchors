<?php

use statikbe\entryanchors\models\Settings;

it('copies the full URL and looks 3 levels deep into every Matrix field by default', function() {
    $settings = new Settings();
    expect($settings->copyFormat)->toBe('url')
        ->and($settings->maxDepth)->toBe(3)
        ->and($settings->fields)->toBeNull();
});
