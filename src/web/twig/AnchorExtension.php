<?php

namespace statikbe\entryanchors\web\twig;

use statikbe\entryanchors\EntryAnchors;
use statikbe\entryanchors\fields\data\AnchorData;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * anchor_id(): the id for a block's heading, `<h2 id="{{ anchor_id(block.blockTitle) }}">`.
 */
class AnchorExtension extends AbstractExtension
{
    /** @var array<string, int> Anchors used on this page, to keep ids unique */
    private array $used = [];

    public function getFunctions(): array
    {
        return [
            new TwigFunction('anchor_id', [$this, 'anchorId']),
        ];
    }

    /**
     * The anchor of an Anchor Link field value (or a slug of a plain text), unique on the page: a second block with the same
     * anchor gets `-2`, a third `-3` … The first one keeps the anchor, links to it go there.
     */
    public function anchorId(mixed $value): string
    {
        $anchor = $value instanceof AnchorData
            ? $value->anchor
            : EntryAnchors::getInstance()->getAnchors()->slug(trim((string)$value));

        if ($anchor === '') {
            return '';
        }

        $count = $this->used[$anchor] = ($this->used[$anchor] ?? 0) + 1;

        return $count === 1 ? $anchor : "{$anchor}-{$count}";
    }
}
