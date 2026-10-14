/**
 * Lab 6 - Real-Time Academic Dashboard (AJAX Polling)
 *
 * Every 30 seconds this script:
 *   1. GET /api/dashboard/stats      -> updates the statistic cards
 *   2. GET /api/students/at-risk     -> updates "Students Needing Attention"
 *   3. GET /api/activity/recent      -> updates "Recent Grades & Attendance"
 * then updates the "Last Updated" time.
 *
 * Module pattern: everything is private inside AcademicDashboard,
 * only init() and refresh() are exposed.
 */
const AcademicDashboard = (function () {
    'use strict';

    let config = {};
    let failures = 0;

    // Map: element id -> key in the stats JSON
    const STAT_FIELDS = {
        statTotalStudents: 'total_students',
        statAverageGpa: 'average_gpa',
        statAtRisk: 'at_risk_count',
        statAverageAttendance: 'average_attendance',
        statPresentToday: 'present_today',
        statAbsentToday: 'absent_today',
        statGradesToday: 'grades_today',
        statNeedsAttention: 'needs_attention_count',
    };

    // ---------- helpers ----------

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function formatNumber(value, element) {
        const decimals = parseInt(element.dataset.decimals || '0', 10);
        const suffix = element.dataset.suffix || '';

        return Number(value).toFixed(decimals) + suffix;
    }

    function getJson(url) {
        return fetch(url, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        }).then(function (response) {
            if (response.status === 401 || response.status === 419) {
                // Session expired: go back to login
                window.location.reload();
                throw new Error('Session expired');
            }

            if (!response.ok) {
                throw new Error('HTTP ' + response.status);
            }

            return response.json();
        });
    }

    /**
     * Counts from the old value to the new value, then flashes the number.
     */
    function animateNumber(element, newValue) {
        const oldValue = parseFloat(element.dataset.value || '0');

        element.dataset.value = newValue;

        if (oldValue === newValue) {
            return;
        }

        const duration = 800;
        const start = performance.now();

        function step(now) {
            const progress = Math.min((now - start) / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3);
            const current = oldValue + (newValue - oldValue) * eased;

            element.textContent = formatNumber(progress === 1 ? newValue : current, element);

            if (progress < 1) {
                requestAnimationFrame(step);
            }
        }

        requestAnimationFrame(step);

        element.classList.remove('stat-updated');
        void element.offsetWidth; // restart the CSS animation
        element.classList.add('stat-updated');
    }

    // ---------- renderers ----------

    function renderStats(json) {
        const stats = json.data || {};

        Object.keys(STAT_FIELDS).forEach(function (id) {
            const element = document.getElementById(id);
            const key = STAT_FIELDS[id];

            if (element && stats[key] !== undefined) {
                animateNumber(element, Number(stats[key]));
            }
        });
    }

    function renderAtRisk(json) {
        const list = document.getElementById('atRiskList');
        if (!list) return;

        const students = (json.data || []).slice(0, 5);

        if (students.length === 0) {
            list.innerHTML = '<li class="list-group-item text-center text-muted py-4">All students are in good standing.</li>';
            return;
        }

        list.innerHTML = students.map(function (s) {
            const textDark = s.standing_color === 'warning' ? ' text-dark' : '';

            return '<li class="list-group-item d-flex justify-content-between align-items-center">' +
                '<div>' +
                    '<a href="' + escapeHtml(s.url) + '" class="text-decoration-none fw-semibold">' + escapeHtml(s.name) + '</a>' +
                    '<small class="text-muted d-block">' + escapeHtml(s.alert_reasons.join('; ')) + '</small>' +
                '</div>' +
                '<span class="badge bg-' + escapeHtml(s.standing_color) + textDark + '">' +
                    Number(s.gpa).toFixed(2) + ' · ' + escapeHtml(s.attendance_rate) + '%' +
                '</span>' +
            '</li>';
        }).join('');
    }

    function renderRecent(json) {
        const body = document.getElementById('recentActivityBody');
        if (!body) return;

        const rows = json.data || [];

        if (rows.length === 0) {
            body.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-3">No activity yet.</td></tr>';
            return;
        }

        body.innerHTML = rows.map(function (a) {
            const textDark = a.color === 'warning' ? ' text-dark' : '';

            return '<tr>' +
                '<td><small>' + escapeHtml(a.created_at_human) + '</small></td>' +
                '<td><a href="' + escapeHtml(a.url) + '" class="text-decoration-none">' + escapeHtml(a.student.name) + '</a></td>' +
                '<td><span class="badge status-badge bg-' + escapeHtml(a.color) + textDark + '">' + escapeHtml(a.label) + '</span></td>' +
                '<td><small>' + escapeHtml(a.detail) + '</small></td>' +
                '<td><small>' + escapeHtml(a.user || '—') + '</small></td>' +
            '</tr>';
        }).join('');
    }

    function setStatus(ok) {
        const dot = document.getElementById('liveDot');
        const error = document.getElementById('liveError');

        if (dot) dot.classList.toggle('is-error', !ok);
        if (error) error.classList.toggle('d-none', ok);
    }

    function updateTimestamp() {
        const element = document.getElementById('lastUpdated');

        if (element) {
            element.textContent = new Date().toLocaleTimeString('en-PH');
        }
    }

    // ---------- polling ----------

    function refresh() {
        return Promise.all([
            getJson(config.statsUrl).then(renderStats),
            getJson(config.atRiskUrl).then(renderAtRisk),
            getJson(config.recentUrl).then(renderRecent),
        ])
            .then(function () {
                failures = 0;
                setStatus(true);
                updateTimestamp();
            })
            .catch(function (error) {
                // Keep the old values on screen and try again next cycle
                failures++;
                setStatus(false);
                console.warn('[AcademicDashboard] refresh failed (' + failures + '):', error.message);
            });
    }

    function init() {
        const root = document.getElementById('academicLive');
        if (!root) return;

        config = {
            statsUrl: root.dataset.statsUrl,
            atRiskUrl: root.dataset.atRiskUrl,
            recentUrl: root.dataset.recentUrl,
            interval: parseInt(root.dataset.interval || '30000', 10),
        };

        setInterval(refresh, config.interval);

        // Refresh right away when the user comes back to the tab
        document.addEventListener('visibilitychange', function () {
            if (!document.hidden) refresh();
        });
    }

    return { init: init, refresh: refresh };
})();

document.addEventListener('DOMContentLoaded', AcademicDashboard.init);
