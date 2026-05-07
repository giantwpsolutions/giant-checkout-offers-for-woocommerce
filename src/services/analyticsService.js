import apiClient from '../api/apiClient';

const ENDPOINT = '/analytics';

const analyticsService = {
  get(period = 7) {
    return apiClient.get(`${ENDPOINT}?period=${period}`);
  },
};

export default analyticsService;
