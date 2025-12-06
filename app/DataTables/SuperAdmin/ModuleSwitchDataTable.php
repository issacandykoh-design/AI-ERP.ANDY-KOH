<?php

namespace App\DataTables\SuperAdmin;

use App\DataTables\BaseDataTable;
use App\Models\ModuleSwitch;
use Yajra\DataTables\Html\Column;

class ModuleSwitchDataTable extends BaseDataTable
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {
        $datatables = datatables()->eloquent($query);
        $datatables->addIndexColumn();

        $datatables->addColumn('check', function ($row) {
            return '<input type="checkbox" class="select-table-row" id="datatable-row-' . $row->id . '"  name="datatable_ids[]" value="' . $row->id . '" onclick="dataTableRowCheck(' . $row->id . ')">';
        });

        $datatables->addColumn('action', function ($row) {
            $action = '<div class="task_view">
                <div class="dropdown">
                    <a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type="link"
                        id="dropdownMenuLink-' . $row->id . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="icon-options-vertical icons"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink-' . $row->id . '" tabindex="0">';

            $action .= '<a class="dropdown-item show-module-details" href="javascript:;" data-module-id="' . $row->id . '">
                        <i class="fa fa-eye mr-2"></i>
                        ' . trans('app.view') . '
                    </a>';

            $action .= '</div>
                    </div>
                </div>';

            return $action;
        });

        $datatables->addColumn('module_name', function ($row) {
            return '<div class="media align-items-center">
                        <div class="media-body">
                            <h5 class="mb-0 f-13 text-darkest-grey">' . $row->display_name . '</h5>
                            <p class="mb-0 f-12 text-grey">' . $row->module_name . '</p>
                        </div>
                    </div>';
        });

        $datatables->addColumn('status', function ($row) {
            $checked = $row->is_enabled ? 'checked' : '';
            return '<div class="custom-control custom-switch">
                        <input type="checkbox" ' . $checked . ' class="custom-control-input toggle-module-status" 
                               id="customSwitch' . $row->id . '" data-module-id="' . $row->id . '">
                        <label class="custom-control-label" for="customSwitch' . $row->id . '"></label>
                    </div>';
        });

        $datatables->addColumn('description', function ($row) {
            return $row->description ?: '-';
        });

        $datatables->addColumn('version', function ($row) {
            return $row->version;
        });

        $datatables->filterColumn('searchText', function ($query, $keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('module_name', 'like', '%' . $keyword . '%')
                  ->orWhere('display_name', 'like', '%' . $keyword . '%')
                  ->orWhere('description', 'like', '%' . $keyword . '%');
            });
        });

        $datatables->filterColumn('status', function ($query, $keyword) {
            if ($keyword == 'enabled') {
                $query->where('is_enabled', 1);
            } elseif ($keyword == 'disabled') {
                $query->where('is_enabled', 0);
            }
        });

        $datatables->smart(false);
        $datatables->rawColumns(['check', 'action', 'module_name', 'status', 'description', 'version']);

        return $datatables;
    }

    /**
     * @param ModuleSwitch $model
     * @return ModuleSwitch|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function query(ModuleSwitch $model)
    {
        return $model->query();
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        $dataTable = $this->setBuilder('module-switch-table', 2)
            ->parameters([
                'initComplete' => 'function () {
                    window.LaravelDataTables["module-switch-table"].buttons().container()
                     .appendTo( "#table-actions")
                 }',
                'fnDrawCallback' => 'function( oSettings ) {
                   $(".select-picker").selectpicker();
                 }',
            ]);

        return $dataTable;
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        return [
            'check' => [
                'title' => '<input type="checkbox" name="select_all_table" id="select-all-table" onclick="selectAllTable(this)">',
                'exportable' => false,
                'orderable' => false,
                'searchable' => false
            ],
            '#' => ['data' => 'DT_RowIndex', 'orderable' => false, 'searchable' => false, 'visible' => false],
            __('app.module') => ['data' => 'module_name', 'name' => 'module_name', 'title' => __('app.module')],
            __('app.description') => ['data' => 'description', 'name' => 'description', 'title' => __('app.description')],
            __('app.version') => ['data' => 'version', 'name' => 'version', 'title' => __('app.version')],
            __('app.status') => ['data' => 'status', 'name' => 'is_enabled', 'title' => __('app.status')],
            Column::computed('action', __('app.action'))
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false)
                ->addClass('text-right pr-20')
        ];
    }
}