<?php

namespace cloudMall\rest;

class UrlRule extends \yii\rest\UrlRule {
    public $patterns = [
        'POST,PUT,PATCH {id}' => 'update',
        'DELETE {id}' => 'delete',
        'GET,HEAD {id}' => 'view',
        'POST' => 'create',
        'GET,HEAD' => 'index',
        '{id}' => 'options',
        '' => 'options',
    ];
}
