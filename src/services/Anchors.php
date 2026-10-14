<?php

namespace statikbe\entryanchors\services;

use craft\base\Component;
use craft\base\ElementInterface;
use craft\elements\Entry;
use craft\fields\Matrix;
use craft\helpers\ElementHelper;
use statikbe\entryanchors\EntryAnchors;
use statikbe\entryanchors\fields\AnchorLink;
use statikbe\entryanchors\fields\data\AnchorData;

/**
 * The anchors on an entry: the Anchor Link fields on the entry and its nested entries.
 */
class Anchors extends Component
{
    /**
     * The anchor (slug) for a title or a hand-made anchor: Craft's slug, as for entry slugs.
     */
    public function slug(string $title): string
    {
        return ElementHelper::generateSlug($title);
    }

    /**
     * All anchors on an entry, in the order they appear on the page. The first block with an anchor gets it as id on the page
     * (see anchor_id()), so that's where a link to it goes.
     *
     * @return array<array{label: string, value: string}>
     */
    public function options(?ElementInterface $entry): array
    {
        if (!$entry instanceof Entry) {
            return [];
        }

        $settings = EntryAnchors::getInstance()->getSettings();
        $options = [];
        $this->collect($entry, $options, 0, $settings->maxDepth, $settings->fields);

        return array_values($options);
    }

    /**
     * @param string[]|null $fields
     */
    private function collect(ElementInterface $element, array &$options, int $depth, int $maxDepth, ?array $fields): void
    {
        foreach ($element->getFieldLayout()?->getCustomFields() ?? [] as $field) {
            if ($field instanceof AnchorLink) {
                $value = $element->getFieldValue($field->handle);

                if ($value instanceof AnchorData) {
                    $options[$value->anchor] ??= ['label' => $value->title, 'value' => $value->anchor];
                }
            } elseif ($field instanceof Matrix && $depth < $maxDepth && ($fields === null || in_array($field->handle, $fields, true))) {
                foreach ($element->getFieldValue($field->handle)->all() as $nestedEntry) {
                    $this->collect($nestedEntry, $options, $depth + 1, $maxDepth, $fields);
                }
            }
        }
    }
}
