<?php

namespace statikbe\entryanchors\services;

use Craft;
use craft\base\Component;
use craft\events\DefineFieldHtmlEvent;
use craft\fields\data\LinkData;
use craft\fields\Link;
use craft\helpers\Cp;
use craft\helpers\Html;
use statikbe\entryanchors\EntryAnchors;
use statikbe\entryanchors\web\assets\linkanchors\LinkAnchorsAsset;

/**
 * The anchor dropdown on Link fields, next to the entry select. The chosen anchor is stored in the field's URL suffix as `#slug`,
 * so `link.url` ends with it.
 *
 * Only for Link fields with the "Entry" link type and the "URL Suffix" advanced field. The JS finds its way through Craft's
 * Link field markup; if that ever changes, it leaves the field as it is and the anchor can still be typed in the URL suffix.
 */
class LinkFields extends Component
{
    public function defineInputHtml(DefineFieldHtmlEvent $event): void
    {
        /** @var Link $field */
        $field = $event->sender;

        if ($event->inline || !in_array('entry', $field->types, true) || !in_array('urlSuffix', $field->advancedFields, true)) {
            return;
        }

        $value = $event->value instanceof LinkData ? $event->value : null;
        $entry = $value?->getType() === 'entry' ? $value->getElement() : null;

        $id = $field->getInputId();
        $view = Craft::$app->getView();
        $view->registerAssetBundle(LinkAnchorsAsset::class);
        $view->registerJsWithVars(fn($id, $settings) => <<<JS
new Craft.EntryAnchorsLinkField($id, $settings);
JS, [
            $view->namespaceInputId($id),
            [
                'options' => EntryAnchors::getInstance()->getAnchors()->options($entry),
                'hasEntry' => $entry !== null,
                'labels' => [
                    'none' => '— ' . Craft::t('entry-anchors', 'No anchor') . ' —',
                    'notFound' => Craft::t('entry-anchors', 'not found'),
                    'error' => Craft::t('entry-anchors', 'Could not load the anchors for this entry.'),
                ],
            ],
        ]);

        // Moved next to the entry select by the JS
        $event->html .= Html::tag('div', Cp::selectHtml([
            'id' => "$id-anchor",
            'options' => [],
            'inputAttributes' => [
                'aria' => ['label' => Craft::t('entry-anchors', 'Anchor')],
            ],
        ]), [
            'class' => ['entry-anchors-select', 'hidden'],
        ]);
    }
}
