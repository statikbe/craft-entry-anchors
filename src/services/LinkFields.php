<?php

namespace statikbe\entryanchors\services;

use Craft;
use craft\base\Component;
use craft\elements\Entry;
use craft\events\DefineFieldHtmlEvent;
use craft\fields\data\LinkData;
use craft\fields\Link;
use craft\fields\linktypes\Url;
use craft\helpers\Cp;
use craft\helpers\ElementHelper;
use craft\helpers\Html;
use statikbe\entryanchors\EntryAnchors;
use statikbe\entryanchors\web\assets\linkanchors\LinkAnchorsAsset;

/**
 * Anchor dropdowns on Link fields:
 *
 * - next to the entry select, with the anchors of the linked entry. The chosen anchor is stored in the field's URL suffix as
 *   `#slug`, so `link.url` ends with it. For Link fields with the "Entry" link type and the "URL Suffix" advanced field.
 * - below the URL input, with the anchors of the page the field is on. The chosen anchor becomes the URL (`#slug`). For Link
 *   fields whose URL type allows anchors; their URL type is called "URL/Anchor" in the link type select.
 *
 * The JS finds its way through Craft's Link field markup; if that ever changes, it leaves the field as it is and anchors can
 * still be typed.
 */
class LinkFields extends Component
{
    public function defineInputHtml(DefineFieldHtmlEvent $event): void
    {
        /** @var Link $field */
        $field = $event->sender;
        $urlType = $field->getLinkTypes()['url'] ?? null;
        $urlAllowsAnchors = $urlType instanceof Url && $urlType->allowAnchors;

        if ($urlAllowsAnchors) {
            $this->relabelUrlType($event);
        }

        if ($event->inline) {
            return;
        }

        if (in_array('entry', $field->types, true) && in_array('urlSuffix', $field->advancedFields, true)) {
            $this->addEntryAnchors($event, $field);
        }

        if ($urlAllowsAnchors && in_array('url', $field->types, true)) {
            $this->addPageAnchors($event, $field);
        }
    }

    /**
     * The anchors of the linked entry, next to the entry select.
     */
    private function addEntryAnchors(DefineFieldHtmlEvent $event, Link $field): void
    {
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
                'labels' => $this->labels(),
            ],
        ]);

        // Moved next to the entry select by the JS
        $event->html .= $this->selectHtml("$id-anchor");
    }

    /**
     * The anchors of the page the field is on (the entry that owns it, also for fields in nested entries), below the URL input.
     */
    private function addPageAnchors(DefineFieldHtmlEvent $event, Link $field): void
    {
        $page = $event->element ? ElementHelper::rootElement($event->element) : null;
        $options = $page instanceof Entry ? EntryAnchors::getInstance()->getAnchors()->options($page) : [];

        $id = $field->getInputId();
        $view = Craft::$app->getView();
        $view->registerAssetBundle(LinkAnchorsAsset::class);
        $view->registerJsWithVars(fn($id, $settings) => <<<JS
new Craft.EntryAnchorsUrlField($id, $settings);
JS, [
            $view->namespaceInputId($id),
            [
                'options' => $options,
                'labels' => array_merge($this->labels(), [
                    'none' => '— ' . Craft::t('entry-anchors', 'Anchor on this page') . ' —',
                ]),
            ],
        ]);

        // Moved below the URL input by the JS
        $event->html .= $this->selectHtml("$id-page-anchor");
    }

    private function selectHtml(string $id): string
    {
        return Html::tag('div', Cp::selectHtml([
            'id' => $id,
            'options' => [],
            'inputAttributes' => [
                'aria' => ['label' => Craft::t('entry-anchors', 'Anchor')],
            ],
        ]), [
            'class' => ['entry-anchors-select', 'hidden'],
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function labels(): array
    {
        return [
            'none' => '— ' . Craft::t('entry-anchors', 'No anchor') . ' —',
            'notFound' => Craft::t('entry-anchors', 'not found'),
            'error' => Craft::t('entry-anchors', 'Could not load the anchors for this entry.'),
        ];
    }

    /**
     * Craft adds its URL link type last, under the same id, so a plugin can't replace it; only its option in the select is
     * relabeled. If Craft's markup changes, the option keeps its own label.
     */
    private function relabelUrlType(DefineFieldHtmlEvent $event): void
    {
        $label = Html::encode(Craft::t('entry-anchors', 'URL/Anchor'));
        $event->html = preg_replace(
            '/(<option value="url"[^>]*>)[^<]*(<\/option>)/',
            '${1}' . $label . '${2}',
            $event->html,
            1,
        ) ?? $event->html;
    }
}
