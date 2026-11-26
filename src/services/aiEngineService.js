import apiClient from '../api/apiClient';

const aiEngineService = {
  getStatus:  () => apiClient.get('/ai-engine/status'),
  learn:      () => apiClient.post('/ai-engine/learn', {}),
  toggle:     (enabled) => apiClient.post('/ai-engine/toggle', { enabled }),
  verifyKey:  (provider, key) => apiClient.post('/ai-engine/verify-key', { provider, key }),
};

export default aiEngineService;
