import apiClient from '../api/apiClient';

const ENDPOINT = '/settings';

const settingsService = {
  get() {
    return apiClient.get(ENDPOINT);
  },

  update(data) {
    return apiClient.post(ENDPOINT, data);
  },
};

export default settingsService;
