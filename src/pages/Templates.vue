<script setup>
import { ref, onMounted } from 'vue';
import TemplateCard from '../components/TemplateCard.vue';
import TemplateStyleEditor from '../components/TemplateStyleEditor.vue';
import message from '../utils/message';
import settingsService from '../services/settingsService';
import { templateList, previewComponents, getDefaultStyles } from '../config/templates';
import { useTemplateSettings } from '../composables/useTemplateSettings';

const { __ } = wp?.i18n || { __: (t) => t };

const { activeTemplate, templateStyles, loadSettings, getStylesForTemplate } = useTemplateSettings();

const isPro = ref(false); // Will be fetched from API

// Currently editing template ID (null = editor closed)
const editingTemplateId = ref(null);

// Add runtime 'selected' state on top of shared config
const templates = ref(
  templateList.map(tpl => ({ ...tpl, selected: tpl.id === 'standard' }))
);

const applySelection = (id) => {
  templates.value.forEach(t => t.selected = t.id === id);
  activeTemplate.value = id;
};

const initSettings = async () => {
  await loadSettings();
  applySelection(activeTemplate.value);
};

const selectTemplate = async (template) => {
  applySelection(template.id);

  try {
    const res = await settingsService.update({ selectedTemplate: template.id });
    if (res.success) {
      message.success(
        /* translators: %s: template name */
        sprintf(__('Template "%s" selected!', 'giant-checkout-offers-for-woocommerce'), template.name)
      );
    }
  } catch {
    message.error(__('Failed to save template selection', 'giant-checkout-offers-for-woocommerce'));
  }
};

const previewTemplate = (template) => {
  message.info(
    /* translators: %s: template name */
    sprintf(__('Previewing "%s" template', 'giant-checkout-offers-for-woocommerce'), template.name)
  );
};

const openStyleEditor = (template) => {
  editingTemplateId.value = editingTemplateId.value === template.id ? null : template.id;
};

const closeStyleEditor = () => {
  editingTemplateId.value = null;
};

let saveTimeout = null;
const updateTemplateStyles = (templateId, styles) => {
  templateStyles.value = { ...templateStyles.value, [templateId]: styles };

  // Debounced save to API
  clearTimeout(saveTimeout);
  saveTimeout = setTimeout(async () => {
    try {
      const res = await settingsService.update({ templateStyles: templateStyles.value });
      if (res.success) {
        message.success(__('Styles saved!', 'giant-checkout-offers-for-woocommerce'));
      }
    } catch {
      message.error(__('Failed to save styles', 'giant-checkout-offers-for-woocommerce'));
    }
  }, 500);
};

// sprintf helper (wp.i18n has sprintf but just in case)
const sprintf = wp?.i18n?.sprintf || ((fmt, ...args) => args.reduce((s, a) => s.replace('%s', a), fmt));

onMounted(() => {
  initSettings();
});
</script>

<template>
  <div>
    <div class="tw-grid tw-grid-cols-2 lg:tw-grid-cols-3 xl:tw-grid-cols-4 tw-gap-5">
      <TemplateCard
        v-for="template in templates"
        :key="template.id"
        :template="template"
        :is-pro="isPro"
        @select="selectTemplate"
        @preview="previewTemplate"
        @customize="openStyleEditor"
      >
        <template #preview>
          <component :is="previewComponents[template.id]" :styles="getStylesForTemplate(template.id)" />
        </template>
      </TemplateCard>
    </div>

    <!-- Style Editor (shown below grid) -->
    <TemplateStyleEditor
      v-if="editingTemplateId"
      :styles="getStylesForTemplate(editingTemplateId)"
      :default-styles="getDefaultStyles(editingTemplateId)"
      :template-id="editingTemplateId"
      :template-name="templates.find(t => t.id === editingTemplateId)?.name || ''"
      @update="(s) => updateTemplateStyles(editingTemplateId, s)"
      @close="closeStyleEditor"
    />
  </div>
</template>
