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
