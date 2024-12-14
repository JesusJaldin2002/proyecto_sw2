<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear los seccións con imágenes
        $modules = [
            [
                'name' => 'Saludos Parte 1',
                'description' => 'Primer sección de saludos básicos',
                'status' => true,
                'image' => 'images/saludos1.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Saludos Parte 2',
                'description' => 'Segunda sección de saludos avanzados',
                'status' => true,
                'image' => 'images/saludos2.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Abecedario',
                'description' => 'Sección con el abecedario completo',
                'status' => true,
                'image' => 'images/abecedario.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'General',
                'description' => 'Sección general para futuras lecciones',
                'status' => true,
                'image' => 'images/general.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($modules as $module) {
            $moduleId = DB::table('modules')->insertGetId($module);

            // DB::table('progresses')->insert([
            //     'module_id' => $moduleId,
            //     'completion_date' => null,
            //     'status' => 'Incompleto',
            //     'time_spent' => '0:00:00',
            //     'created_at' => now(),
            //     'updated_at' => now(),
            // ]);
        }
    }
}
