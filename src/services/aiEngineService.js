import apiClient from '../api/apiClient';

const aiEngineService = {
  getStatus: () => apiClient.get('/ai-engine/status'),
  learn:     () => apiClient.post('/ai-engine/learn', {}),
  toggle:    (enabled) => apiClient.post('/ai-engine/toggle', { enabled }),
};

export default aiEngineService;
