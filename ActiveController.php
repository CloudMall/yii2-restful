<?php

namespace cloudmall\rest;

class ActiveController extends \yii\rest\ActiveController {

    public $actionSerializer = null;

    private $_currentAction = null;
    /**
     * set json response as default
     *
     * @return array behaviors
     */
    public function behaviors() {
        $behaviors = parent::behaviors();
        $behaviors['contentNegotiator']['formats']['text/html'] = \yii\web\Response::FORMAT_JSON;
        return $behaviors;
    }

    /**
     * get Relations
     *
     * @return array relations
     */
    public function getRelations() {
        $modelClass = $this->modelClass;
        return (new $modelClass())->extraFields();
    }

    /**
     * merge restful action & relation action
     *
     * @return array actions
     */
    public function actions() {
        $restActions = [
            'index' => [
                'class' => 'yii\rest\IndexAction',
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this, 'checkAccess'],
            ],
            'view' => [
                'class' => 'yii\rest\ViewAction',
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this, 'checkAccess'],
            ],
            'create' => [
                'class' => 'yii\rest\CreateAction',
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this, 'checkAccess'],
                'scenario' => $this->createScenario,
            ],
            'update' => [
                'class' => 'yii\rest\UpdateAction',
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this, 'checkAccess'],
                'scenario' => $this->updateScenario,
            ],
            'delete' => [
                'class' => 'yii\rest\DeleteAction',
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this, 'checkAccess'],
            ],
            'options' => [
                'class' => 'yii\rest\OptionsAction',
            ],
        ];
        $allActions = array_merge($restActions, $this->relationActions());
        foreach ($allActions as $actionName => $action) {
            $configMethod = 'getAction' . ucfirst($actionName) . 'Config';
            if (method_exists($this, $configMethod)) {
                $allActions[$actionName] = array_merge($allActions[$actionName], $this->$configMethod());
            }
        }
        return $allActions;
    }

    /**
     * generate relation action from $relation
     *
     * @return array relation actions
     */
    public function relationActions() {
        $actions = [];
        foreach ($this->relations as $relation) {
            $actionName = $relation;
            if (is_array($relation)) {
    	        $actionName = join('-', $relation);
            }
            $actionName = $actionName;
            $actions[$actionName] = [
                'class' => 'cloudmall\rest\Action',
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this, 'checkAccess'],
                'relation' => $relation,
            ];
        }
        return $actions;
    }

    /**
     * This method is invoked right after an action is executed.
     *
     * The method will trigger the [[EVENT_AFTER_ACTION]] event. The return value of the method
     * will be used as the action return value.
     *
     * If you override this method, your code should look like the following:
     *
     * ```php
     * public function afterAction($action, $result)
     * {
     *     $result = parent::afterAction($action, $result);
     *     // your custom code here
     *     return $result;
     * }
     * ```
     *
     * @param Action $action the action just executed.
     * @param mixed $result the action return result.
     * @return mixed the processed action result.
     */
    public function afterAction($action, $result) {
        $this->_currentAction = $action;
        return parent::afterAction($action, $result);
    }

    /**
     * Serializes the specified data.
     * The default implementation will create a serializer based on the configuration given by [[serializer]].
     * It then uses the serializer to serialize the given data.
     * @param mixed $data the data to be serialized
     * @return mixed the serialized data.
     */
    protected function serializeData($data) {
        $action = $this->_currentAction;
        if (property_exists($action, 'serializer') && $action->serializer) {
            if (!array_key_exists('class', $action->serializer)) {
                $action->serializer['class'] = 'cloudmall\rest\Serializer';
            }
            if (!array_key_exists('expand', $action->serializer)) {
                if (property_exists($action, 'expand')) {
                    $action->serializer['expand'] = $action->expand;
                }
            }
            return \Yii::createObject($action->serializer)->serialize($data);
        } else {
            return \Yii::createObject($this->serializer)->serialize($data);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function verbs() {
        return [
            'index' => ['GET', 'HEAD'],
            'view' => ['GET', 'HEAD'],
            'create' => ['POST'],
            'update' => ['POST', 'PUT', 'PATCH'],
            'delete' => ['DELETE'],
        ];
    }
}

