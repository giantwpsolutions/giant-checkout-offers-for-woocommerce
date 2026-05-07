import apiClient from '../api/apiClient';

const ENDPOINT = '/categories';

const categoryService = {
  getAll() {
    return apiClient.get(ENDPOINT);
  },
};

export default categoryService;
