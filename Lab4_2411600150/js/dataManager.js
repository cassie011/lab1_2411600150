/**
 * dataManager.js
 * ------------------------------------------------------------------
 * Central data management module for the GSCSDA Student Portal
 * Dashboard (Laboratory Exercise 4) — the academic-records section
 * of the portal, covering GPA, program, and standing per student.
 *
 * Data model per student record:
 *   studentId, name, program, yearLevel, units, gpa, attendanceRate
 * Computed on load:
 *   academicStanding ("Good Standing" / "At Risk" / "Probation")
 *   gpaThreshold      (the GPA that separates Good Standing from At Risk)
 *   qualityPoints     (units * gpa)
 *
 * Module pattern (IIFE) keeps all state private and exposes a single
 * global `dataManager` object.
 * ------------------------------------------------------------------
 */

const dataManager = (function () {
    'use strict';

    // ---------------------------------------------------------------
    // Private state
    // ---------------------------------------------------------------
    let students = [];

    let activeFilters = {
        program: 'all',
        academicStanding: 'all',
        gpaMin: null,
        gpaMax: null,
        searchQuery: ''
    };

    // Academic standing thresholds (GPA on a 4.0 scale, higher = better)
    const GPA_GOOD_STANDING = 3.0; // >= this -> Good Standing
    const GPA_AT_RISK = 2.0;       // >= this and < GOOD_STANDING -> At Risk
    // below GPA_AT_RISK -> Probation

    // ---------------------------------------------------------------
    // Sample fallback data, used only if api.php is unreachable
    // ---------------------------------------------------------------
    const SAMPLE_STUDENTS = [
    { "id": 1, "studentId": "2411600001", "name": "Diana Fe Diego", "program": "BS Computer Science", "yearLevel": 4, "units": 21, "gpa": 3.75, "attendanceRate": 96 },
    { "id": 2, "studentId": "2411600002", "name": "Renella Del Rosario", "program": "BS Information Technology", "yearLevel": 2, "units": 18, "gpa": 1.85, "attendanceRate": 78 },
    { "id": 3, "studentId": "2411600003", "name": "Amron Basher", "program": "BS Computer Science", "yearLevel": 4, "units": 24, "gpa": 3.20, "attendanceRate": 91 },
    { "id": 4, "studentId": "2411600004", "name": "Walter Sun", "program": "BS Accountancy", "yearLevel": 3, "units": 15, "gpa": 2.40, "attendanceRate": 85 },
    { "id": 5, "studentId": "2411600005", "name": "Wneljae Giangan", "program": "BS Nursing", "yearLevel": 3, "units": 22, "gpa": 1.60, "attendanceRate": 70 },
    { "id": 6, "studentId": "2411600006", "name": "Reahmeil Perocillo", "program": "BS Information Technology", "yearLevel": 4, "units": 20, "gpa": 3.90, "attendanceRate": 98 }


    ];

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------

    function deriveStanding(gpa) {
        if (gpa >= GPA_GOOD_STANDING) return 'Good Standing';
        if (gpa >= GPA_AT_RISK) return 'At Risk';
        return 'Probation';
    }

    // Attaches computed fields (academicStanding, gpaThreshold, qualityPoints)
    function withComputed(student) {
        return {
            ...student,
            academicStanding: deriveStanding(student.gpa),
            gpaThreshold: GPA_GOOD_STANDING,
            qualityPoints: +(student.units * student.gpa).toFixed(2)
        };
    }

    // ---------------------------------------------------------------
    // Part 7: backend endpoint. If api.php isn't deployed/running
    // (e.g. opened via file://, or XAMPP's Apache isn't started),
    // initializeData() falls back to SAMPLE_STUDENTS so the dashboard
    // still works standalone.
    // ---------------------------------------------------------------
    const API_ENDPOINT = 'api.php?action=list';
    const API_TIMEOUT_MS = 2000;

    function fetchFromApi() {
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), API_TIMEOUT_MS);

        return fetch(API_ENDPOINT, { signal: controller.signal })
            .then(res => {
                clearTimeout(timeoutId);
                if (!res.ok) throw new Error(`API responded with status ${res.status}`);
                return res.json();
            })
            .then(data => {
                if (!Array.isArray(data)) throw new Error('Unexpected API response shape');
                return data;
            });
    }

    /**
     * Sets up the initial dataset. Tries api.php first; falls back to
     * bundled sample data after a short simulated delay if the API is
     * unreachable. Always returns a Promise resolving to the roster.
     */
    function initializeData() {
        return fetchFromApi()
            .then(apiStudents => {
                students = apiStudents.map(withComputed);
                return getStudents();
            })
            .catch(() => {
                console.warn('dataManager: api.php unreachable — using bundled sample data.');
                return new Promise((resolve) => {
                    setTimeout(() => {
                        students = SAMPLE_STUDENTS.map(withComputed);
                        resolve(getStudents());
                    }, 300);
                });
            });
    }

    // ---------------------------------------------------------------
    // Getters
    // ---------------------------------------------------------------

    /** Returns a shallow copy of the full current student list. */
    function getStudents() {
        return [...students];
    }

    /** Returns a single student by numeric id or studentId string. */
    function getStudentById(id) {
        return students.find(s => s.id === id || s.studentId === id) || null;
    }

    /** Returns students enrolled in a given program. */
    function getStudentsByProgram(program) {
        return students.filter(s => s.program === program);
    }

    /** Returns students who are At Risk or on Probation. */
    function getAtRiskStudents() {
        return students.filter(s => s.academicStanding !== 'Good Standing');
    }

    /**
     * Returns aggregate academic statistics. Pass a pre-filtered list
     * (e.g. from applyFilters()) to scope stats to the current view;
     * omit it to summarize the entire roster.
     */
    function getAcademicStatistics(list) {
        const source = list || students;
        const total = source.length;
        const totalQualityPoints = source.reduce((sum, s) => sum + s.qualityPoints, 0);
        const goodStanding = source.filter(s => s.academicStanding === 'Good Standing').length;
        const atRisk = source.filter(s => s.academicStanding === 'At Risk').length;
        const probation = source.filter(s => s.academicStanding === 'Probation').length;
        const averageGpa = total
            ? +(source.reduce((sum, s) => sum + s.gpa, 0) / total).toFixed(2)
            : 0;

        return {
            totalStudents: total,
            totalQualityPoints: +totalQualityPoints.toFixed(2),
            goodStanding,
            atRisk,
            probation,
            averageGpa
        };
    }

    /**
     * Returns per-program totals (units, quality points, headcount).
     * Pass a pre-filtered list to scope the summary to the current view.
     */
    function getProgramSummary(list) {
        const source = list || students;
        const map = {};
        source.forEach(s => {
            if (!map[s.program]) {
                map[s.program] = {
                    program: s.program,
                    totalUnits: 0,
                    totalQualityPoints: 0,
                    count: 0
                };
            }
            map[s.program].totalUnits += s.units;
            map[s.program].totalQualityPoints += s.qualityPoints;
            map[s.program].count += 1;
        });
        return Object.values(map).map(p => ({ ...p, totalQualityPoints: +p.totalQualityPoints.toFixed(2) }));
    }

    /** Returns the distinct list of programs present in the data. */
    function getPrograms() {
        return [...new Set(students.map(s => s.program))];
    }

    // ---------------------------------------------------------------
    // Filtering & search
    // ---------------------------------------------------------------

    function filterByProgram(program) {
        activeFilters.program = program;
        return applyFilters();
    }

    function filterByAcademicStanding(standing) {
        activeFilters.academicStanding = standing;
        return applyFilters();
    }

    function filterByGpaRange(min, max) {
        activeFilters.gpaMin = (min === '' || min == null) ? null : Number(min);
        activeFilters.gpaMax = (max === '' || max == null) ? null : Number(max);
        return applyFilters();
    }

    /** Applies every active filter + the search query together. */
    function applyFilters() {
        return students.filter(s => {
            const matchProgram = activeFilters.program === 'all' || s.program === activeFilters.program;
            const matchStanding = activeFilters.academicStanding === 'all' || s.academicStanding === activeFilters.academicStanding;
            const matchMin = activeFilters.gpaMin == null || s.gpa >= activeFilters.gpaMin;
            const matchMax = activeFilters.gpaMax == null || s.gpa <= activeFilters.gpaMax;
            const q = activeFilters.searchQuery.trim().toLowerCase();
            const matchSearch = !q ||
                s.name.toLowerCase().includes(q) ||
                s.studentId.toLowerCase().includes(q);
            return matchProgram && matchStanding && matchMin && matchMax && matchSearch;
        });
    }

    function resetFilters() {
        activeFilters = { program: 'all', academicStanding: 'all', gpaMin: null, gpaMax: null, searchQuery: '' };
        return getStudents();
    }

    function searchStudents(query) {
        activeFilters.searchQuery = query || '';
        return applyFilters();
    }

    /** Convenience alias for the search input handler. */
    function updateSearchResults(query) {
        return searchStudents(query);
    }

    function getActiveFilters() {
        return { ...activeFilters };
    }

    // ---------------------------------------------------------------
    // CSV export
    // ---------------------------------------------------------------

    /** Converts a list of student records into a CSV string. */
    function exportToCSV(data) {
        const rows = (data && data.length) ? data : students;
        const headers = ['Student ID', 'Name', 'Program', 'Year Level', 'Units', 'GPA', 'Attendance %', 'Status'];

        const escape = (val) => `"${String(val).replace(/"/g, '""')}"`;

        const lines = rows.map(s => [
            s.studentId,
            escape(s.name),
            escape(s.program),
            s.yearLevel,
            s.units,
            s.gpa.toFixed(2),
            s.attendanceRate,
            s.academicStanding
        ].join(','));

        return [headers.join(','), ...lines].join('\r\n');
    }

    /** Triggers a browser download of the given CSV content. */
    function downloadCSV(csvContent, filename) {
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.setAttribute('href', url);
        link.setAttribute('download', filename || 'student_roster.csv');
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);
    }

    // ---------------------------------------------------------------
    // Real-time simulation hook (wired up by app.js in Part 5)
    // ---------------------------------------------------------------

    /** Nudges one random student's GPA to mimic a live data feed. */
    function simulateUpdate() {
        if (!students.length) return null;
        const idx = Math.floor(Math.random() * students.length);
        const delta = +(Math.random() * 0.4 - 0.2).toFixed(2);
        let newGpa = +(students[idx].gpa + delta).toFixed(2);
        newGpa = Math.min(4.0, Math.max(0.5, newGpa));
        students[idx] = withComputed({ ...students[idx], gpa: newGpa });
        return students[idx];
    }

    // ---------------------------------------------------------------
    // Public API
    // ---------------------------------------------------------------
    return {
        initializeData,
        getStudents,
        getStudentById,
        getStudentsByProgram,
        getAtRiskStudents,
        getAcademicStatistics,
        getProgramSummary,
        getPrograms,
        filterByProgram,
        filterByAcademicStanding,
        filterByGpaRange,
        applyFilters,
        resetFilters,
        searchStudents,
        updateSearchResults,
        getActiveFilters,
        exportToCSV,
        downloadCSV,
        simulateUpdate
    };
})();
