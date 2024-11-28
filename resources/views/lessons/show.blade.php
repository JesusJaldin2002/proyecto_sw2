@extends('layouts.app')

@section('scriptsTop')
    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/camera_utils/camera_utils.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/control_utils/control_utils.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/drawing_utils/drawing_utils.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/holistic/holistic.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@tensorflow/tfjs@latest/dist/tf.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@tensorflow/tfjs-backend-wasm/dist/tf-backend-wasm.js"></script>

    <script type="module">
        // Define the `expectedLetter` as a global variable to be used in the JavaScript
        window.expectedLetter = "{{ strtolower($lesson->name) }}";

        const lessonId = "{{ $lesson->module->id }}";
        const scriptPath = `{{ asset('js') }}/saludos${lessonId}/saludos${lessonId}.js`;

        console.log(`Intentando cargar el script desde: ${scriptPath}`);
        console.log(`Letra esperada definida como: ${window.expectedLetter}`);

        const scriptElement = document.createElement("script");
        scriptElement.type = "module";
        scriptElement.defer = true; // Ensure script runs after the DOM is loaded
        scriptElement.src = scriptPath;
        scriptElement.onload = () => console.log("Script cargado exitosamente.");
        scriptElement.onerror = () => console.error(`Error al cargar el script desde ${scriptPath}`);
        document.head.appendChild(scriptElement);
    </script>
@endsection

@section('content')
    <div class="container mt-4">
        <div class="row mt-4">
            <!-- Botón Volver -->
            <div class="col-6">
                <button class="btn btn-primary w-100" onclick="history.back()">Volver Atrás</button>
            </div>
            <!-- Botón Siguiente Lección -->
            @if($lesson->module->id == 3)
                <div class="col-6 text-right">
                    <a href="{{ route('lessons.next', $lesson->id) }}" 
                        class="btn btn-success w-100 disabled" 
                        id="next-lesson-btn">
                        Siguiente Lección
                    </a>
                </div>
            @endif
        </div>

        <div class="row mt-4">
            <h1>{{ $lesson->name }}</h1>
            <!-- Video de tutorial -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header text-center">Video Tutorial</div>
                    <div class="card-body">
                        <video id="tutorial-video" width="100%" autoplay loop muted>
                            <source src="{{ asset('videos/' . $lesson->video_path) }}" type="video/mp4">
                            Tu navegador no soporta el video.
                        </video>
                    </div>
                </div>
            </div>

            <!-- Cámara -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header text-center">
                        <button id="toggle-camera" class="btn btn-secondary">Encender Cámara</button>
                    </div>
                    <div class="card-body">
                        <canvas id="output_canvas" width="100%" height="320" style="display: none;"></canvas>
                        <div id="loading" class="text-center">Cargando...</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <!-- Palabra -->
            <div class="col-12">
                <div class="text-center">
                    <h2 class="word-display">{{ ucwords(str_replace('_', ' ', $lesson->name)) }}</h2>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <!-- Contador de palabras correctas -->
            <div class="col-12 text-center">
                <h4>Contador de Palabras Bien</h4>
                <div class="counter-display">
                    <span id="correct-count">0</span> / <span id="total-words">5</span>
                </div>
            </div>
        </div>
    </div>
@endsection
