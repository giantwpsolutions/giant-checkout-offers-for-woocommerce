import apiClient from '../api/apiClient';

const ENDPOINT = '/bumps';

const bumpService = {
  /**
   * Get all bumps.
   */
  getAll() {
    return apiClient.get(ENDPOINT);
  },

  /**
   * Get a single bump by ID.
   */
  getById(id) {
    return apiClient.get(`${ENDPOINT}/${id}`);
  },

  /**
   * Create a new bump.
   */
  create(data) {
    return apiClient.post(ENDPOINT, data);
  },

  /**
   * Update an existing bump.
   */
  update(id, data) {
    return apiClient.put(`${ENDPOINT}/${id}`, data);
  },

  /**
   * Delete a bump.
   */
  delete(id) {
    return apiClient.delete(`${ENDPOINT}/${id}`);
  },

  /**
   * Toggle bump status (active/inactive).
   */
  toggleStatus(id) {
    return apiClient.put(`${ENDPOINT}/${id}/toggle`);
  },
};

export default bumpService;
