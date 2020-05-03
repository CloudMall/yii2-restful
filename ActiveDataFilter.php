<?php

namespace cloudMall\rest;

class ActiveDataFilter extends \yii\data\ActiveDataFilter {

    /**
     * @param string $value some conditions like 0,1,2,3  or  ok,fail
     * @return array conditions as [0,1,2,3]  or ['ok', 'fail']
     */
    private function splitFunc($value) {
        return is_string($value) ? preg_split('/,/', $value, -1, PREG_SPLIT_NO_EMPTY) : $value;
    }

    /**
     * Builds an operator condition.
     * @param string $operator operator keyword.
     * @param mixed $condition attribute condition.
     * @param string $attribute attribute name.
     * @return array actual condition.
     */
    protected function buildOperatorCondition($operator, $condition, $attribute) {
        if (in_array($operator, $this->multiValueOperators, true)) {
            $condition = $this->splitFunc($condition);
        }
        return parent::buildOperatorCondition($operator, $condition, $attribute);
    }

    /**
     * Validates operator condition.
     * @param string $operator raw operator control keyword.
     * @param mixed $condition attribute condition.
     * @param string $attribute attribute name.
     */
    protected function validateOperatorCondition($operator, $condition, $attribute = null) {
        if ($attribute === null) {
            // absence of an attribute indicates that operator has been placed in a wrong position
            $this->addError($this->filterAttributeName, $this->parseErrorMessage('operatorRequireAttribute', ['operator' => $operator]));
            return;
        }
        $internalOperator = $this->filterControls[$operator];
        // check operator type :
        $operatorTypes = $this->operatorTypes[$internalOperator];
        if ($operatorTypes !== '*') {
            $attributeTypes = $this->getSearchAttributeTypes();
            $attributeType = $attributeTypes[$attribute];
            if (!in_array($attributeType, $operatorTypes, true)) {
                $this->addError($this->filterAttributeName, $this->parseErrorMessage('unsupportedOperatorType', ['attribute' => $attribute, 'operator' => $operator]));
                return;
            }
        }
        if (in_array($internalOperator, $this->multiValueOperators, true)) {
            $condition = $this->splitFunc($condition);
            // multi-value operator:
            if (!is_array($condition)) {
                $this->addError($this->filterAttributeName, $this->parseErrorMessage('operatorRequireMultipleOperands', ['operator' => $operator]));
            } else {
                foreach ($condition as $v) {
                    $this->validateAttributeValue($attribute, $v);
                }
            }
        } else {
            // single-value operator :
            $this->validateAttributeValue($attribute, $condition);
        }
    }
}

