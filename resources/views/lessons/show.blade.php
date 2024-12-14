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
        <!-- Cabecera con el reloj -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="module-title"><strong>Lección: </strong>{{ ucwords(str_replace('_', ' ', $lesson->name)) }}</h1>
            <div class="lesson-timer-container">
                <h4 style="font-family: roboto"><strong>Tiempo en la Lección</strong></h4>
                <div id="lesson-timer" style="font-size: 20px" class="timer-display">00:00</div>
            </div>
        </div>

        <!-- Botones de navegación -->
        <div class="row mt-2 mb-4">
            <div class="col-md-6">
                <a class="btn btn-primary w-100" href="{{ route('modules.show', $lesson->module->id) }}">Volver Atrás</a>
            </div>
            <div class="col-md-6 text-right">
                <a href="{{ route('lessons.next', $lesson->id) }}" class="btn btn-success w-100 disabled" id="next-lesson-btn">
                    Siguiente Lección
                </a>
            </div>
        </div>

        <!-- Contenido principal -->
        <div class="row">
            <!-- Video Tutorial -->
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-header text-center bg-secondary text-white"><strong>Video Tutorial</strong></div>
                    <div class="card-body">
                        <video id="tutorial-video" width="100%" autoplay loop muted class="rounded shadow">
                            <source src="{{ asset('videos/' . $lesson->video_path) }}" type="video/mp4">
                            Tu navegador no soporta el video.
                        </video>
                    </div>
                </div>
            </div>

            <!-- Cámara -->
            <div class="col-md-7">
                <div class="card shadow-sm">
                    <div class="card-header text-center bg-secondary text-white">
                        <button id="toggle-camera" class="btn btn-light">Encender Cámara</button>
                    </div>
                    <div class="card-body text-center">
                        <canvas id="output_canvas" width="100%" height="320" style="display: none;" ></canvas>
                        <div id="loading" class="text-center mt-3">Cargando...</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Palabra actual -->
        <div class="row mt-4">
            <div class="col-12 text-center">
                <h2 class="word-display bg-light py-3 px-4 rounded shadow">
                    {{ ucwords(str_replace('_', ' ', $lesson->name)) }}
                </h2>
            </div>
        </div>

        <!-- Contador de palabras correctas -->
        <div class="row mt-4">
            <div class="col-12 text-center">
                <h4 class="mb-3">Contador de Palabras Correctas</h4>
                <div class="counter-display bg-light py-3 px-4 rounded shadow d-inline-block">
                    <span id="correct-count">0</span> / <span id="total-words">3</span>
                </div>
            </div>
        </div>
    </div>
@endsection
