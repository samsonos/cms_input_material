<?php
namespace samson\cms\input\fieldmaterial;

use samson\activerecord\structure;

/**
 * Created by Maxim Omelchenko <omelchenko@samsonos.com>
 * on 22.12.2014 at 19:23
 */

class Material extends Field
{
    protected $id = 'field_material';

    /**
     * @param int $structureId Selected structure identifier
     * @param string $search Search string
     * @param int $page Page number
     * @return array Asynchronous result array
     */
    public function __async_form($structureId, $search = null, $page = null)
    {
        /** @var \samson\cms\Navigation $structure Object to store selected structure */
        $structure = null;

        // If structure identifier is set
        if (isset($structureId)) {
            // Try to find it in database and save
            dbQuery('\samson\cms\Navigation')->id($structureId)->first($structure);
            // Get parent structure
            $parent = $structure->parent();
        }

        /** @var array $table Result of asynchronous controller
         * Represented as array of rendered table and pager objects */
        $table = $this->__async_table($structure, $search, $page);

        // If parent structure is not set, store structure by itself instead
        $parent = isset($parent) ? $parent : $structure;

        /** @var \samson\treeview\SamsonTree $tree Tree structure object */
        $tree = new \samson\treeview\SamsonTree('tree/template', 0, 'field_material/getchild');

        /** @var string $treeHTML Rendered tree */
        $treeHTML = $tree->htmlTree($parent);

        // Return asynchronous result
        return array(
            'status' => 1,
            'html' => $this->view('index')
                ->set($table)
                ->set('tree', $treeHTML)
                ->output()
        );
    }

    /**
     * @param \samson\cms\Navigation $structure Structure object to form table
     * @param string $search Search string
     * @param int $page Page number
     * @return array Asynchronous result array
     */
    public function __async_table($structure, $search = null, $page = null)
    {
        /** @var Table $table Object to store set of materials */
        $table = new Table($structure, $search, $page);

        /** @var string $tableHTML Rendered table */
        $tableHTML = $table->render();

        /** @var string $pager_html Rendered pager */
        $pagerHTML = $table->pager->toHTML();

        // Return table
        return array(
            'status' => 1,
            'table_html' => $tableHTML,
            'pager_html' => $pagerHTML,
        );
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
}
