<script setup>
import { ref, watch, computed } from 'vue';
import { commonStyleFields, templateStyleFields } from '../config/templates';

const props = defineProps({
  styles: { type: Object, required: true },
  defaultStyles: { type: Object, required: true },
  templateId: { type: String, required: true },
  templateName: { type: String, default: '' },
});

const emit = defineEmits(['update', 'close']);

const localStyles = ref({ ...props.styles });

watch(() => props.styles, (val) => {
  localStyles.value = { ...val };
}, { deep: true });

const specificFields = computed(() => templateStyleFields[props.templateId] || []);

const updateStyle = (key, value) => {
  localStyles.value[key] = value;
  emit('update', { ...localStyles.value });
};

const updateNumberStyle = (key, value, min, max) => {
  const num = Math.min(max, Math.max(min, parseInt(value) || min));
  localStyles.value[key] = num;
  emit('update', { ...localStyles.value });
};

const resetToDefaults = () => {
  localStyles.value = { ...props.defaultStyles };
  emit('update', { ...localStyles.value });
};
</script>

<template>
  <div class="tw-bg-white tw-rounded-xl tw-border tw-border-gray-200 tw-shadow-lg tw-p-5 tw-mt-4">
    <!-- Header -->
    <div class="tw-flex tw-items-center tw-justify-between tw-mb-5">
      <div class="tw-flex tw-items-center tw-gap-2">
        <svg class="tw-w-4 tw-h-4 tw-text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
        </svg>
        <h4 class="tw-font-semibold tw-text-gray-900 tw-text-sm tw-m-0">{{ __('Customize', 'giant-checkout-offers-for-woocommerce') }}: {{ templateName }}</h4>
      </div>
      <div class="tw-flex tw-items-center tw-gap-2">
        <button
          @click="resetToDefaults"
          class="tw-text-xs tw-text-gray-500 hover:tw-text-gray-700 tw-font-medium tw-transition-colors"
        >{{ __('Reset to Default', 'giant-checkout-offers-for-woocommerce') }}</button>
        <button
          @click="emit('close')"
          class="tw-w-7 tw-h-7 tw-flex tw-items-center tw-justify-center tw-rounded-lg tw-text-gray-400 hover:tw-bg-gray-100 hover:tw-text-gray-600 tw-transition-colors"
        >
          <svg class="tw-w-4 tw-h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
    </div>

    <!-- General Section -->
    <div class="tw-mb-5">
      <h5 class="tw-text-xs tw-font-semibold tw-text-gray-400 tw-uppercase tw-tracking-wider tw-mb-3 tw-m-0">{{ __('General', 'giant-checkout-offers-for-woocommerce') }}</h5>
      <div class="tw-grid tw-grid-cols-2 lg:tw-grid-cols-3 xl:tw-grid-cols-4 tw-gap-4">
        <template v-for="field in commonStyleFields" :key="field.key">
          <!-- Color field -->
          <div v-if="field.type === 'color'">
            <label class="tw-block tw-text-xs tw-font-medium tw-text-gray-600 tw-mb-1.5">{{ field.label }}</label>
            <div class="tw-flex tw-items-center tw-gap-2">
              <input
                type="color"
                :value="localStyles[field.key]"
                @input="updateStyle(field.key, $event.target.value)"
                class="tw-w-8 tw-h-8 tw-rounded tw-border tw-border-gray-300 tw-cursor-pointer tw-p-0.5"
              />
              <input
                type="text"
                :value="localStyles[field.key]"
                @change="updateStyle(field.key, $event.target.value)"
                class="tw-flex-1 tw-px-2 tw-py-1.5 tw-border tw-border-gray-300 tw-rounded tw-text-xs tw-text-gray-700 tw-font-mono focus:tw-outline-none focus:tw-border-blue-500"
              />
            </div>
          </div>
          <!-- Select field -->
          <div v-else-if="field.type === 'select'">
            <label class="tw-block tw-text-xs tw-font-medium tw-text-gray-600 tw-mb-1.5">{{ field.label }}</label>
            <select
              :value="localStyles[field.key]"
              @change="updateStyle(field.key, $event.target.value)"
              class="tw-w-full tw-px-2 tw-py-1.5 tw-border tw-border-gray-300 tw-rounded tw-text-xs tw-text-gray-700 tw-bg-white focus:tw-outline-none focus:tw-border-blue-500 tw-appearance-none tw-cursor-pointer"
              style="background-image: url('data:image/svg+xml;charset=UTF-8,%3csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 24 24%27 fill=%27none%27 stroke=%27%239ca3af%27 stroke-width=%272%27 stroke-linecap=%27round%27 stroke-linejoin=%27round%27%3e%3cpolyline points=%276 9 12 15 18 9%27%3e%3c/polyline%3e%3c/svg%3e'); background-repeat: no-repeat; background-position: right 8px center; background-size: 14px;"
            >
              <option v-for="opt in field.options" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
            </select>
          </div>
          <!-- Number field -->
          <div v-else-if="field.type === 'number'">
            <label class="tw-block tw-text-xs tw-font-medium tw-text-gray-600 tw-mb-1.5">{{ field.label }}</label>
            <div class="tw-flex tw-items-center tw-gap-2">
              <input
                type="number"
                :value="localStyles[field.key]"
                :min="field.min"
                :max="field.max"
                @input="updateNumberStyle(field.key, $event.target.value, field.min, field.max)"
                class="tw-w-full tw-px-2 tw-py-1.5 tw-border tw-border-gray-300 tw-rounded tw-text-xs tw-text-gray-700 focus:tw-outline-none focus:tw-border-blue-500"
              />
              <span class="tw-text-xs tw-text-gray-400 tw-flex-shrink-0">{{ field.unit }}</span>
            </div>
          </div>
        </template>
      </div>
    </div>

    <!-- Template-Specific Section -->
    <div v-if="specificFields.length > 0">
      <h5 class="tw-text-xs tw-font-semibold tw-text-gray-400 tw-uppercase tw-tracking-wider tw-mb-3 tw-m-0">{{ templateName }} {{ __('Options', 'giant-checkout-offers-for-woocommerce') }}</h5>
      <div class="tw-grid tw-grid-cols-2 lg:tw-grid-cols-3 xl:tw-grid-cols-4 tw-gap-4">
        <template v-for="field in specificFields" :key="field.key">
          <!-- Color field -->
          <div v-if="field.type === 'color'">
            <label class="tw-block tw-text-xs tw-font-medium tw-text-gray-600 tw-mb-1.5">{{ field.label }}</label>
            <div class="tw-flex tw-items-center tw-gap-2">
              <input
                type="color"
                :value="localStyles[field.key]"
                @input="updateStyle(field.key, $event.target.value)"
                class="tw-w-8 tw-h-8 tw-rounded tw-border tw-border-gray-300 tw-cursor-pointer tw-p-0.5"
              />
              <input
                type="text"
                :value="localStyles[field.key]"
                @change="updateStyle(field.key, $event.target.value)"
                class="tw-flex-1 tw-px-2 tw-py-1.5 tw-border tw-border-gray-300 tw-rounded tw-text-xs tw-text-gray-700 tw-font-mono focus:tw-outline-none focus:tw-border-blue-500"
              />
            </div>
          </div>
          <!-- Select field -->
          <div v-else-if="field.type === 'select'">
            <label class="tw-block tw-text-xs tw-font-medium tw-text-gray-600 tw-mb-1.5">{{ field.label }}</label>
            <select
              :value="localStyles[field.key]"
              @change="updateStyle(field.key, $event.target.value)"
              class="tw-w-full tw-px-2 tw-py-1.5 tw-border tw-border-gray-300 tw-rounded tw-text-xs tw-text-gray-700 tw-bg-white focus:tw-outline-none focus:tw-border-blue-500 tw-appearance-none tw-cursor-pointer"
              style="background-image: url('data:image/svg+xml;charset=UTF-8,%3csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 24 24%27 fill=%27none%27 stroke=%27%239ca3af%27 stroke-width=%272%27 stroke-linecap=%27round%27 stroke-linejoin=%27round%27%3e%3cpolyline points=%276 9 12 15 18 9%27%3e%3c/polyline%3e%3c/svg%3e'); background-repeat: no-repeat; background-position: right 8px center; background-size: 14px;"
            >
              <option v-for="opt in field.options" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
            </select>
          </div>
          <!-- Number field -->
          <div v-else-if="field.type === 'number'">
            <label class="tw-block tw-text-xs tw-font-medium tw-text-gray-600 tw-mb-1.5">{{ field.label }}</label>
            <div class="tw-flex tw-items-center tw-gap-2">
              <input
                type="number"
                :value="localStyles[field.key]"
                :min="field.min"
                :max="field.max"
                @input="updateNumberStyle(field.key, $event.target.value, field.min, field.max)"
                class="tw-w-full tw-px-2 tw-py-1.5 tw-border tw-border-gray-300 tw-rounded tw-text-xs tw-text-gray-700 focus:tw-outline-none focus:tw-border-blue-500"
              />
              <span class="tw-text-xs tw-text-gray-400 tw-flex-shrink-0">{{ field.unit }}</span>
            </div>
          </div>
        </template>
      </div>
    </div>
  </div>
</template>
