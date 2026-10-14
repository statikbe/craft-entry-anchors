<?php

namespace statikbe\entryanchors\fields\data;

use craft\base\Serializable;
use Stringable;

/**
 * The value of an Anchor Link field: the block's title and its anchor (slug).
 *
 * The anchor is set from the title once and then kept, so links to it keep working when the title changes. In templates the
 * value is the title: `{{ block.blockTitle }}`, `block.blockTitle|length`; the anchor is `block.blockTitle.anchor`, or
 * `anchor_id(block.blockTitle)` for the id of the heading.
 */
class AnchorData implements Serializable, Stringable
{
    public function __construct(
        public readonly string $title,
        public readonly string $anchor,
    ) {
    }

    public function __toString(): string
    {
        return $this->title;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getAnchor(): string
    {
        return $this->anchor;
    }

    /**
     * @return array{title: string, anchor: string}
     */
    public function serialize(): array
    {
        return [
            'title' => $this->title,
            'anchor' => $this->anchor,
        ];
    }
}
