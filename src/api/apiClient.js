/**
 * API client for Smart Order Bump.
 * Uses native fetch with WordPress nonce authentication.
 */

const getBaseUrl = () => {
  const url = gcowPluginData.restUrl;
  // Remove trailing slash to prevent double slashes
  return url.endsWith('/') ? url.slice(0, -1) : url;
};

const getHeaders = () => ({
  'Content-Type': 'application/json',
  'X-WP-Nonce': gcowPluginData.nonce,
});

const handleResponse = async (response) => {
  const data = await response.json();
  if (!response.ok) {
    throw data;
  }
  return data;
};

const apiClient = {
  get(path, options = {}) {
    return fetch(`${getBaseUrl()}${path}`, {
      method: 'GET',
      headers: getHeaders(),
      ...options,
    }).then(handleResponse);
  },

  post(path, data = {}, options = {}) {
    return fetch(`${getBaseUrl()}${path}`, {
      method: 'POST',
      headers: getHeaders(),
      body: JSON.stringify(data),
      ...options,
    }).then(handleResponse);
  },

  put(path, data = {}, options = {}) {
    return fetch(`${getBaseUrl()}${path}`, {
      method: 'PUT',
      headers: getHeaders(),
      body: JSON.stringify(data),
      ...options,
    }).then(handleResponse);
  },

  delete(path, options = {}) {
    return fetch(`${getBaseUrl()}${path}`, {
      method: 'DELETE',
      headers: getHeaders(),
      ...options,
    }).then(handleResponse);
  },
};

export default apiClient;
