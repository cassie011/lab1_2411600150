/**
 * charts.js
 * ------------------------------------------------------------------
 * Chart.js configuration and rendering for the GSCSDA Student Portal
 * Dashboard (Laboratory Exercise 4, Part 4).
 *
 * Three charts, each reading from dataManager:
 *   1. programValueChart -> Enrolled Students by Program      (bar)
 *   2. standingChart      -> Academic Standing Distribution    (doughnut)
 *   3. topStudentsChart    -> Top 5 Students by GPA             (horizontal bar)
 *
 * Chart instances are kept in module state so they can be destroyed
 * and rebuilt whenever filters, search, or the real-time simulation
 * change the underlying data (a Chart.js instance must be destroyed
 * before its canvas is redrawn, or it leaks and stacks tooltips).
 * ------------------------------------------------------------------
 */

const dashboardCharts = (function () {
    'use strict';

    let programChart = null;
    let standingChartInstance = null;
    let topStudentsChartInstance = null;

    // Matches css/style.css theme variables (pink theme)
    const COLORS = {
        primary: '#FF8FAB',
        secondary: '#FFC2D1',
        accent: '#6B818C',
        success: '#FFC2D1',
        warning: '#FF8FAB',
        danger: '#FB6F92'
    };

    const baseOptions = {
        responsive: true,
        maintainAspectRatio: false,
        animation: { duration: 500 }
    };

    /**
     * Enrolled Students by Program chart.
     * Bar chart: programs on the x-axis, headcount on the y-axis.
     */
    function renderProgramChart(programSummary) {
        const canvas = document.getElementById('programValueChart');
        if (!canvas) return;

        const labels = programSummary.map(p => p.program);
        const counts = programSummary.map(p => p.count);

        if (programChart) programChart.destroy();
        programChart = new Chart(canvas, {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label: 'Enrolled Students',
                    data: counts,
                    backgroundColor: COLORS.primary,
                    hoverBackgroundColor: COLORS.secondary,
                    borderRadius: 6,
                    maxBarThickness: 48
                }]
            },
            options: {
                ...baseOptions,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => `${ctx.parsed.y} student(s)`
                        }
                    }
                },
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0 } },
                    x: { ticks: { autoSkip: false, maxRotation: 30, minRotation: 0 } }
                }
            }
        });
    }

    /**
     * Academic Standing Distribution chart.
     * doughnut chart with semantic colors (green/yellow/red).
     */
    function renderStandingChart(stats) {
        const canvas = document.getElementById('standingChart');
        if (!canvas) return;

        if (standingChartInstance) standingChartInstance.destroy();
        standingChartInstance = new Chart(canvas, {
            type: 'doughnut',
            data: {
                labels: ['Good Standing', 'At Risk', 'Probation'],
                datasets: [{
                    data: [stats.goodStanding, stats.atRisk, stats.probation],
                    backgroundColor: [COLORS.success, COLORS.warning, COLORS.danger],
                    hoverOffset: 8
                }]
            },
            options: {
                ...baseOptions,
                plugins: {
                    legend: { position: 'bottom' }
                },
                cutout: '60%'
            }
        });
    }

    /**
     * Top 5 Students by GPA.
     * Horizontal bar chart — good for longer name labels.
     */
    function renderTopStudentsChart(students) {
        const canvas = document.getElementById('topStudentsChart');
        if (!canvas) return;

        const top5 = [...students]
            .sort((a, b) => b.gpa - a.gpa)
            .slice(0, 5);

        if (topStudentsChartInstance) topStudentsChartInstance.destroy();
        topStudentsChartInstance = new Chart(canvas, {
            type: 'bar',
            data: {
                labels: top5.map(s => s.name),
                datasets: [{
                    label: 'GPA',
                    data: top5.map(s => s.gpa),
                    backgroundColor: COLORS.secondary,
                    hoverBackgroundColor: COLORS.primary,
                    borderRadius: 6
                }]
            },
            options: {
                ...baseOptions,
                indexAxis: 'y',
                plugins: { legend: { display: false } },
                scales: {
                    x: { beginAtZero: true, max: 4.0 }
                }
            }
        });
    }

    /**
     * Re-renders all three charts. Pass the currently filtered student
     * list (from dataManager.applyFilters()) so the charts update in
     * sync with the table and search; omit it to chart the full roster.
     */
    function renderAll(list) {
        const source = list || dataManager.getStudents();
        renderProgramChart(dataManager.getProgramSummary(source));
        renderStandingChart(dataManager.getAcademicStatistics(source));
        renderTopStudentsChart(source);
    }

    return {
        renderAll,
        renderProgramChart,
        renderStandingChart,
        renderTopStudentsChart
    };
})();
