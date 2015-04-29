<?php
/**
 * Created by Maxim Omelchenko <omelchenko@samsonos.com>
 * on 31.03.2015 at 18:55
 */

namespace samsoncms\input\material;

use samson\activerecord\dbQuery;
use samson\cms\web\navigation\CMSNav;
use samson\treeview\SamsonTree;

/**
 * SamsonCMS material input module
 * @author Max Omelchenko <omelchenko@samsonos.com>
 */
class Application extends \samsoncms\input\Application
{
    /** @var int Field type number */
    public static $type = 6;

    /** @var string SamsonCMS field class */
    protected $fieldClass = '\samsoncms\input\material\Material';

    /**
     * @return array Asynchronous result array
     */
    public function __async_form($structureId = 0)
    {
        /** @var array $table Result of asynchronous controller
         * Represented as array of rendered table and pager objects */
        $table = $this->__async_table($structureId);

        if ($structureId == 0) {
            // If parent structure is not set, store structure by itself instead
            $parent = isset($parent) ? $parent : CMSNav::fullTree();

            /** @var SamsonTree $tree Tree structure object */
            $tree = new SamsonTree('tree/template', 0, $this->id . '/getchild');

            /** @var string $treeHTML Rendered tree */
            $treeHTML = $tree->htmlTree($parent);
            $treeHide = false;
        } else {
            $treeHTML = '';
            $treeHide = true;
        }


        // Return asynchronous result
        return array(
            'status' => 1,
            'html' => $this->view('form')
                ->set($table)
                ->set('tree', $treeHTML)
                ->set('treeHide', $treeHide)
                ->set('structureID', $structureId)
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

        $response['status'] = true;
        $response['table_html'] = $tableHTML;
        $response['pager_html'] = $pagerHTML;

        // Return Table
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

            /** @var SamsonTree $tree Object to store tree structure */
            $tree = new SamsonTree('tree/template', 0, 'product/addchildren');

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
            $this->createField(new dbQuery(), $_GET['e'], $_GET['f'], $_GET['i']);
            $this->field->save($materialId);
            return array('status' => true, 'material' => $name, 'url' => url_build($this->id, $materialId));
        }
        return array('status' => false);
    }

    public function __async_delete()
    {
        /** @var \samson\activerecord\materialfield $field Materialfield object to store material id */
        $this->createField(new dbQuery(), $_GET['e'], $_GET['f'], $_GET['i']);
        $this->field->save('');
        return array('status'=>true);
    }
}
