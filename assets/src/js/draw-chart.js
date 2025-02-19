var zwssgrChart;
var zwssgrData;
var zwssgrOptions;

google.charts.load('current', { packages: ['corechart'] });
google.charts.setOnLoadCallback(() => zwssgr_draw_chart(zwssgr_admin.zwssgr_dynamic_chart_data));

export function zwssgr_draw_chart(zwssgrChartData) {
    "use strict";

    if (!Array.isArray(zwssgrChartData)) {
        document.getElementById('zwssgr_chart_wrapper').innerHTML =
            '<div class="zwssgr-dashboard-text"> No enough data available. </div>';
        return;
    }

    const zwssgr_all_zero = zwssgrChartData.every(
        (row) => Array.isArray(row) && row[1] === 0
    );

    if (zwssgr_all_zero) {
        document.getElementById('zwssgr_chart_wrapper').innerHTML =
            '<div class="zwssgr-dashboard-text"> No enough data available. </div>';
        return;
    }

    zwssgrChartData.unshift(['Rating', 'Number of Reviews']);

    const zwssgrData = google.visualization.arrayToDataTable(zwssgrChartData);

    const zwssgrOptions = {
        pieHole: 0.4,
        width: 276,
        height: 276,
        pieSliceText: 'percentage',
        pieSliceTextStyle: {
            color: '#000000',
            fontSize: 16,
        },
        legend: 'none',
        chartArea: {
            width: '90%',
            height: '90%',
        },
        colors: ['#F08C3C', '#3CAAB4', '#A9C6CC', '#285064', '#F44336'],
        backgroundColor: 'transparent',
    };

    try {
        const zwssgrChart = new google.visualization.PieChart(
            document.getElementById('zwssgr_chart_wrapper')
        );
        zwssgrChart.draw(zwssgrData, zwssgrOptions);
    } catch (error) {
        console.error("Error drawing the chart:", error);
        document.getElementById('zwssgr_chart_wrapper').innerHTML =
            '<div class="zwssgr-dashboard-text">Failed to render chart</div>';
    }

}

let zwssgrResizeTimeout;
window.addEventListener('resize', () => {

    clearTimeout(zwssgrResizeTimeout);
    zwssgrResizeTimeout = setTimeout(() => {
        if (zwssgrChart && zwssgrData && zwssgrOptions) {
            zwssgrChart.draw(zwssgrData, zwssgrOptions);
        } else {
            console.warn("Chart or data is not initialized. Skipping redraw.");
        }
    }, 200);

});