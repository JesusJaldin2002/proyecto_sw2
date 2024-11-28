import {
    extractKeypoints,
    thereHand,
    drawGuidelines,
    textToSpeech,
} from "../helpers.js";

const canvasElement = document.getElementById("output_canvas");
const canvasCtx = canvasElement.getContext("2d");
const toggleCameraButton = document.getElementById("toggle-camera");
const loadingElement = document.getElementById("loading");
const wordDisplayElement = document.querySelector(".word-display");
const correctCountElement = document.getElementById("correct-count");
const totalWordsElement = document.getElementById("total-words");

let camera = null;
let kpSequence = [];
let countFrame = 0;
let handsPresent = false; // Estado de las manos
let correctWords = 0;
const threshold = 0.8;
const MAX_LENGTH_FRAMES = 15; // Máximo de frames para predicción
const MIN_LENGTH_FRAMES = 10; // Mínimo de frames para predicción

// Lista de palabras a detectar
const actions = [
    "adios",
    "gracias",
    "hola",
    "mal",
    "mas_o_menos",
    "no_puedo",
    "permiso",
    "por_favor",
    "puedo",
];

// Palabra actual que el usuario debe realizar
let currentWordIndex = 0;
wordDisplayElement.textContent = actions[currentWordIndex]
    .replace(/_/g, " ")
    .toUpperCase();
totalWordsElement.textContent = actions.length; // Total de palabras

tf.setBackend("wasm").then(async () => {
    const model = await tf.loadLayersModel("../../models/saludos1/model.json");

    loadingElement.style.display = "none";
    canvasElement.style.display = "block";

    function updateDetectedWord(word) {
        if (wordDisplayElement) {
            wordDisplayElement.textContent = word
                .replace(/_/g, " ")
                .toUpperCase();
        }
    }

    let cooldown = false; // Estado para evitar predicciones consecutivas

    function onResults(results) {
        canvasCtx.save();
        canvasCtx.clearRect(0, 0, canvasElement.width, canvasElement.height);
        canvasCtx.drawImage(
            results.image,
            0,
            0,
            canvasElement.width,
            canvasElement.height
        );

        // Verificar si las manos están presentes
        if (thereHand(results)) {
            if (!handsPresent) {
                // Si las manos aparecen, inicializamos la secuencia
                handsPresent = true;
                kpSequence = [];
                countFrame = 0;
                console.log("Manos detectadas, iniciando captura.");
            }

            const newKeypoints = extractKeypoints(results);
            kpSequence.push(newKeypoints);
            countFrame += 1;

            // Limitar el tamaño del buffer
            if (kpSequence.length > MAX_LENGTH_FRAMES) {
                kpSequence = kpSequence.slice(-MAX_LENGTH_FRAMES);
            }
        } else if (handsPresent && !cooldown) {
            // Si las manos ya no están presentes y no estamos en cooldown
            handsPresent = false;

            if (kpSequence.length >= MIN_LENGTH_FRAMES) {
                console.log("Manos fuera, evaluando secuencia...");
                const lastKeypoints = kpSequence.slice(-MAX_LENGTH_FRAMES);
                const inputTensor = tf.tensor([lastKeypoints]);

                try {
                    const prediction = model.predict(inputTensor);
                    const res = prediction.dataSync();
                    console.log("Resultados de la predicción:", res);

                    if (Math.max(...res) > threshold) {
                        const maxIndex = res.indexOf(Math.max(...res));
                        const detectedWord = actions[maxIndex];
                        console.log("Palabra detectada:", detectedWord);

                        textToSpeech(detectedWord);
                        updateDetectedWord(detectedWord);

                        // Verificar si la palabra es la correcta
                        if (detectedWord === actions[currentWordIndex]) {
                            correctWords += 1;
                            correctCountElement.textContent = correctWords;

                            if (correctWords < actions.length) {
                                currentWordIndex += 1;
                                updateDetectedWord(actions[currentWordIndex]);
                            } else {
                                alert("¡Lección completada!");
                            }
                        }
                    } else {
                        console.log(
                            "Confianza insuficiente para detectar palabra."
                        );
                    }
                } catch (error) {
                    console.error("Error al realizar la predicción:", error);
                }
            } else {
                console.log("Secuencia demasiado corta, descartada.");
            }

            // Reiniciar buffer y contador
            kpSequence = [];
            countFrame = 0;

            // Activar cooldown para evitar predicciones consecutivas
            cooldown = true;
            setTimeout(() => {
                cooldown = false;
                console.log(
                    "Cooldown finalizado, listo para nueva predicción."
                );
            }, 1000); // 1 segundo de espera
        }

        // Dibujar landmarks
        drawConnectors(canvasCtx, results.poseLandmarks, POSE_CONNECTIONS, {
            color: "#00FF00",
            lineWidth: 3,
        });
        drawLandmarks(canvasCtx, results.poseLandmarks, {
            color: "#FF0000",
            lineWidth: 1,
        });

        drawConnectors(canvasCtx, results.faceLandmarks, FACEMESH_TESSELATION, {
            color: "#C0C0C070",
            lineWidth: 1,
        });

        drawConnectors(canvasCtx, results.leftHandLandmarks, HAND_CONNECTIONS, {
            color: "#CC0000",
            lineWidth: 4,
        });
        drawLandmarks(canvasCtx, results.leftHandLandmarks, {
            color: "#00FF00",
            lineWidth: 1,
        });

        drawConnectors(
            canvasCtx,
            results.rightHandLandmarks,
            HAND_CONNECTIONS,
            { color: "#00CC00", lineWidth: 5 }
        );
        drawLandmarks(canvasCtx, results.rightHandLandmarks, {
            color: "#FF0000",
            lineWidth: 2,
        });

        drawGuidelines(canvasCtx, canvasElement.width, canvasElement.height);

        canvasCtx.restore();
    }

    const holistic = new Holistic({
        locateFile: (file) =>
            `https://cdn.jsdelivr.net/npm/@mediapipe/holistic/${file}`,
    });

    holistic.setOptions({
        modelComplexity: 1,
        smoothLandmarks: true,
        enableSegmentation: false,
        smoothSegmentation: true,
        refineFaceLandmarks: false,
        minDetectionConfidence: 0.6,
        minTrackingConfidence: 0.6,
    });

    holistic.onResults(onResults);

    function startCamera() {
        const videoElement = document.createElement("video");
        videoElement.width = 640;
        videoElement.height = 360;
        videoElement.autoplay = true;
        videoElement.muted = true;
        videoElement.playsInline = true;

        camera = new Camera(videoElement, {
            onFrame: async () => {
                await holistic.send({ image: videoElement });
            },
            width: 640,
            height: 360,
        });
        camera.start();

        canvasElement.width = 640;
        canvasElement.height = 360;
    }

    toggleCameraButton.addEventListener("click", () => {
        if (camera) {
            camera.stop();
            camera = null;
            toggleCameraButton.textContent = "Encender Cámara";
        } else {
            startCamera();
            toggleCameraButton.textContent = "Apagar Cámara";
        }
    });

    startCamera();
});
