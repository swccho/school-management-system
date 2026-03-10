import api from './api.js';

export async function getLeaveRequests() {
  const { data } = await api.get('/leave-requests');
  return data;
}

export async function getLeaveRequest(id) {
  const { data } = await api.get(`/leave-requests/${id}`);
  return data;
}

export async function createLeaveRequest(payload) {
  const body = payload instanceof FormData ? payload : (() => {
    const fd = new FormData();
    Object.keys(payload).forEach((k) => { if (payload[k] != null && k !== 'attachment') fd.append(k, payload[k]); });
    if (payload.attachment instanceof File) fd.append('attachment', payload.attachment);
    return fd;
  })();
  const { data } = await api.post('/leave-requests', body);
  return data;
}

export async function updateLeaveRequest(id, payload) {
  const hasFile = payload?.attachment instanceof File;
  if (hasFile) {
    const fd = new FormData();
    fd.append('leave_type', payload.leave_type);
    fd.append('start_date', payload.start_date);
    fd.append('end_date', payload.end_date);
    fd.append('reason', payload.reason);
    fd.append('attachment', payload.attachment);
    const { data } = await api.post(`/leave-requests/${id}`, fd);
    return data;
  }
  const { data } = await api.put(`/leave-requests/${id}`, payload);
  return data;
}

export async function deleteLeaveRequest(id) {
  const { data } = await api.delete(`/leave-requests/${id}`);
  return data;
}
