<?php

namespace statikbe\entryanchors\models;

use craft\base\Model;

/**
 * Settings, set in config/entry-anchors.php.
 */
class Settings extends Model
{
    /**
     * What the copy button of an Anchor Link field copies: 'url' (the page URL + #slug) or 'anchor' (only #slug)
     */
    public string $copyFormat = 'url';

    /**
     * How deep to look into nested entries for anchors (content builder → row → cell → FAQ items, ...)
     */
    public int $maxDepth = 3;

    /**
     * Handles of the Matrix fields to look into for anchors, e.g. ['contentBuilder', 'gridCells', 'faqItems'].
     * The handle in the field layout, when it differs from the field's own handle. Null looks into every Matrix field.
     *
     * @var string[]|null
     */
    public ?array $fields = null;

    protected function defineRules(): array
    {
        return [
            [['copyFormat'], 'in', 'range' => ['url', 'anchor']],
            [['maxDepth'], 'integer', 'min' => 0],
            [['fields'], 'each', 'rule' => ['string']],
        ];
    }
}
