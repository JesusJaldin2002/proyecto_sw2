<?php

namespace App\Http\Controllers;

use App\Models\Module;
use Illuminate\Support\Facades\Auth as Ath;

class ModuleController extends Controller
{
    public function edit() {}

    public function update() {}

    public function enable() {}

    public function disable() {}

    public function show($id)
    {
        $module = Module::findOrFail($id);

        $lessons = $module->lessons()
            ->with(['progresses' => function ($query) {
                $query->where('user_id', Ath::user()->id);
            }])
            ->orderBy('id')
            ->get();

        return view('modules.show', compact('module', 'lessons'));
    }
}
