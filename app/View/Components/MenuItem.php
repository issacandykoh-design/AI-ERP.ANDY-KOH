<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class MenuItem extends Component
{

    public $icon;
    public $text;
    public $link;
    public $active;
    public $addon;
    public $count;
    public $menuKey;
    public $attributes;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($icon, $text, $link = null, $active = false, $addon = false, $count = 0, $menuKey = null, $attributes = [])
    {
        $this->icon = $icon;
        $this->link = $link;
        $this->active = $active;
        $this->addon = $addon;
        $this->count = $count;
        $this->menuKey = $menuKey;
        $this->attributes = $attributes;
        
        // Use custom menu name only when current locale matches company default
        // Use per-locale custom menu name if available, else translated text
        if ($menuKey && function_exists('get_custom_menu_name')) {
            $this->text = get_custom_menu_name($menuKey, $text);
        } else {
            $this->text = $text;
        }
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|string
     */
    public function render()
    {
        return view('components.menu-item');
    }

}
