<?php

namespace cloudmall\rest;

use Yii;

/**
 * IndexAction implements the API endpoint for listing multiple models.
 *
 * For more details and usage information on IndexAction, see the [guide article on rest controllers](guide:rest-controllers).
 *
 */
class ViewAction extends \yii\rest\ViewAction {

    public $expand = null;
    public $expandParam = 'expand';
    public $fields = null;
    public $fieldsParam = 'fields';

    public $serializer = null;
    
    /**
     * return model expand
     * @return array
     */
    protected function getModelExpand() {
        $expand = \Yii::$app->request->get($this->expandParam);
        $expand = is_string($expand) ? preg_split('/\s*,\s*/', $expand, -1, PREG_SPLIT_NO_EMPTY) : [];
        if ($this->expand !== null) {
            if (is_string($this->expand)) {
                $this->expand = preg_split('/\s*,\s*/', $this->expand, -1, PREG_SPLIT_NO_EMPTY);
            }
            if (array_key_exists('fix', $this->expand)) {
                if (is_string($this->expand['fix'])) {
                    $this->expand['fix'] = preg_split('/\s*,\s*/', $this->expand['fix'], -1, PREG_SPLIT_NO_EMPTY);
                }
                $expand = $this->expand['fix'];
            } else {
                $expand = array_flip(array_merge(array_flip($this->expand), array_flip($expand)));
            }
        }
        return $expand;
    }

    /**
     * Returns the data model based on the primary key given.
     * If the data model is not found, a 404 HTTP exception will be raised.
     * @param string $id the ID of the model to be loaded. If the model has a composite primary key,
     * the ID must be a string of the primary key values separated by commas.
     * The order of the primary key values should follow that returned by the `primaryKey()` method
     * of the model.
     * @return ActiveRecordInterface the model found
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function findModel($id) {
        $model = parent::findModel($id);
        $expand = $this->getModelExpand();
        if ($expand && !empty($expand)) {
            $model->with($expand);
        }
        return $model;
    }
}


