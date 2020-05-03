<?php

namespace cloudMall\rest;

use Yii;

/**
 * IndexAction implements the API endpoint for listing multiple models.
 *
 * For more details and usage information on IndexAction, see the [guide article on rest controllers](guide:rest-controllers).
 *
 */
class IndexAction extends \yii\rest\IndexAction {

    public $expand = null;
    public $expandParam = 'expand';
    public $fields = null;
    public $fieldsParam = 'fields';

    public $serializer = null;
    
    public $pageParam = 'page';
    public $pageSizeParam = 'per-page';
    public $pageSize = 20;
    
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
     * Prepares the data provider that should return the requested collection of the models.
     * @return ActiveDataProvider
     */
    protected function prepareDataProvider() {
        $requestParams = Yii::$app->getRequest()->getBodyParams();
        if (empty($requestParams)) {
            $requestParams = Yii::$app->getRequest()->getQueryParams();
        }
        $filter = null;
        if ($this->dataFilter !== null) {
            $this->dataFilter = Yii::createObject($this->dataFilter);
            if ($this->dataFilter->load($requestParams)) {
                $filter = $this->dataFilter->build();
                if ($filter === false) {
                    return $this->dataFilter;
                }
            }
        }
        if ($this->prepareDataProvider !== null) {
            return call_user_func($this->prepareDataProvider, $this, $filter);
        }
        $modelClass = $this->modelClass;
        $query = $modelClass::find();
        if (!empty($filter)) {
            $query->andWhere($filter);
        }
        $expand = $this->getModelExpand();
        if ($expand && !empty($expand)) {
            $query->with($expand);
        }
       
        return Yii::createObject([
            'class' => \yii\data\ActiveDataProvider::className(),
            'query' => $query,
            'pagination' => $this->buildPaginationParams($requestParams),
            'sort' => [
                'params' => $requestParams,
            ],
        ]);
    }

    /**
     * build Pagination parameter
     *
     * @param array $requestParams the request parameter 
     * @return array the Pagination parameter value
     */
    protected function buildPaginationParams($requestParams) {
        $paginationParams = [
            'page' => 1,
            'per-page' => $this->pageSize,
        ];
        if (array_key_exists($this->pageSizeParam, $requestParams)) {
            $paginationParams['per-page'] = $requestParams[$this->pageSizeParam];
        }
        if (array_key_exists($this->pageParam, $requestParams)) {
            $paginationParams['page'] = $requestParams[$this->pageParam];
        }
        if ($paginationParams['per-page'] < 0) {
            return false;
        }
        return [
            'params' => $paginationParams,
            'defaultPageSize' => $this->pageSize,
            'pageSizeLimit' => [1, 100000],
        ];
    }
}

