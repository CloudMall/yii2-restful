<?php

namespace cloudmall\rest;

class ModuleApi {

    public $module = "";

    /**
     * GET request for list and view
     * @param $url local api uri
     * @param $params args
     *
     * @return data
     */
    public function get($url, $params=[]) {
        $request = \Yii::createObject([
            'class' => 'yii\web\Request',
            'url' => $this->module . '/' . $url,
            'queryParams' => $params,
        ]);
        list($route, $params) = $request->resolve();
        $oldBodyParams = \Yii::$app->getRequest()->getBodyParams();
        $oldQueryParams = \Yii::$app->getRequest()->getQueryParams();
        \Yii::$app->getRequest()->setBodyParams([]);
        \Yii::$app->getRequest()->setQueryParams($params);
        $result = \Yii::$app->runAction($route, $params);
        \Yii::$app->getRequest()->setBodyParams($oldBodyParams);
        \Yii::$app->getRequest()->setQueryParams($oldQueryParams);
        return $result;
    }

    /**
     * POST request for create
     * @param $url local api uri
     * @param $params args
     *
     * @return data
     */
    public function post($url, $params=[]) {
        $request = \Yii::createObject([
            'class' => 'yii\web\Request',
            'url' => $this->module . '/' . $url,
            'bodyParams' => $params,
        ]);
        $oldMethod = $_SERVER['REQUEST_METHOD'];
        $_SERVER['REQUEST_METHOD'] = 'POST';
        list($route, $params) = $request->resolve();
        var_dump($route);exit;
        $oldBodyParams = \Yii::$app->getRequest()->getBodyParams();
        $oldQueryParams = \Yii::$app->getRequest()->getQueryParams();
        \Yii::$app->getRequest()->setBodyParams([]);
        \Yii::$app->getRequest()->setQueryParams($params);
        $result = \Yii::$app->runAction($route, $params);
        $_SERVER['REQUEST_METHOD'] = $oldMethod;
        \Yii::$app->getRequest()->setBodyParams($oldBodyParams);
        \Yii::$app->getRequest()->setQueryParams($oldQueryParams);
        return $result;
    }

    /**
     * PUT request for update
     * @param $url local api uri
     * @param $params args
     *
     * @return data
     */
    public function put($url, $params=[]) {
        $request = \Yii::createObject([
            'class' => 'yii\web\Request',
            'url' => $this->module . '/' . $url,
            'bodyParams' => $params,
        ]);
        $oldMethod = $_SERVER['REQUEST_METHOD'];
        $_SERVER['REQUEST_METHOD'] = 'PUT';
        list($route, $params) = $request->resolve();
        $oldBodyParams = \Yii::$app->getRequest()->getBodyParams();
        $oldQueryParams = \Yii::$app->getRequest()->getQueryParams();
        \Yii::$app->getRequest()->setBodyParams([]);
        \Yii::$app->getRequest()->setQueryParams($params);
        $result = \Yii::$app->runAction($route, $params);
        $_SERVER['REQUEST_METHOD'] = $oldMethod;
        \Yii::$app->getRequest()->setBodyParams($oldBodyParams);
        \Yii::$app->getRequest()->setQueryParams($oldQueryParams);
        return $result;
    }
}


