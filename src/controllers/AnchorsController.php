<?php

namespace statikbe\entryanchors\controllers;

use Craft;
use craft\elements\Entry;
use craft\web\Controller;
use statikbe\entryanchors\EntryAnchors;
use yii\web\Response;

class AnchorsController extends Controller
{
    public function beforeAction($action): bool
    {
        $this->requireCpRequest();

        return parent::beforeAction($action);
    }

    /**
     * The anchors of an entry, for the anchor dropdown on Link fields.
     */
    public function actionList(): Response
    {
        $entryId = $this->request->getRequiredParam('entryId');
        $siteId = $this->request->getParam('siteId') ?: null;

        $entry = Entry::find()
            ->id($entryId)
            ->siteId($siteId)
            ->status(null)
            ->one();

        if ($entry && !Craft::$app->getElements()->canView($entry)) {
            $entry = null;
        }

        return $this->asJson([
            'options' => EntryAnchors::getInstance()->getAnchors()->options($entry),
        ]);
    }

    /**
     * The anchor (slug) for a title, for the copy button of Anchor Text fields.
     */
    public function actionSlug(): Response
    {
        $title = (string)$this->request->getRequiredParam('title');

        return $this->asJson([
            'slug' => EntryAnchors::getInstance()->getAnchors()->slug($title),
        ]);
    }
}
