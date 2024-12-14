@extends('layouts.app')

@section('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js/dist/chart.min.css">
@endsection

@section('content')
    <div class="container mt-5">
        <h1 class="text-center">Reporte de Progreso</h1>
        <div class="text-center mb-4">
            <form action="{{ route('reports.exportFull') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-secondary">
                    Generar Reporte Completo
                </button>
            </form>
        </div>
        <div class="row mt-4">
            <!-- Progreso General -->
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white text-center">
                        <strong>Progreso General</strong>
                    </div>
                    <div class="card-body">
                        <canvas id="generalProgressChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Progreso por Módulos -->
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white text-center">
                        <strong>Progreso por Módulos</strong>
                    </div>
                    <div class="card-body">
                        <canvas id="moduleProgressChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt-4">
            <!-- Tiempo dedicado por lección -->
            <div class="col-md-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-warning text-dark text-center">
                        <strong>Tiempo Dedicado por Lección</strong>
                    </div>
                    <div class="card-body">
                        <canvas id="lessonTimeChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const generalProgress = @json($generalProgress);
            const moduleProgress = @json($moduleProgress);
            const lessonTimeData = @json($lessonTimeData);

            console.log("Progreso General:", generalProgress);
            console.log("Progreso por Módulos:", moduleProgress);
            console.log("Tiempo por Lección:", lessonTimeData);

            // Progreso General
            const ctxGeneral = document.getElementById('generalProgressChart').getContext('2d');
            new Chart(ctxGeneral, {
                type: 'doughnut',
                data: {
                    labels: ['Completado', 'Por Hacer'],
                    datasets: [{
                        data: [generalProgress.completed, generalProgress.pending],
                        backgroundColor: ['#4CAF50', '#FF5722'],
                    }]
                },
                options: {
                    plugins: {
                        legend: {
                            position: 'bottom',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(tooltipItem) {
                                    const dataset = tooltipItem.dataset.data;
                                    const label = tooltipItem.label;
                                    const value = dataset[tooltipItem.dataIndex];

                                    if (label === 'Completado') {
                                        return [
                                            `${label}: ${value} lecciones completadas`,
                                            ...generalProgress.completedLessons.map(lesson =>
                                                `- ${lesson}`)
                                        ];
                                    } else if (label === 'Por Hacer') {
                                        return [
                                            `${label}: ${value} lecciones pendientes`,
                                            ...generalProgress.pendingLessons.map(lesson =>
                                                `- ${lesson}`)
                                        ];
                                    }
                                    return `${label}: ${value}`;
                                }
                            }
                        }
                    }
                }
            });

            // Progreso por Módulos
            const ctxModule = document.getElementById('moduleProgressChart').getContext('2d');
            new Chart(ctxModule, {
                type: 'bar',
                data: {
                    labels: moduleProgress.modules,
                    datasets: [{
                        label: 'Progreso (%)',
                        data: moduleProgress.percentages,
                        backgroundColor: '#2196F3',
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false,
                        },
                        tooltip: {
                            callbacks: {
                                label: (tooltipItem) => tooltipItem.raw + '%',
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            title: {
                                display: true,
                                text: '% Progreso'
                            }
                        }
                    }
                }
            });

            // Tiempo Dedicado por Lección
            const ctxLesson = document.getElementById('lessonTimeChart').getContext('2d');
            new Chart(ctxLesson, {
                type: 'line',
                data: {
                    labels: lessonTimeData.lessons,
                    datasets: [{
                        label: 'Tiempo Dedicado (min)',
                        data: lessonTimeData.times,
                        backgroundColor: 'rgba(255, 193, 7, 0.2)',
                        borderColor: '#FFC107',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.3,
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Tiempo (min)'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Lecciones'
                            }
                        }
                    }
                }
            });
        });
    </script>
@endsection
