<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LessonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
     function run(): void
    {
        // Obtener los IDs de los módulos
        $modules = DB::table('modules')->pluck('id', 'name');

        // Lecciones para "Saludos Parte 1"
        $saludosParte1 = [
            'hola',
            'adios',
            'gracias',
            'permiso',
            'por_favor',
            'mal',
            'mas_o_menos',
            'no_puedo',
            'puedo'
        ];

        // Lecciones para "Saludos Parte 2"
        $saludosParte2 = [
            'buenas_noches',
            'buenas_tardes',
            'buenos_dias',
            'como_estas',
            'estoy_bien'
        ];

        // Lecciones para "Abecedario"
        $abecedario = range('A', 'Z');

        // Insertar lecciones de "Saludos Parte 1"
        foreach ($saludosParte1 as $lesson) {
            DB::table('lessons')->insert([
                'name' => $lesson,
                'status' => false,
                'module_id' => $modules['Saludos Parte 1'],
                'video_path' => "{$lesson}.mp4",
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Insertar lecciones de "Saludos Parte 2"
        foreach ($saludosParte2 as $lesson) {
            DB::table('lessons')->insert([
                'name' => $lesson,
                'status' => false,
                'module_id' => $modules['Saludos Parte 2'],
                'video_path' => "{$lesson}.mp4",
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Insertar lecciones de "Abecedario"
        foreach ($abecedario as $letter) {
            DB::table('lessons')->insert([
                'name' => $letter,
                'status' => false,
                'module_id' => $modules['Abecedario'],
                'video_path' => "{$letter}.mp4",
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
