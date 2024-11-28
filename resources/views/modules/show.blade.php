@extends('layouts.app')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/modules/show.css') }}">
@endsection

@section('content')
    <div class="container mt-5">
        <h1 class="module-header">{{ $module->name }}</h1>
        <div class="ag-format-container">
            <div class="ag-courses_box">
                @foreach ($lessons as $index => $lesson)
                    @php
                        // Verificar si el módulo requiere restricciones
                        $requiresRestriction = $module->id == 3;

                        // Verificar si la lección anterior está completada
                        $previousLesson = $lessons[$index - 1] ?? null;
                        $isAccessible = !$requiresRestriction || $index === 0 || ($previousLesson && $previousLesson->status);
                    @endphp

                    <div class="ag-courses_item">
                        <!-- Mostrar el número de la lección -->
                        <div class="ag-courses-item-number">#{{ $index + 1 }}</div>

                        @if ($isAccessible)
                            <a href="{{ route('lessons.show', $lesson->id) }}" class="ag-courses-item_link">
                                <!-- Fondo circular -->
                                <div class="ag-courses-item_bg"></div>

                                <!-- Nombre de la lección -->
                                <div class="ag-courses-item_title">
                                    {{ ucwords(str_replace('_', ' ', $lesson->name)) }}
                                </div>

                                <!-- Estado de la lección -->
                                <div class="ag-courses-item_date-box">
                                    <span class="badge {{ $lesson->status ? 'bg-success' : 'bg-warning' }}">
                                        {{ $lesson->status ? 'Completado' : 'Por Hacer' }}
                                    </span>
                                </div>
                            </a>
                        @else
                            <div class="ag-courses-item_link disabled">
                                <!-- Fondo circular -->
                                <div class="ag-courses-item_bg"></div>

                                <!-- Nombre de la lección -->
                                <div class="ag-courses-item_title">
                                    {{ ucwords(str_replace('_', ' ', $lesson->name)) }}
                                </div>

                                <!-- Mensaje de bloqueo -->
                                <div class="ag-courses-item_date-box">
                                    <span class="badge bg-secondary">
                                        Debes completar la lección: {{ ucwords(str_replace('_', ' ', $previousLesson->name)) }}
                                    </span>
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection
