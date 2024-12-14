import {
    GestureRecognizer,
    FilesetResolver,
    DrawingUtils,
} from "https://cdn.jsdelivr.net/npm/@mediapipe/tasks-vision@0.10.3";
import { textToSpeech } from "../helpers.js";

console.log("Iniciando el script del modelo de alfabeto...");

// Obtenemos los elementos de la vista
const canvasElement = document.getElementById("output_canvas");
const canvasCtx = canvasElement.getContext("2d");
const toggleCameraButton = document.getElementById("toggle-camera");
const loadingElement = document.getElementById("loading");
const wordDisplayElement = document.querySelector(".word-display");
const correctCountElement = document.getElementById("correct-count");
const nextLessonButton = document.getElementById("next-lesson-btn");
const timerElement = document.getElementById("lesson-timer");

let gestureRecognizer;
let runningMode = "VIDEO";
let webcamRunning = false;
let lastDetectedWord = ""; // Última palabra detectada
let cooldownActive = false; // Controla si el cooldown está activo
let correctCount = 0; // Contador de palabras correctas
let timer = 0; // Temporizador en segundos
let timerInterval;

// `expectedLetter` es definida en el Blade y pasada como variable global.
console.log(`Letra esperada: ${expectedLetter}`);

const videoHeight = 320; // Aseguramos que coincide con el canvas en la vista
const videoWidth = 480; // Ajustamos las proporciones

// Temporizador que inicia 5 segundos después de cargar la página
setTimeout(() => {
    timerInterval = setInterval(() => {
        timer++;
        const minutes = Math.floor(timer / 60);
        const seconds = timer % 60;
        timerElement.textContent = `${String(minutes).padStart(
            2,
            "0"
        )}:${String(seconds).padStart(2, "0")}`;
    }, 1000);
}, 5000);

// Cargar modelo de Mediapipe
const createGestureRecognizer = async () => {
    console.log("Cargando modelo del alfabeto...");
    try {
        const vision = await FilesetResolver.forVisionTasks(
            "https://cdn.jsdelivr.net/npm/@mediapipe/tasks-vision@0.10.3/wasm"
        );
        console.log("Fileset de Vision cargado correctamente.");

        gestureRecognizer = await GestureRecognizer.createFromOptions(vision, {
            baseOptions: {
                modelAssetPath: "../../models/alfabeto/alfabeto.task",
                delegate: "GPU",
            },
            runningMode: runningMode,
        });
        console.log("Modelo cargado correctamente.");
        loadingElement.style.display = "none";
        canvasElement.style.display = "block";
    } catch (error) {
        console.error("Error al cargar el modelo:", error);
    }
};

// Activar cámara y detección
const enableCam = async () => {
    if (!gestureRecognizer) {
        alert("Por favor espera a que el modelo se cargue.");
        return;
    }

    if (webcamRunning) {
        stopCamera();
        return;
    }

    console.log("Activando cámara...");
    const constraints = {
        video: {
            width: videoWidth,
            height: videoHeight,
        },
    };

    try {
        const stream = await navigator.mediaDevices.getUserMedia(constraints);

        // Crear el elemento de video
        const videoElement = document.createElement("video");
        videoElement.setAttribute("id", "webcam");
        videoElement.autoplay = true;
        videoElement.playsInline = true;
        videoElement.muted = true;
        videoElement.width = videoWidth;
        videoElement.height = videoHeight;

        // Obtener el contenedor del canvas y su padre
        const canvasParent = canvasElement.parentElement;

        // Crear un contenedor para centrar video y canvas
        const container = document.createElement("div");
        container.style.display = "flex";
        container.style.flexDirection = "column"; // Para que estén uno sobre otro
        container.style.alignItems = "center"; // Centrado horizontal
        container.style.justifyContent = "center"; // Centrado vertical
        container.style.width = "100%"; // Asegura que ocupe todo el espacio disponible
        videoElement.style.marginTop = "20px";
        // Mover el canvas al nuevo contenedor
        canvasParent.insertBefore(container, canvasElement);
        container.appendChild(videoElement);
        container.appendChild(canvasElement);

        // Configurar el stream para el video
        videoElement.srcObject = stream;

        // Escuchar el evento cuando se cargue el video
        videoElement.addEventListener("loadeddata", () =>
            predictWebcam(videoElement)
        );

        // Marcar que la cámara está activa
        webcamRunning = true;
        toggleCameraButton.textContent = "Apagar Cámara";
        console.log("Cámara activada.");
    } catch (error) {
        console.error("Error al activar la cámara:", error);
    }
};

// Detección continua
async function predictWebcam(videoElement) {
    if (runningMode === "IMAGE") {
        runningMode = "VIDEO";
        await gestureRecognizer.setOptions({ runningMode: "VIDEO" });
    }

    const nowInMs = Date.now();
    const results = await gestureRecognizer.recognizeForVideo(
        videoElement,
        nowInMs
    );

    canvasElement.width = videoElement.videoWidth;
    canvasElement.height = videoElement.videoHeight;

    canvasCtx.save();
    canvasCtx.clearRect(0, 0, canvasElement.width, canvasElement.height);
    canvasCtx.drawImage(
        videoElement,
        0,
        0,
        canvasElement.width,
        canvasElement.height
    );

    const drawingUtils = new DrawingUtils(canvasCtx);

    if (results?.landmarks) {
        for (const landmarks of results.landmarks) {
            drawingUtils.drawConnectors(
                landmarks,
                GestureRecognizer.HAND_CONNECTIONS,
                { color: "#00FF00", lineWidth: 5 }
            );
            drawingUtils.drawLandmarks(landmarks, {
                color: "#FF0000",
                lineWidth: 2,
            });
        }
    }

    canvasCtx.restore();

    if (results?.gestures.length > 0) {
        const detectedLetter =
            results.gestures[0][0].categoryName.toLowerCase();
        console.log(`Letra detectada: ${detectedLetter}`);

        if (!cooldownActive && detectedLetter !== lastDetectedWord) {
            lastDetectedWord = detectedLetter;

            if (wordDisplayElement) {
                wordDisplayElement.textContent = detectedLetter.toUpperCase();
            }

            if (detectedLetter === expectedLetter) {
                console.log("Letra correcta detectada.");
                correctCount++;
                correctCountElement.textContent = correctCount;

                if (correctCount >= 3) {
                    console.log("Se alcanzó el máximo de palabras correctas.");
                    nextLessonButton.classList.remove("disabled");
                    nextLessonButton.removeAttribute("disabled");

                    // Extraer el tiempo del reloj directamente
                    const formattedTime = timerElement.textContent;

                    // Adjuntar el tiempo formateado al enlace del botón
                    nextLessonButton.href += `?time=${formattedTime}`;
                }
            } else {
                console.log(
                    `Letra detectada (${detectedLetter}) no coincide con la esperada (${expectedLetter}).`
                );
            }

            textToSpeech(detectedLetter);

            cooldownActive = true;
            setTimeout(() => {
                cooldownActive = false;
            }, 2000);
        }
    }

    if (webcamRunning) {
        window.requestAnimationFrame(() => predictWebcam(videoElement));
    }
}

// Detener cámara
const stopCamera = () => {
    console.log("Deteniendo cámara...");
    const videoElement = document.getElementById("webcam");
    const stream = videoElement?.srcObject;
    const tracks = stream?.getTracks();

    tracks?.forEach((track) => track.stop());
    videoElement?.remove();
    webcamRunning = false;
    toggleCameraButton.textContent = "Encender Cámara";
    console.log("Cámara detenida.");
};

// Configurar el botón para alternar la cámara
toggleCameraButton.addEventListener("click", enableCam);

// Inicializar el modelo
console.log("Cargando el modelo...");
createGestureRecognizer();
