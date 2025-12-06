<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ModuleCard extends Component
{
    public $module;
    public $type;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($module, $type = 'custom')
    {
        $this->module = $module;
        $this->type = $type;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|string
     */
    public function render()
    {
        return view('components.module-card');
    }
}