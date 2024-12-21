<?php
// app/View/Components/DataTable.php
namespace App\View\Components;

use Illuminate\View\Component;

class DataTable extends Component
{
    public $id;
    public $columns;
    public $ajaxUrl;
    public $filters;

    public $isCheckbox;

    public $bulkDeleteUrl;

    public $csrfToken;
    
    public function __construct($id, $columns, $ajaxUrl, $csrfToken = null, $bulkDeleteUrl = null, $isCheckbox = false,$filters = [])
    {
        $this->id = $id;
        $this->columns = $columns;
        $this->ajaxUrl = $ajaxUrl;
        $this->csrfToken = $csrfToken;
        $this->bulkDeleteUrl = $bulkDeleteUrl;
        $this->isCheckbox = $isCheckbox;
        $this->filters = $filters;
    }
    
    public function render()
    {
        return view('components.form-components.data-table');
    }
}