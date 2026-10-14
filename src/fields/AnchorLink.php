<?php

namespace statikbe\entryanchors\fields;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\helpers\ElementHelper;
use statikbe\entryanchors\EntryAnchors;
use statikbe\entryanchors\web\assets\anchorlink\AnchorLinkAsset;
use yii\db\Schema;

/**
 * A block's title, which is also its anchor: the front end renders it with its slug as id (`id="{{ block.blockTitle|slugify }}"`).
 * The copy button copies the link to the block. The anchor dropdown on Link fields lists these fields.
 */
class AnchorLink extends Field
{
    public static function displayName(): string
    {
        return Craft::t('entry-anchors', 'Anchor Link');
    }

    public static function icon(): string
    {
        return 'hashtag';
    }

    public static function dbType(): string
    {
        return Schema::TYPE_STRING;
    }

    protected function inputHtml(mixed $value, ?ElementInterface $element, bool $inline): string
    {
        $id = $this->getInputId();
        $view = Craft::$app->getView();
        $view->registerAssetBundle(AnchorLinkAsset::class);
        $view->registerJsWithVars(fn($id, $settings) => <<<JS
new Craft.EntryAnchorsAnchorLink($id, $settings);
JS, [
            $view->namespaceInputId($id),
            [
                // Nested entries (content builder blocks) have no URL of their own, the page they are on has
                'url' => $element ? (ElementHelper::rootElement($element)->getUrl() ?? '') : '',
                'copyFormat' => EntryAnchors::getInstance()->getSettings()->copyFormat,
                'copied' => Craft::t('entry-anchors', 'Anchor link copied to clipboard.'),
                'failed' => Craft::t('entry-anchors', 'Could not copy the anchor link.'),
            ],
        ]);

        return $view->renderTemplate('entry-anchors/_field/input', [
            'field' => $this,
            'id' => $id,
            'value' => $value,
        ]);
    }
}
