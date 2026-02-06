<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Analytics & Reports') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Charts Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">

                <!-- Income Chart -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Income (Last 12 Months)</h3>
                    <div class="relative h-64 w-full">
                        <canvas id="incomeChart"></canvas>
                    </div>
                </div>

                <!-- Hours by Project -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Hours by Project</h3>
                    <div class="relative h-64 w-full flex justify-center">
                        <canvas id="hoursChart"></canvas>
                    </div>
                </div>

                <!-- Productivity by Day -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 md:col-span-2">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Productivity (Entries by Day)</h3>
                    <div class="relative h-64 w-full">
                        <canvas id="daysChart"></canvas>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            fetch('{{ route('reports.data') }}')
                .then(response => response.json())
                .then(data => {
                    // 1. Income Chart
                    new Chart(document.getElementById('incomeChart'), {
                        type: 'bar',
                        data: {
                            labels: data.income.labels,
                            datasets: [{
                                label: 'Income (€)',
                                data: data.income.data,
                                backgroundColor: 'rgba(79, 70, 229, 0.2)',
                                borderColor: 'rgba(79, 70, 229, 1)',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: { beginAtZero: true }
                            }
                        }
                    });

                    // 2. Hours Chart
                    new Chart(document.getElementById('hoursChart'), {
                        type: 'doughnut',
                        data: {
                            labels: data.projects.labels,
                            datasets: [{
                                label: 'Hours',
                                data: data.projects.data,
                                backgroundColor: [
                                    'rgba(255, 99, 132, 0.6)',
                                    'rgba(54, 162, 235, 0.6)',
                                    'rgba(255, 206, 86, 0.6)',
                                    'rgba(75, 192, 192, 0.6)',
                                    'rgba(153, 102, 255, 0.6)',
                                    'rgba(255, 159, 64, 0.6)'
                                ],
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                        }
                    });

                    // 3. Days Chart
                    new Chart(document.getElementById('daysChart'), {
                        type: 'line',
                        data: {
                            labels: data.days.labels,
                            datasets: [{
                                label: 'Activity Count',
                                data: data.days.data,
                                fill: true,
                                backgroundColor: 'rgba(16, 185, 129, 0.2)',
                                borderColor: 'rgba(16, 185, 129, 1)',
                                tension: 0.4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: { beginAtZero: true, ticks: { stepSize: 1 } }
                            }
                        }
                    });
                });
        });
    </script>
</x-app-layout>