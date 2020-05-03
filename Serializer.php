<?php

namespace cloudMall\rest;

class Serializer extends \yii\rest\Serializer {

    public $fields = null;
    public $expand = null;

    /**
     * @return array the names of the requested fields. The first element is an array
     * representing the list of default fields requested, while the second element is
     * an array of the extra fields requested in addition to the default fields.
     * @see Model::fields()
     * @see Model::extraFields()
     */
    protected function getRequestedFields() {
        $fields = $this->request->get($this->fieldsParam);
        $fields = is_string($fields) ? preg_split('/\s*,\s*/', $fields, -1, PREG_SPLIT_NO_EMPTY) : [];
        $expand = $this->request->get($this->expandParam);
        $expand = is_string($expand) ? preg_split('/\s*,\s*/', $expand, -1, PREG_SPLIT_NO_EMPTY) : [];
        if ($this->fields !== null) {
            if (is_string($this->fields)) {
                $this->fields = preg_split('/\s*,\s*/', $this->fields, -1, PREG_SPLIT_NO_EMPTY);
            }
            if (array_key_exists('fix', $this->fields)) {
                $fields = $this->fields['fix'];
            } else if (array_key_exists('default', $this->fields)) {
                $fields = array_flip(array_merge(array_flip($this->fields['default']), array_flip($fields)));
            } else if (array_key_exists('addition', $this->fields)) {
                $fields = array_flip(array_flip($fields), array_merge(array_flip($this->fields['default'])));
            } else {
                $fields = array_flip(array_merge(array_flip($this->fields), array_flip($fields)));
            }
        }
        if ($this->expand !== null) {
            if (is_string($this->expand)) {
                $this->expand = preg_split('/\s*,\s*/', $this->expand, -1, PREG_SPLIT_NO_EMPTY);
            }
            if (array_key_exists('fix', $this->expand)) {
                $expand = $this->expand['fix'];
            } else if (array_key_exists('default', $this->expand)) {
                $expand = array_flip(array_merge(array_flip($this->expand['default']), array_flip($expand)));
            } else if (array_key_exists('addition', $this->expand)) {
                $expand = array_flip(array_flip($expand), array_merge(array_flip($this->expand['default'])));
            } else {
                $expand = array_flip(array_merge(array_flip($this->expand), array_flip($expand)));
            }
        }
        return [
            $fields,
            $expand,
        ];
    }

    /**
     * @param DataProvider $dataProvider 数据提供
     *
     * @return array data
     */
    protected function serializeDataProvider($dataProvider) {
        if ($this->preserveKeys) {
            $models = $dataProvider->getModels();
        } else {
            $models = array_values($dataProvider->getModels());
        }
        $models = $this->serializeModels($models);
        if (($pagination = $dataProvider->getPagination()) !== false) {
            $this->addPaginationHeaders($pagination);
        }
        if ($this->request->getIsHead()) {
            return null;
        } elseif ($this->collectionEnvelope === null) {
            return $models;
        }
        $result = [
            $this->collectionEnvelope => $models,
        ];
        if ($pagination !== false) {
            $result['record_num'] = $pagination->totalCount;
            $result['currentPage'] = $pagination->getPage() + 1;
            $result['pageCount'] = $pagination->getPageCount();
            $result['pageSize'] = $pagination->getPageSize();
        } else {
            $result['record_num'] = count($models);
        }
        return $result;
    }

}

