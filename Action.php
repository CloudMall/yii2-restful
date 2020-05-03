<?php

namespace cloudMall\rest;

use Yii;
use yii\data\ActiveDataProvider;
use yii\data\DataFilter;

class Action extends \yii\rest\Action {

    public $relation;

    public $dataFilter;
    public $prepareDataProvider;

    public $serializer;


    public $pageParam = 'page';
    public $pageSizeParam = 'per-page';
    public $pageSize = 20;

    /**
     * @return ActiveDataProvider
     */
    public function run($id) {
        $model = $this->findModel($id);
        if ($this->checkAccess) {
            call_user_func($this->checkAccess, $this->id);
        }
        return $this->prepareRelationDataProvider($model);
    }

    /**
     * Prepares the data provider that should return the requested collection of the models.
     * @return ActiveDataProvider
     */
    protected function prepareRelationDataProvider($model) {
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
        /* @var $modelClass \yii\db\BaseActiveRecord */
        $relations = $this->relation;
        if (!is_array($relations)) {
            $relations = [ $this->relation ];
        }
        foreach ($relations as $relation) {
            if ($model instanceof \yii\db\ActiveQuery) {
                $model = $model->one();
                if ($model === null) {
                    throw new \yii\web\HttpException(404, 'item not exists');
                }
            } else if ($model instanceof \yii\db\ActiveRecord) {
                // AR do nothing
            } else {
            }
            $relationMethod = 'get' . ucfirst($relation);
            $reflection = new \ReflectionMethod($model, $relationMethod);
            $relationParams = [];
            foreach ($reflection->getParameters() as $arg) {
                if ($requestParams[$arg->name]) {
                    $relationParams[$arg->name] = $requestParams[$arg->name];
                }
            }
            $query = call_user_func_array([$model, $relationMethod], $relationParams);
            $model = $query;
        }
        if ($query instanceof \yii\db\ActiveQuery) {
            if ($query->multiple) {
                if (!empty($filter)) {
                    $query->andWhere($filter);
                }
                return Yii::createObject([
                    'class' => ActiveDataProvider::className(),
                    'query' => $query,
                    'pagination' => $this->buildPaginationParams($requestParams),
                    'sort' => [
                        'params' => $requestParams,
                    ],
                ]);
            } else {
                return $query->one();
            }
        } else {
            return $query;
        }
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

