import { ElMessage } from 'element-plus';

const DEFAULT_OFFSET = 150;

const message = {
  success(msg, options = {}) {
    return ElMessage.success({ message: msg, offset: DEFAULT_OFFSET, ...options });
  },
  error(msg, options = {}) {
    return ElMessage.error({ message: msg, offset: DEFAULT_OFFSET, ...options });
  },
  warning(msg, options = {}) {
    return ElMessage.warning({ message: msg, offset: DEFAULT_OFFSET, ...options });
  },
  info(msg, options = {}) {
    return ElMessage.info({ message: msg, offset: DEFAULT_OFFSET, ...options });
  },
};

export default message;
