@extends('layouts.app')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/home.css') }}">
@endsection

@section('content')
    <div class="container mt-5 mb-3">
        <div class="row">
            @foreach ($modules as $module)
                <div class="col-md-4 mb-4">
                    <div class="card p-3 position-relative">
                        <!-- Botones para Admin -->
                        @role('Admin')
                            <div class="position-absolute top-0 end-0 p-2">
                                <a href="{{ route('modules.edit', $module->id) }}" class="btn btn-sm btn-primary me-2">Editar</a>
                                <form action="{{ route('modules.disable', $module->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-danger">Deshabilitar</button>
                                </form>
                            </div>
                        @endrole

                        <!-- Contenedor para la imagen o ícono -->
                        <div class="d-flex justify-content-center align-items-center mb-3"
                            style="height: 80px; width: 80px; background-color: #f9f9f9; border-radius: 50%; margin: 0 auto;">
                            @if ($module->image)
                                <img src="{{ asset($module->image) }}" alt="{{ $module->name }}" class="img-fluid"
                                    style="max-height: 100%; max-width: 100%; object-fit: cover; border-radius: 50%;">
                            @else
                                <i class="fas fa-image fa-3x text-muted"></i> <!-- Ícono FontAwesome -->
                            @endif
                        </div>

                        <!-- Información del módulo -->
                        <div class="d-flex justify-content-between">
                            <div class="d-flex flex-column align-items-center">
                                <h6 class="mb-0"><strong>{{ $module->name }}</strong></h6>
                                <span>{{ $module->created_at->diffForHumans() }}</span>
                            </div>
                            <div class="badge">
                                <span>{{ $module->status ? 'Activo' : 'Inactivo' }}</span>
                            </div>
                        </div>

                        <!-- Descripción -->
                        <div class="mt-4">
                            <h3 class="heading">{{ $module->description }}</h3>

                            <!-- Botón "Ver módulo" -->
                            <div class="mt-3">
                                <a href="{{ route('modules.show', $module->id) }}" class="btn btn-primary btn-block w-100">
                                    Ver módulo
                                </a>
                            </div>

                            @if ($module->lessons->count() > 0)
                                <div class="mt-4">
                                    <!-- Progreso dinámico basado en el accesor progress_percentage -->
                                    <div class="progress">
                                        <div class="progress-bar bg-success" role="progressbar"
                                            style="width: {{ $module->progress->progress_percentage ?? 0 }}%"
                                            aria-valuenow="{{ $module->progress->progress_percentage ?? 0 }}"
                                            aria-valuemin="0" aria-valuemax="100">
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        <span class="text1">Progreso:
                                            {{ $module->progress->progress_percentage ?? 0 }}%</span>
                                        <span class="text2"> ({{ $module->lessons->where('status', true)->count() }} de
                                            {{ $module->lessons->count() }} lecciones completadas)</span>
                                    </div>
                                </div>
                            @else
                                <div class="mt-3 text-center">
                                    <span class="text1">Sin lecciones aún</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
