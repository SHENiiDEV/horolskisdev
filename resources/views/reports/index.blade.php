<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Analytics & Reports') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="{
        stats: { income: 0, hours: 0, active_projects: 0 },
        init() {
            fetch('{{ route('reports.data') }}')
                .then(response => response.json())
                .then(data => {
                    // Update Stats
                    this.stats.income = data.income.data.reduce((a, b) => parseFloat(a) + parseFloat(b), 0).toFixed(2);
                    this.stats.hours = data.projects.data.reduce((a, b) => parseFloat(a) + parseFloat(b), 0).toFixed(1);
                    this.stats.active_projects = data.projects.labels.length;

                    // Initialize Charts
                    this.initIncomeChart(data.income);
                    this.initHoursChart(data.projects);
                    this.initDaysChart(data.days);
                });
        },
        initIncomeChart(data) {
            new Chart(document.getElementById('incomeChart'), {
                type: 'bar',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'Income (€)',
                        data: data.data,
                        backgroundColor: (ctx) => {
                            const gradient = ctx.chart.ctx.createLinearGradient(0, 0, 0, 400);
                            gradient.addColorStop(0, 'rgba(79, 70, 229, 0.8)');
                            gradient.addColorStop(1, 'rgba(79, 70, 229, 0.2)');
                            return gradient;
                        },
                        borderColor: 'rgba(79, 70, 229, 1)',
                        borderWidth: 1,
                        borderRadius: 5,
                        barPercentage: 0.6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { 
                            beginAtZero: true, 
                            grid: { color: 'rgba(0, 0, 0, 0.05)' } 
                        },
                        x: { 
                            grid: { display: false } 
                        }
                    }
                }
            });
        },
        initHoursChart(data) {
            new Chart(document.getElementById('hoursChart'), {
                type: 'doughnut',
                data: {
                    labels: data.labels,
                    datasets: [{
                        data: data.data,
                        backgroundColor: [
                            '#4F46E5', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899'
                        ],
                        borderWidth: 0,
                        hoverOffset: 10
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: {
                        legend: { position: 'right', labels: { usePointStyle: true, boxWidth: 8 } }
                    }
                }
            });
        },
        initDaysChart(data) {
            new Chart(document.getElementById('daysChart'), {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'Activity Count',
                        data: data.data,
                        fill: true,
                        backgroundColor: (ctx) => {
                            const gradient = ctx.chart.ctx.createLinearGradient(0, 0, 0, 300);
                            gradient.addColorStop(0, 'rgba(16, 185, 129, 0.4)');
                            gradient.addColorStop(1, 'rgba(16, 185, 129, 0.0)');
                            return gradient;
                        },
                        borderColor: '#10B981',
                        borderWidth: 2,
                        tension: 0.4,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: '#10B981',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { 
                            beginAtZero: true, 
                            ticks: { stepSize: 1 },
                            grid: { color: 'rgba(0, 0, 0, 0.05)' }
                        },
                        x: { grid: { display: false } }
                    }
                }
            });
        }
    }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Total Income -->
                <div
                    class="bg-white overflow-hidden shadow-sm sm:rounded-xl p-6 border-l-4 border-indigo-500 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Total Revenue (12m)</p>
                        <h3 class="text-2xl font-bold text-gray-900 mt-1">€<span x-text="stats.income"></span></h3>
                    </div>
                    <div class="p-3 bg-indigo-50 rounded-lg text-indigo-600">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>

                <!-- Total Hours -->
                <div
                    class="bg-white overflow-hidden shadow-sm sm:rounded-xl p-6 border-l-4 border-green-500 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Hours Tracked</p>
                        <h3 class="text-2xl font-bold text-gray-900 mt-1"><span x-text="stats.hours"></span> hrs</h3>
                    </div>
                    <div class="p-3 bg-green-50 rounded-lg text-green-600">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>

                <!-- Active Projects -->
                <div
                    class="bg-white overflow-hidden shadow-sm sm:rounded-xl p-6 border-l-4 border-orange-500 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Active Projects</p>
                        <h3 class="text-2xl font-bold text-gray-900 mt-1" x-text="stats.active_projects"></h3>
                    </div>
                    <div class="p-3 bg-orange-50 rounded-lg text-orange-600">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Charts Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">

                <!-- Income Chart -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-gray-900">Revenue Growth</h3>
                        <span class="text-xs font-semibold bg-indigo-100 text-indigo-700 px-2 py-1 rounded">Last 12
                            Months</span>
                    </div>
                    <div class="relative h-72 w-full">
                        <canvas id="incomeChart"></canvas>
                    </div>
                </div>

                <!-- Hours by Project -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Time Distribution</h3>
                    <div class="relative h-72 w-full flex justify-center">
                        <canvas id="hoursChart"></canvas>
                    </div>
                </div>

                <!-- Productivity by Day -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl p-6 md:col-span-2">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Weekly Productivity Trend</h3>
                    <div class="relative h-72 w-full">
                        <canvas id="daysChart"></canvas>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</x-app-layout>