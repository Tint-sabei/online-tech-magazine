/**
 * MULTIMEDIA ELEMENT (GRAPHIC/STATISTICS)
 * This integrates Google Charts to provide a visual representation of 
 * engagement data (clicks and comments) fetched dynamically from the database.
 */
 
google.charts.load('current', { 'packages': ['corechart'] });
google.charts.setOnLoadCallback(loadDashboard);

function loadDashboard() {
    // Fetch Engagement Data (Clicks + Comments)
    fetch('api_engagement.php')
        .then(res => res.json())
        .then(data => {
            drawEngagement(data);
        })
        .catch(err => {
            // Handle error or show a fallback if the API fails
            console.error("Chart data could not be loaded", err);
        });
}

function drawEngagement(chartData) {
    if (!chartData || chartData.length < 1) return;

    var data = google.visualization.arrayToDataTable(chartData);

    var options = {
        title: 'User Interaction: Clicks vs Comments',
        titleTextStyle: { color: '#512DA8', fontSize: 14, bold: true },
        seriesType: 'bars',
        series: { 1: { type: 'line', pointSize: 8, color: '#FFCCBC' } },
        colors: ['#D1C4E9'],
        legend: { position: 'bottom' },
        chartArea: { width: '80%', height: '70%' },
        backgroundColor: 'transparent',
        vAxis: { minValue: 0, format: '0' },
        animation: {
            duration: 1000,
            easing: 'out',
            startup: true
        }
    };

    var chartElement = document.getElementById('engagementChart');
    if (chartElement) {
        new google.visualization.ComboChart(chartElement).draw(data, options);
    }
}