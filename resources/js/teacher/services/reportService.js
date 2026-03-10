import api from './api.js';

function downloadBlob(blob, filename) {
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = filename || 'download.csv';
  a.click();
  URL.revokeObjectURL(url);
}

export async function downloadAttendanceSummary(params) {
  const { data } = await api.get('/reports/attendance-summary', {
    params: { ...params, format: 'csv' },
    responseType: 'blob',
  });
  downloadBlob(data, `attendance-summary-${params.date_from}-${params.date_to}.csv`);
}

export async function downloadMarksSheet(params) {
  const { data } = await api.get('/reports/marks-sheet', {
    params: { ...params, format: 'csv' },
    responseType: 'blob',
  });
  downloadBlob(data, 'marks-sheet.csv');
}

export async function downloadHomeworkSummary(params) {
  const { data } = await api.get('/reports/homework-summary', {
    params: { ...params, format: 'csv' },
    responseType: 'blob',
  });
  downloadBlob(data, 'homework-summary.csv');
}
