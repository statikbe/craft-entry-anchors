<?php

use statikbe\entryanchors\fields\data\AnchorData;

it('is the title in templates', function() {
    $value = new AnchorData('How long does a project take?', 'project-duration');
    expect((string)$value)->toBe('How long does a project take?')
        ->and($value->getAnchor())->toBe('project-duration');
});

it('stores the title and the anchor', function() {
    expect((new AnchorData('Contact', 'contact'))->serialize())->toBe(['title' => 'Contact', 'anchor' => 'contact']);
});
