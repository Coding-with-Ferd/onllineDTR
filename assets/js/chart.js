document.addEventListener('DOMContentLoaded', function () {
    fetch('../backend/attendance_trends.php')
        .then(res => res.json())
        .then(data => {
            
            if(document.getElementById("totalEmployees")) {
                document.getElementById("totalEmployees").innerText = data.totalEmployees;
            }
            if(document.getElementById("presentToday")) {
                document.getElementById("presentToday").innerText = data.presentToday;
            }
            if(document.getElementById("absentToday")) {
                document.getElementById("absentToday").innerText = data.absentToday;
            }
            
            const canvas = document.getElementById('attendanceChart');
            if (!canvas) return;

            const ctx = canvas.getContext('2d');

            // --- GRADIANT DEFINITIONS ---
            const greenGrad = ctx.createLinearGradient(0, 0, 0, 400);
            greenGrad.addColorStop(0, 'rgba(26, 109, 24, 0.25)'); 
            greenGrad.addColorStop(1, 'rgba(26, 109, 24, 0)');

            const redGrad = ctx.createLinearGradient(0, 0, 0, 400);
            redGrad.addColorStop(0, 'rgba(229, 62, 62, 0.15)'); 
            redGrad.addColorStop(1, 'rgba(229, 62, 62, 0)');

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: [
                        {
                            label: 'Present',
                            data: data.present,
                            borderColor: '#1a6d18',
                            backgroundColor: greenGrad,
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
                            pointRadius: 2,
                            pointHoverRadius: 6,
                            pointBackgroundColor: '#fff',
                            pointBorderColor: '#1a6d18',
                            pointBorderWidth: 2
                        },
                        {
                            label: 'Absent',
                            data: data.absent,
                            borderColor: '#e53e3e',
                            backgroundColor: redGrad,
                            borderWidth: 2,
                            borderDash: [5, 5], 
                            fill: true,
                            tension: 0.4,
                            pointRadius: 0,
                            pointHoverRadius: 5
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false, 
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            align: 'end',
                            labels: {
                                boxWidth: 8,
                                usePointStyle: true,
                                padding: 20,
                                font: { size: 12, weight: '600' }
                            }
                        },
                        tooltip: {
                            backgroundColor: '#fff',
                            titleColor: '#1a202c',
                            bodyColor: '#4a5568',
                            borderColor: '#edf2f7',
                            borderWidth: 1,
                            padding: 12,
                            boxPadding: 6,
                            usePointStyle: true,
                            callbacks: {
                                label: function(context) {
                                    return ` ${context.dataset.label}: ${context.raw} Employees`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            suggestedMax: data.totalEmployees || 10,
                            grid: { color: '#f7fafc', drawBorder: false },
                            ticks: { stepSize: 1, color: '#a0aec0' }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { color: '#a0aec0' }
                        }
                    }
                }
            });
        });
});