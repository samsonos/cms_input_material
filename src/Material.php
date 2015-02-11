<?php
namespace samsoncms\input\material;

use samson\activerecord\structure;
use samson\cms\web\navigation\CMSNav;
use samson\cms\input\Field;

/**
 * Created by Maxim Omelchenko <omelchenko@samsonos.com>
 * on 22.12.2014 at 19:23
 */

class Material extends Field
{
    protected $id = 'samson_cms_input_material';

    protected $default_view = 'field';

    /**
     * @return array Asynchronous result array
     */
    public function __async_form()
    {
        /** @var array $table Result of asynchronous controller
         * Represented as array of rendered table and pager objects */
        $table = $this->__async_table(0);

        // If parent structure is not set, store structure by itself instead
        $parent = isset($parent) ? $parent : CMSNav::fullTree();

        /** @var \samson\treeview\SamsonTree $tree Tree structure object */
        $tree = new \samson\treeview\SamsonTree('tree/template', 0, $this->id . '/getchild');

        /** @var string $treeHTML Rendered tree */
        $treeHTML = $tree->htmlTree($parent);

        // Return asynchronous result
        return array(
            'status' => 1,
            'html' => $this->view('form')
                ->set($table)
                ->set('tree', $treeHTML)
                ->output()
        );
    }

    /**
     * @param int $structureId Structure identifier to form table
     * @param string $search Search string
     * @param int $page Page number
     * @return array Asynchronous result array
     */
    public function __async_table($structureId, $search = null, $page = null)
    {
        /** @var array $response Asynchronous controller result */
        $response = array('status' => false);

        /** @var \samson\cms\Navigation $structure Object to store selected structure */
        $structure = null;

        // Try to find it in database
        dbQuery('\samson\cms\Navigation')->id($structureId)->first($structure);

        /** @var FieldMaterialTable $table Object to store set of materials */
        $table = new FieldMaterialTable($structure, $search, $page);

        /** @var string $tableHTML Rendered table */
        $tableHTML = $table->render();

        /** @var string $pager_html Rendered pager */
        $pagerHTML = $table->pager->toHTML();

        // Return table

        $response['status'] = true;
        $response['table_html'] = $tableHTML;
        $response['pager_html'] = $pagerHTML;

        return $response;
    }

    /**
     * Function to retrieve tree structure
     * @param int $structureId Current structure identifier
     * @return array Asynchronous result
     */
    public function __async_getchild($structureId)
    {
        /** @var \samson\cms\Navigation $structure Current structure object */
        $structure = null;

        // If structure was found by Identifier
        if (dbQuery('\samson\cms\Navigation')->cond('StructureID', $structureId)->first($structure)) {

            /** @var \samson\treeview\SamsonTree $tree Object to store tree structure */
            $tree = new \samson\treeview\SamsonTree('tree/template', 0, 'product/addchildren');

            // Asynchronous controller performed and JSON object is returned
            return array('status' => 1, 'tree' => $tree->htmlTree($structure));
        }

        // Asynchronous controller failed
        return array('status' => 0);
    }

    public function __async_confirm($materialId)
    {
        $name = null;
        if (dbQuery('material')->cond('MaterialID', $materialId)->fieldsNew('Name', $name)) {
            $name = $name[0];
            /** @var \samson\activerecord\materialfield $field Materialfield object to store material id */
            $field = Field::fromMetadata($_GET['e'], $_GET['f'], $_GET['i']);
            $field->save($materialId);
            return array('status' => true, 'material' => $name);
        }
        return array('status' => false);
    }

    public function __async_delete()
    {
        /** @var \samson\activerecord\materialfield $field Materialfield object to store material id */
        $field = Field::fromMetadata($_GET['e'], $_GET['f'], $_GET['i']);
        $field->save('');
        return array('status'=>true);
    }

    /** @see \samson\core\iModuleViewable::toView() */
    public function toView($prefix = null, array $restricted = array())
    {
        $materialName = null;

        if (dbQuery('material')->cond('MaterialID', $this->obj->Value)->fieldsNew('Name', $materialName)) {
            $materialName = $materialName[0];
        } else {
            $materialName = t('Данный материал не существует! Выберите новый!', true);
        }
        // Generate controller links
        $this->set('getattr', '?f='.$this->param.'&e='.$this->entity.'&i='.$this->obj->id)
            ->set('delete_controller', $this->id.'/delete?f='.$this->param.'&e='.$this->entity.'&i='.$this->obj->id)
            ->set('name', $materialName);

        //$this->set('empty_text', 'Выберите текст');
        // Call parent rendering routine
        return parent::toView($prefix, $restricted);
    }
}
