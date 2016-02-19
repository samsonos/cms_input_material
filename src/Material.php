<?php
/**
 * Created by Maxim Omelchenko <omelchenko@samsonos.com>
 * on 22.12.2014 at 19:23
 */

namespace samsoncms\input\material;

use samsoncms\input\Field;

/**
 * Material SamsonCMS input field
 * @author Maxim Omelchenko <omelchenko@samsonos.com>
 */
class Material extends Field
{
    /** Database object field name */
    protected $param = 'key_value';

    /** {@inheritdoc} */
    public function view($renderer, $saveHandler = '')
    {
        /** @var \samson\activerecord\material $material Additional field material */
        $material = null;

        $structure = (isset($this->dbObject->FieldID) &&
                    dbQuery('field')->cond('FieldID', $this->dbObject->FieldID)->first($field))
                    ? intval($field->Value) : 0;

        //TODO Fixed if later
        $correctID = preg_replace('/[_\d+]+$/', '', $renderer->id());

        $renderer->view($this->defaultView)

            ->set(url_build($correctID, 'delete'), 'deleteController')
            ->set('?f=' . $this->param . '&e=' . $this->entity . '&i=' . $this->dbObject->id, 'getParams');

        if ((int)$this->value() != 0) {
            // If material exists
            if (!$this->dbQuery->className('material')->cond('MaterialID', $this->value())->first($material)) {
                $renderer->set(t('Данный материал не существует! Выберите новый!', true), 'material_Name');
            } else {
                $renderer->set($material, 'material');
            }
        }

        return $renderer->set($correctID, 'id')->set($structure, 'parentID')->output();
    }
}
