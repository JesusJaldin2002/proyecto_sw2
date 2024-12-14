@extends('layouts.app')

@section('content')
    <div class="container mt-5">
        <h1 class="text-center">Enviar Notificación</h1>
        <div class="row justify-content-center">
            <div class="col-md-8">
                @if (session('success'))
                    <div class="alert alert-success mt-3">
                        {{ session('success') }}
                    </div>
                @endif
                <div class="card">

                    <div class="card-body">
                        <form method="POST" action="{{ route('notifications.store') }}">
                            @csrf
                            <div class="mb-3">
                                <label for="title" class="form-label">Título de la notificación:</label>
                                <input type="text" name="title" id="title" class="form-control"
                                    placeholder="Ingresa el título de la notificación" required>
                            </div>

                            <div class="mb-3">
                                <label for="message" class="form-label">Mensaje:</label>
                                <textarea name="message" id="message" class="form-control" rows="4"
                                    placeholder="Escribe el mensaje de la notificación" required></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="type" class="form-label">Tipo de Notificación:</label>
                                <select name="type" id="type" class="form-select" required>
                                    <option value="" disabled selected>Selecciona un tipo</option>
                                    <option value="info">Información</option>
                                    <option value="alert">Alerta</option>
                                    <option value="warning">Advertencia</option>
                                </select>
                            </div>

                            <div class="text-center">
                                <button type="submit" class="btn btn-primary">Enviar Notificación</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
