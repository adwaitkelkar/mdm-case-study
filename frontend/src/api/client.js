const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || '/api';

async function request(path, options = {}) {
  const response = await fetch(`${API_BASE_URL}${path}`, {
    ...options,
    headers: {
      Accept: 'application/json',
      ...(options.body ? { 'Content-Type': 'application/json' } : {}),
      ...options.headers,
    },
  });

  if (!response.ok) {
    let message = `Request failed with status ${response.status}`;
    try {
      const data = await response.json();
      message = data.message || message;
    } catch {
      // No JSON body to read (e.g. a network-level failure) - keep default message.
    }
    throw new Error(message);
  }

  if (response.status === 204) {
    return null;
  }

  return response.json();
}

export function listAssets() {
  return request('/assets');
}

export function getAsset(id) {
  return request(`/assets/${id}`);
}

export function deleteAsset(id) {
  return request(`/assets/${id}`, { method: 'DELETE' });
}

export function listEmployees() {
  return request('/employees');
}

export function deleteEmployee(id) {
  return request(`/employees/${id}`, { method: 'DELETE' });
}

export function triggerSync() {
  return request('/sync', { method: 'POST' });
}
