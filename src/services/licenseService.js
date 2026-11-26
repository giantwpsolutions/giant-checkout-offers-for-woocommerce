import apiClient from '../api/apiClient';

const ENDPOINT = '/license';

const licenseService = {
  getStatus() {
    return apiClient.get(`${ENDPOINT}/status`);
  },

  activate(licenseKey) {
    return apiClient.post(`${ENDPOINT}/activate`, { license_key: licenseKey });
  },

  deactivate() {
    return apiClient.post(`${ENDPOINT}/deactivate`);
  },
};

export default licenseService;
