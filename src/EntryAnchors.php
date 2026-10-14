<?php

namespace statikbe\entryanchors;

use Craft;
use craft\base\Field;
use craft\base\Model;
use craft\base\Plugin;
use craft\events\DefineFieldHtmlEvent;
use craft\events\RegisterComponentTypesEvent;
use craft\fields\Link;
use craft\services\Fields;
use statikbe\entryanchors\fields\AnchorLink;
use statikbe\entryanchors\models\Settings;
use statikbe\entryanchors\services\Anchors;
use statikbe\entryanchors\services\LinkFields;
use statikbe\entryanchors\web\twig\AnchorExtension;
use yii\base\Event;

/**
 * Entry Anchors: anchor titles for blocks, and an anchor dropdown on Link fields to link to a block on an entry.
 *
 * The "Anchor Link" field holds a block's title and its anchor, which is kept when the title changes; the front end renders it
 * as id with anchor_id(). Link fields get dropdowns with the anchors of the linked entry or of the page itself.
 *
 * @method static EntryAnchors getInstance()
 * @method Settings getSettings()
 */
class EntryAnchors extends Plugin
{
    public string $schemaVersion = '1.0.0';

    public static function config(): array
    {
        return [
            'components' => [
                'anchors' => Anchors::class,
                'linkFields' => LinkFields::class,
            ],
        ];
    }

    public function init(): void
    {
        parent::init();

        Event::on(Fields::class, Fields::EVENT_REGISTER_FIELD_TYPES, function(RegisterComponentTypesEvent $event) {
            $event->types[] = AnchorLink::class;
        });

        Craft::$app->getView()->registerTwigExtension(new AnchorExtension());

        if (Craft::$app->getRequest()->getIsCpRequest()) {
            Event::on(Link::class, Field::EVENT_DEFINE_INPUT_HTML, function(DefineFieldHtmlEvent $event) {
                $this->getLinkFields()->defineInputHtml($event);
            });
        }
    }

    public function getAnchors(): Anchors
    {
        /** @var Anchors */
        return $this->get('anchors');
    }

    public function getLinkFields(): LinkFields
    {
        /** @var LinkFields */
        return $this->get('linkFields');
    }

    protected function createSettingsModel(): ?Model
    {
        return new Settings();
    }
}
