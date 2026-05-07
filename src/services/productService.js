import apiClient from '../api/apiClient';

const ENDPOINT = '/products';

const productService = {
  search(term, limit = 20) {
    return apiClient.get(`${ENDPOINT}/search?search=${encodeURIComponent(term)}&limit=${limit}`);
  },

  getById(id) {
    return apiClient.get(`${ENDPOINT}/${id}`);
  },

  getAll(params = {}) {
    const query = new URLSearchParams(params).toString();
    return apiClient.get(`${ENDPOINT}${query ? '?' + query : ''}`);
  },
};

export default productService;
