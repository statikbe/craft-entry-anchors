<?php

namespace statikbe\entryanchors\web\assets\anchorlink;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * The copy button of Anchor Link fields (see dist/anchorlink.js).
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
}
