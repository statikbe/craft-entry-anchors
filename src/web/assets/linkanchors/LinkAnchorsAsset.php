<?php

namespace statikbe\entryanchors\web\assets\linkanchors;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * The anchor dropdown on Link fields (see dist/linkanchors.js). Registered with the Link field, see LinkFields.
 */
class LinkAnchorsAsset extends AssetBundle
{
    public $sourcePath = __DIR__ . '/dist';

    public $depends = [
        CpAsset::class,
    ];

    public $js = [
        'linkanchors.js',
    ];

    public $css = [
        'linkanchors.css',
    ];
}
