<?php

namespace barrelstrength\sproutbaseimport\importers\elements;

use barrelstrength\sproutbaseimport\base\ElementImporter;
use barrelstrength\sproutbaseimport\SproutBaseImport;
use craft\commerce\elements\Product as ProductElement;
use craft\commerce\records\Purchasable;
use Throwable;
use yii\base\Model;

class Product extends ElementImporter
{
    /**
     * @return mixed|string
     */
    public function getModelName(): string
    {
        return ProductElement::class;
    }

    /**
     * @return array
     */
    public function getImporterDataKeys(): array
    {
        return ['variants'];
    }

    /**
     * @param Model $model
     * @param array $settings
     *
     * @return bool|mixed|void
     * @throws Throwable
     */
    public function setModel($model, array $settings = [])
    {
        $this->model = parent::setModel($model, $settings);

        $variants = $settings['variants'] ?? null;
        $rowVariants = [];
        if ($variants) {
            foreach ($variants as $key => $variant) {

                $var = Purchasable::find()->where([
                    'sku' => $variant['sku']
                ])->one();

                if ($var) {
                    $rowVariants[$var->id] = $variant;

                    if (!$this->model->id) {
                        SproutBaseImport::$app
                            ->importUtilities
                            ->addError('exist-'.$variant['sku'], $variant['sku'].' sku already exists');
                    }
                } else {
                    $rowVariants['new'.$key] = $variant;
                }
            }
        }

        /**
         * @var $product ProductElement
         */
        $product = $this->model;

        if ($this->model !== null && count($rowVariants)) {
            $product->setVariants($rowVariants);
        }
    }

    public function getFieldLayoutId($model)
    {
        // TODO: Implement getFieldLayoutId() method.
    }

}