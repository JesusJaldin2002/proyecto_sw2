<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TestProgressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userId = 1; // ID del usuario
        $moduleName = 'Saludos Parte 1'; // Nombre del módulo

        // Obtener el ID del módulo "Saludos Parte 1"
        $moduleId = DB::table('modules')->where('name', $moduleName)->value('id');

        if (!$moduleId) {
            $this->command->info("El módulo '{$moduleName}' no existe.");
            return;
        }

        // Obtener las primeras 3 lecciones del módulo
        $lessons = DB::table('lessons')
            ->where('module_id', $moduleId)
            ->take(3)
            ->get();

        if ($lessons->isEmpty()) {
            $this->command->info("No hay lecciones para el módulo '{$moduleName}'.");
            return;
        }

        foreach ($lessons as $lesson) {
            // Crear registro en lesson_user
            DB::table('lesson_user')->insert([
                'lesson_id' => $lesson->id,
                'user_id' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Crear registro en progresses
            DB::table('progresses')->insert([
                'lesson_id' => $lesson->id,
                'user_id' => $userId,
                'completion_at' => now(),
                'status' => 'completed',
                'time_spent' => '02:05', // Ejemplo de tiempo
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info("Progreso registrado para las 3 primeras lecciones del módulo '{$moduleName}'.");
    }
}
