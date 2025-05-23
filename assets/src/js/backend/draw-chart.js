var zwssgrChart;
var zwssgrData;
var zwssgrOptions;

google.charts.load('current', { packages: ['corechart'] });
google.charts.setOnLoadCallback(() => zwssgr_draw_chart(zwssgr_admin.zwssgr_dynamic_chart_data));

export function zwssgr_draw_chart(zwssgrChartData) {
    "use strict";

    const chartWrapper = document.getElementById('zwssgr_chart_wrapper');
    const zwssgrChartLegendWrapper = document.getElementById('zwsr_chart_legend_wrapper');
    const zwssgrOuterWrapper = document.querySelector(".zwssgr-outer-wrapper");

    if (!Array.isArray(zwssgrChartData) && chartWrapper) {
        zwssgrChartLegendWrapper.remove();
        zwssgrOuterWrapper.style.justifyContent = "center";
        chartWrapper.innerHTML =
            '<div class="zwssgr-dashboard-text"> No enough data available. </div>';
        return;
    }

    const zwssgr_all_zero = zwssgrChartData.every(
        (row) => Array.isArray(row) && row[1] === 0
    );

    if (zwssgr_all_zero && chartWrapper) {
        zwssgrChartLegendWrapper.remove();
        zwssgrOuterWrapper.style.justifyContent = "center";
        chartWrapper.innerHTML =
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
        if (chartWrapper) {
            const zwssgrChart = new google.visualization.PieChart(
                chartWrapper
            );
            zwssgrChart.draw(zwssgrData, zwssgrOptions);
        }
    } catch (error) {
        if (chartWrapper) {
            chartWrapper.innerHTML = '<div class="zwssgr-dashboard-text">Failed to render chart</div>';
        }
    }

}

let zwssgrResizeTimeout;
window.addEventListener('resize', () => {

    clearTimeout(zwssgrResizeTimeout);
    zwssgrResizeTimeout = setTimeout(() => {
        if (zwssgrChart && zwssgrData && zwssgrOptions) {
            zwssgrChart.draw(zwssgrData, zwssgrOptions);
        } else {
        }
    }, 200);

});