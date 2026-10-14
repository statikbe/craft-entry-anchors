<?php

namespace statikbe\entryanchors\fields;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\db\Query;
use craft\db\Table;
use craft\helpers\ElementHelper;
use statikbe\entryanchors\EntryAnchors;
use statikbe\entryanchors\fields\data\AnchorData;
use statikbe\entryanchors\web\assets\anchorlink\AnchorLinkAsset;
use yii\db\Schema;

/**
 * A block's title and its anchor. The front end renders the title with the anchor as id: `id="{{ anchor_id(block.blockTitle) }}"`.
 *
 * Like Craft's slug, the anchor follows the title while the block isn't published yet, and is kept after that: changing the
 * title doesn't break links to the block. Editors can change the anchor below the title, e.g. to make it shorter.
 * The copy button copies the link to the block. The anchor dropdowns on Link fields list these anchors.
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
        return Schema::TYPE_JSON;
    }

    public function normalizeValue(mixed $value, ?ElementInterface $element): mixed
    {
        if ($value instanceof AnchorData) {
            return $value;
        }

        // A plain title: content from before the anchor was stored (its anchor is the slug it was rendered with), or content
        // set from code
        if (is_string($value)) {
            $value = ['title' => $value];
        }

        if (!is_array($value)) {
            return null;
        }

        $title = trim((string)($value['title'] ?? ''));
        if ($title === '') {
            return null;
        }

        $anchors = EntryAnchors::getInstance()->getAnchors();
        $anchor = $anchors->slug(trim((string)($value['anchor'] ?? '')));

        return new AnchorData($title, $anchor !== '' ? $anchor : $anchors->slug($title));
    }

    public function getSearchKeywords(mixed $value, ElementInterface $element): string
    {
        return $value instanceof AnchorData ? $value->title : '';
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
                'follow' => !$value instanceof AnchorData || $this->isUnpublished($element),
                'copied' => Craft::t('entry-anchors', 'Anchor link copied to clipboard.'),
                'failed' => Craft::t('entry-anchors', 'Could not copy the anchor link.'),
            ],
        ]);

        return $view->renderTemplate('entry-anchors/_field/input', [
            'field' => $this,
            'id' => $id,
            'value' => $value instanceof AnchorData ? $value : null,
            'copyFormat' => EntryAnchors::getInstance()->getSettings()->copyFormat,
        ]);
    }

    /**
     * Whether the element isn't on a published page yet: then its anchor can still follow the title, like Craft's slug.
     *
     * Drafts share their unchanged nested entries with the published entry, so being in a draft doesn't make a block new:
     * a block is published when any chain of owners (elements_owners) leads to an entry that isn't a draft.
     */
    private function isUnpublished(?ElementInterface $element): bool
    {
        if (!$element || !$element->id || $element->getIsFresh()) {
            return true;
        }

        return !$this->isPublished((int)$element->getCanonicalId());
    }

    private function isPublished(int $elementId, int $depth = 0): bool
    {
        // Nested entries go a few levels deep (content builder → row → cell → …); this guards against loops
        if ($depth > 10) {
            return false;
        }

        $element = (new Query())
            ->select(['draftId', 'revisionId', 'dateDeleted'])
            ->from(Table::ELEMENTS)
            ->where(['id' => $elementId])
            ->one();
        if (!$element || $element['revisionId'] || $element['dateDeleted']) {
            return false;
        }

        $ownerIds = (new Query())
            ->select(['ownerId'])
            ->from(Table::ELEMENTS_OWNERS)
            ->where(['elementId' => $elementId])
            ->column();
        if (!$ownerIds) {
            return $element['draftId'] === null;
        }

        foreach ($ownerIds as $ownerId) {
            if ($this->isPublished((int)$ownerId, $depth + 1)) {
                return true;
            }
        }

        return false;
    }
}
