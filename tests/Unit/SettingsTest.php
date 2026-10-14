<?php

use statikbe\entryanchors\models\Settings;

it('looks 3 levels deep into every Matrix field by default', function() {
    $settings = new Settings();
    expect($settings->maxDepth)->toBe(3)
        ->and($settings->fields)->toBeNull();
});
