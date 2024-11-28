<?php

namespace App\Http\Controllers;

use App\Models\Module;
use Illuminate\Http\Request;

class ModuleController extends Controller
{
    public function edit() {}

    public function update() {}

    public function enable() {}

    public function disable() {}

    public function show($id)
    {
        $module = Module::findOrFail($id);
        $lessons = $module->lessons()->orderBy('id')->get();
        return view('modules.show', compact('module', 'lessons'));
    }
}
