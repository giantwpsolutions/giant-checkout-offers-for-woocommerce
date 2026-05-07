import { ref, computed } from 'vue';
import { getDefaultStyles } from '../config/templates';
import settingsService from '../services/settingsService';

// Singleton refs — shared across all components that use this composable
const activeTemplate = ref('standard');
const templateStyles = ref({});
const loaded = ref(false);

export function useTemplateSettings() {
  const loadSettings = async () => {
    try {
      const res = await settingsService.get();
      if (res.success) {
        if (res.data.selectedTemplate) {
          activeTemplate.value = res.data.selectedTemplate;
        }
        if (res.data.templateStyles && typeof res.data.templateStyles === 'object') {
          templateStyles.value = res.data.templateStyles;
        }
        loaded.value = true;
      }
    } catch {
      // Use defaults
    }
  };

  const currentTemplateStyles = computed(() => {
    const defaults = getDefaultStyles(activeTemplate.value);
    const saved = templateStyles.value[activeTemplate.value];
    return saved ? { ...defaults, ...saved } : { ...defaults };
  });

  const getStylesForTemplate = (templateId) => {
    const defaults = getDefaultStyles(templateId);
    const saved = templateStyles.value[templateId];
    return saved ? { ...defaults, ...saved } : { ...defaults };
  };

  return {
    activeTemplate,
    templateStyles,
    loaded,
    loadSettings,
    currentTemplateStyles,
    getStylesForTemplate,
  };
}
