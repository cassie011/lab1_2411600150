/**
 * app.js
 * ------------------------------------------------------------------
 * DOM handlers for the GSCSDA Student Portal Dashboard (Lab 4, Part
 * 5). Wires dataManager (state) and dashboardCharts (visualization) to the page: filter controls, live
 * search, academic warning alerts, CSV export, and a simulated
 * real-time feed.
 * ------------------------------------------------------------------
 */

document.addEventListener('DOMContentLoaded', () => {
    // Guard: only run on pages that actually have the roster section.
    const tableBody = document.getElementById('studentTableBody');
    if (!tableBody || typeof dataManager === 'undefined') return;

    const els = {
        programFilter: document.getElementById('programFilter'),
        standingFilter: document.getElementById('standingFilter'),
        gpaMin: document.getElementById('gpaMinInput'),
        gpaMax: document.getElementById('gpaMaxInput'),
        resetBtn: document.getElementById('resetFiltersBtn'),
        searchInput: document.getElementById('searchInput'),
        exportBtn: document.getElementById('exportCsvBtn'),
        tableBody,
        resultsCount: document.getElementById('resultsCount'),
        alertBox: document.getElementById('atRiskAlert'),
        alertText: document.getElementById('atRiskAlertText'),
        toastContainer: document.getElementById('toastContainer')
    };

    let currentQuery = '';

    // ---- Boot -----------------------------------------------------
    renderLoadingRow();

    dataManager.initializeData().then(() => {
        populateProgramDropdown();
        refreshView();
        attachListeners();
        startRealtimeSimulation();
    });

    // ---- Setup ------------------------------------------------------

    function populateProgramDropdown() {
        dataManager.getPrograms().forEach(program => {
            const opt = document.createElement('option');
            opt.value = program;
            opt.textContent = program;
            els.programFilter.appendChild(opt);
        });
    }

    function attachListeners() {
        els.programFilter.addEventListener('change', (e) => {
            dataManager.filterByProgram(e.target.value);
            refreshView();
        });

        els.standingFilter.addEventListener('change', (e) => {
            dataManager.filterByAcademicStanding(e.target.value);
            refreshView();
        });

        let gpaDebounce;
        function handleGpaChange() {
            clearTimeout(gpaDebounce);
            gpaDebounce = setTimeout(() => {
                dataManager.filterByGpaRange(els.gpaMin.value, els.gpaMax.value);
                refreshView();
            }, 250);
        }
        els.gpaMin.addEventListener('input', handleGpaChange);
        els.gpaMax.addEventListener('input', handleGpaChange);

        els.resetBtn.addEventListener('click', () => {
            dataManager.resetFilters();
            currentQuery = '';
            els.programFilter.value = 'all';
            els.standingFilter.value = 'all';
            els.gpaMin.value = '';
            els.gpaMax.value = '';
            els.searchInput.value = '';
            refreshView();
        });

        let searchDebounce;
        els.searchInput.addEventListener('input', (e) => {
            currentQuery = e.target.value;
            clearTimeout(searchDebounce);
            searchDebounce = setTimeout(() => {
                dataManager.updateSearchResults(currentQuery);
                refreshView();
            }, 150);
        });

        els.exportBtn.addEventListener('click', () => {
            const filtered = dataManager.applyFilters();
            if (!filtered.length) {
                showToast('Nothing to export', 'No students match the current filters.', 'warning');
                return;
            }
            const csv = dataManager.exportToCSV(filtered);
            const stamp = new Date().toISOString().slice(0, 10);
            dataManager.downloadCSV(csv, `student_roster_${stamp}.csv`);
            showToast('Export complete', `${filtered.length} student record(s) exported to CSV.`, 'success');
        });
    }

    // ---- Rendering --------------------------------------------------

    function renderLoadingRow() {
        els.tableBody.innerHTML = `
            <tr><td colspan="8" class="text-center text-muted py-4">Loading student roster...</td></tr>
        `;
    }

    function standingBadgeClass(standing) {
        if (standing === 'Good Standing') return 'bg-success';
        if (standing === 'At Risk') return 'bg-warning text-dark';
        return 'bg-danger';
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = String(str);
        return div.innerHTML;
    }

    function escapeRegExp(str) {
        return str.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }

    /** Wraps the matching substring of `text` in <mark> for live search highlighting. */
    function highlight(text, query) {
        const safe = escapeHtml(text);
        if (!query) return safe;
        const re = new RegExp(`(${escapeRegExp(query)})`, 'ig');
        return safe.replace(re, '<mark>$1</mark>');
    }

    /**
     * Rebuilds the student table body from scratch using DOM methods:
     * innerHTML reset to clear, then createElement + appendChild per
     * row, so individual rows can carry data attributes and per-row
     * classes for the academic-standing highlight and the real-time
     * "flash" animation.
     */
    function renderTable(list, query) {
        els.tableBody.innerHTML = '';

        if (!list.length) {
            const emptyRow = document.createElement('tr');
            const cell = document.createElement('td');
            cell.colSpan = 8;
            cell.className = 'text-center text-muted py-4';
            cell.textContent = 'No students match the current filters.';
            emptyRow.appendChild(cell);
            els.tableBody.appendChild(emptyRow);
        } else {
            list.forEach(s => {
                const row = document.createElement('tr');
                if (s.academicStanding === 'At Risk') row.classList.add('row-at-risk');
                if (s.academicStanding === 'Probation') row.classList.add('row-probation');
                row.dataset.studentId = s.studentId;

                row.innerHTML = `
                    <td>${highlight(s.studentId, query)}</td>
                    <td>${highlight(s.name, query)}</td>
                    <td>${escapeHtml(s.program)}</td>
                    <td>${s.yearLevel}</td>
                    <td>${s.units}</td>
                    <td>${s.gpa.toFixed(2)}</td>
                    <td>${s.attendanceRate}%</td>
                    <td><span class="badge status-badge ${standingBadgeClass(s.academicStanding)}">${s.academicStanding}</span></td>
                `;
                els.tableBody.appendChild(row);
            });
        }

        els.resultsCount.textContent = `Showing ${list.length} of ${dataManager.getStudents().length} students`;
    }

    /** Academic alert banner always reflects the whole roster, not just the current filter. */
    function updateAcademicAlert() {
        const atRisk = dataManager.getAtRiskStudents();
        if (atRisk.length === 0) {
            els.alertBox.classList.add('d-none');
            return;
        }
        const names = atRisk.slice(0, 3).map(s => s.name).join(', ');
        const extra = atRisk.length > 3 ? ` and ${atRisk.length - 3} more` : '';
        els.alertText.textContent = `${atRisk.length} student(s) are At Risk or on Probation: ${names}${extra}.`;
        els.alertBox.classList.remove('d-none');
    }

    /** Re-pulls the filtered list from dataManager and repaints table + alert + charts. */
    function refreshView() {
        const filtered = dataManager.applyFilters();
        renderTable(filtered, currentQuery);
        updateAcademicAlert();
        if (typeof dashboardCharts !== 'undefined') {
            dashboardCharts.renderAll(filtered);
        }
    }

    // ---- Notifications ------------------------------------------------

    function showToast(title, message, variant = 'primary') {
        if (!els.toastContainer || typeof bootstrap === 'undefined') return;

        const toastEl = document.createElement('div');
        toastEl.className = `toast align-items-center text-bg-${variant} border-0`;
        toastEl.setAttribute('role', 'alert');
        toastEl.setAttribute('aria-live', 'assertive');
        toastEl.setAttribute('aria-atomic', 'true');
        toastEl.innerHTML = `
            <div class="d-flex">
                <div class="toast-body"><strong>${escapeHtml(title)}:</strong> ${escapeHtml(message)}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        `;
        els.toastContainer.appendChild(toastEl);
        const toast = new bootstrap.Toast(toastEl, { delay: 4000 });
        toast.show();
        toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
    }

    // ---- Part 5 Step 5: simulated real-time updates ---------------------

    function startRealtimeSimulation() {
        setInterval(() => {
            const updated = dataManager.simulateUpdate();
            if (!updated) return;

            refreshView();

            const row = els.tableBody.querySelector(`tr[data-student-id="${CSS.escape(updated.studentId)}"]`);
            if (row) {
                row.classList.add('row-flash');
                setTimeout(() => row.classList.remove('row-flash'), 1200);
            }

            showToast(
                'Live update',
                `${updated.name}'s GPA changed to ${updated.gpa.toFixed(2)} (${updated.academicStanding}).`,
                'info'
            );
        }, 2000); // every 2 seconds
    }
});
