const ctx = document.getElementById('playerChart').getContext('2d');
const playerChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: playerNames,
        datasets: [{
            label: 'Notes Moyennes des Joueurs',
            data: playerScores,
            borderColor: 'rgba(75, 192, 192, 1)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            borderWidth: 2,
            fill: true,
            pointBackgroundColor: 'rgba(75, 192, 192, 1)'
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                suggestedMax: 5,
                ticks: {
                    stepSize: 0.5
                }
            },
            x: {
                title: {
                    display: true,
                    text: 'Joueurs'
                },
                ticks: {
                    maxRotation: 45,
                    minRotation: 45
                }
            }
        },
        plugins: {
            legend: {
                display: true,
                position: 'top'
            },
            title: {
                display: true,
                text: 'Notes Moyennes des Joueurs'
            }
        }
    }
});


