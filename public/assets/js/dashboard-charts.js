// Dashboard Charts JavaScript (Super Admin)

function parseDataset(element, attribute) {
    try {
        return JSON.parse(element.getAttribute(attribute) || '[]');
    } catch (error) {
        console.warn('Invalid dataset for', attribute, error);
        return [];
    }
}

function initCreditUsageChart() {
    if (typeof ApexCharts === 'undefined') {
        return;
    }

    const chartElement = document.getElementById('creditUsageChart');
    if (!chartElement) {
        return;
    }

    const creditUsage = parseDataset(chartElement, 'data-credit-usage');
    if (!Array.isArray(creditUsage) || creditUsage.length === 0) {
        return;
    }

    const colors = parseDataset(chartElement, 'data-credit-colors');
    const series = creditUsage.map(item => Number(item.value) || 0);
    const labels = creditUsage.map(item => item.label || '');

    const chart = new ApexCharts(chartElement, {
        series,
        labels,
        chart: {
            type: 'donut',
            height: 340,
        },
        colors: colors.length ? colors : undefined,
        legend: {
            position: 'bottom',
            fontSize: '13px',
        },
        dataLabels: {
            style: {
                fontSize: '12px',
            },
            formatter: function (value, opts) {
                const total = series.reduce((sum, current) => sum + current, 0);
                const absolute = series[opts.seriesIndex];
                const percentage = total > 0 ? Math.round((absolute / total) * 100) : 0;
                return `${percentage}%`;
            },
        },
        plotOptions: {
            pie: {
                donut: {
                    size: '70%',
                    labels: {
                        show: true,
                        name: {
                            show: true,
                            fontSize: '14px',
                        },
                        value: {
                            show: true,
                            fontSize: '20px',
                            formatter: function (val) {
                                return Math.round(Number(val)).toLocaleString('fa-IR');
                            },
                        },
                        total: {
                            show: true,
                            label: 'مجموع',
                            formatter: function (w) {
                                const total = w.globals.seriesTotals.reduce((sum, current) => sum + current, 0);
                                return Math.round(total).toLocaleString('fa-IR');
                            },
                        },
                    },
                },
            },
        },
        tooltip: {
            y: {
                formatter: function (value) {
                    return `${Math.round(value).toLocaleString('fa-IR')} تومان`;
                },
            },
        },
        stroke: {
            width: 1,
            colors: ['#ffffff'],
        },
        responsive: [
            {
                breakpoint: 992,
                options: {
                    chart: {
                        height: 300,
                    },
                    legend: {
                        fontSize: '12px',
                    },
                },
            },
        ],
    });

    chart.render();
}

document.addEventListener('DOMContentLoaded', function () {
    initCreditUsageChart();
});