<?php
namespace samson\cms\input;

/**
 * Created by Maxim Omelchenko <omelchenko@samsonos.com>
 * on 24.12.2014 at 13:23
 */

class FieldMaterialTable extends \samson\cms\web\material\Table
{
    /** Table rows count */
    const ROWS_COUNT = 15;

    /** Parent materials CMSNav */
    protected $nav;

    /** Current search keywords */
    protected $search;

    /** Array of drafts for current materials */
    protected $drafts = array();

    /** Array of drafts with out materials */
    protected $single_drafts = array();

    /** Search material fields */
    public $search_fields = array( 'Name', 'Url'  );

    /** Default table template file */
    public $table_tmpl = 'table/index';

    /** Default table row template */
    public $row_tmpl = 'table/row/index';

    /** Default table notfound row template */
    public $notfound_tmpl = 'table/row/notfound';

    /** Default table empty row template */
    public $empty_tmpl = 'table/row/empty';

    /**
     * Constructor
     * @param \samson\cms\Navigation $structure	Parent CMSNav to filter materials
     * @param string $search Keywords to search in materials
     * @param string $page Current table page number
     */
    public function __construct(\samson\cms\Navigation & $structure = null, $search = null, $page = null)
    {
        // Call parent constructor
        parent::__construct($structure, $search, $page);
    }

    /**
     * Function to add query conditions
     * @return void
     */
    public function queryHandler()
    {
        /** @var array $materialIds Array of identifiers of suitable materials */
        $materialIds = array();

        // Fill our recently declared array
        $matIdQuery = dbQuery('samson\cms\CMSNavMaterial');
        if (!empty($this->nav)) {

            $childStructures = array($this->nav->id);
            $stepChildren = array($this->nav->id);

            while (dbQuery('structure_relation')->cond('parent_id', $stepChildren)->fieldsNew('child_id', $stepChildren)) {
                $childStructures = array_merge($childStructures, $stepChildren);
            }

            $matIdQuery->cond('StructureID', $childStructures);
        }
        $matIdQuery->cond('Active', 1)->fields('MaterialID', $materialIds);

        // Add this identifiers as query condition if they exist
        empty($materialIds) ? $this->query->id(0) : $this->query->id($materialIds);
    }

    /**
     * Function to form pager prefix
     * @return string Pager prefix
     */
    public function setPagerPrefix()
    {
        // Generate pager url prefix
        return 'field_material/table/' . (isset($this->nav) ? $this->nav->id : '0') .
        '/' . (isset($this->search{0}) ? $this->search : 'no-search') . '/';
    }

    /**
     * Function to render rows of the table
     * @param \samson\activerecord\material $material Material object to fill row info
     * @return string Rendered row
     */
    public function row(&$material, \samson\pager\Pager & $pager = null)
    {
        // Set table row view context
        m()->view($this->row_tmpl);

//        // If there is Navigation for material pass them
//        if (isset($material->onetomany['_structure'])) {
//            foreach ($material->onetomany['_structure'] as $structure) {
//                m()->set('structure', $structure);
//                break;
//            }
//        }

        // Render row template
        return m()
            ->set($material, 'material')
            ->set('pager', $this->pager)
            ->set('structureId', isset($this->nav) ? $this->nav->id : '0')
            ->output();
    }
}
