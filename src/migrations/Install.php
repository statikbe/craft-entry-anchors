<?php

namespace statikbe\entryanchors\migrations;

use Craft;
use craft\db\Migration;
use craft\services\ProjectConfig;
use statikbe\entryanchors\fields\AnchorLink;

/**
 * Takes over the Anchor Text fields of the Statik base install's module (`modules\statik\fields\AnchorLink`), so their content
 * stays where it is: the field type is a plain string either way.
 */
class Install extends Migration
{
    public const LEGACY_FIELD_TYPES = [
        'modules\statik\fields\AnchorLink',
    ];

    public function safeUp(): bool
    {
        $projectConfig = Craft::$app->getProjectConfig();

        // On other environments the YAML already has the new type, it comes with the plugin install
        if ($projectConfig->getIsApplyingExternalChanges()) {
            return true;
        }

        foreach ($projectConfig->get(ProjectConfig::PATH_FIELDS) ?? [] as $uid => $config) {
            if (in_array($config['type'] ?? null, self::LEGACY_FIELD_TYPES, true)) {
                $config['type'] = AnchorLink::class;
                // The module's field had an unused "someAttribute" setting
                $config['settings'] = [];
                $projectConfig->set(ProjectConfig::PATH_FIELDS . ".$uid", $config, 'Take over the field from the Statik module');
            }
        }

        return true;
    }

    public function safeDown(): bool
    {
        return true;
    }
}
