<?php

namespace statikbe\entryanchors\web\assets\anchorlink;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * Anchor Link fields: the anchor below the title and the copy button (see dist/anchorlink.js).
 */
class AnchorLinkAsset extends AssetBundle
{
    public $sourcePath = __DIR__ . '/dist';

    public $depends = [
        CpAsset::class,
    ];

    public $js = [
        'anchorlink.js',
    ];

    public $css = [
        'anchorlink.css',
    ];
}
