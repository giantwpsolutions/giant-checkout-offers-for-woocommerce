<script setup>
defineProps({
  title: { type: String, default: 'Add Product' },
  productName: { type: String, default: '' },
  description: { type: String, default: 'One-time offer' },
  buttonText: { type: String, default: '' },
  price: { type: String, default: '$19.99' },
  originalPrice: { type: String, default: '' },
  discountValue: { type: Number, default: 33 },
  discountType: { type: String, default: 'percentage' },
  image: { type: String, default: '' },
  styles: { type: Object, default: () => ({}) },
});
</script>

<template>
  <div class="tw-w-full tw-max-w-[280px]">
    <div
      class="tw-shadow-sm tw-scale-90"
      :style="{ backgroundColor: styles.backgroundColor, borderWidth: (styles.borderWidth || 1) + 'px', borderStyle: styles.borderStyle || 'solid', borderColor: styles.borderColor, borderRadius: (styles.borderRadius || 8) + 'px', padding: (styles.padding || 12) + 'px' }"
    >
      <div class="tw-flex tw-items-center tw-gap-3">
        <input type="checkbox" class="tw-w-4 tw-h-4 tw-rounded" checked disabled />
        <div class="tw-w-12 tw-h-12 tw-bg-gray-100 tw-rounded tw-flex tw-items-center tw-justify-center tw-flex-shrink-0 tw-overflow-hidden">
          <img v-if="image" :src="image" :alt="title" class="tw-w-full tw-h-full tw-object-cover" />
          <svg v-else class="tw-w-6 tw-h-6 tw-text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        </div>
        <div class="tw-flex-1 tw-min-w-0">
          <div class="tw-flex tw-items-center tw-justify-between">
            <h4 class="tw-font-medium tw-text-sm tw-m-0" :style="{ color: styles.textColor }">{{ title }}</h4>
            <div class="tw-text-right">
              <span class="tw-font-bold tw-text-sm" :style="{ color: styles.priceColor || '#111827' }">{{ price }}</span>
              <span class="tw-text-xs tw-block" :style="{ color: styles.saveBadgeColor || '#059669' }">Save {{ discountValue }}{{ discountType === 'percentage' ? '%' : '$' }}</span>
            </div>
          </div>
          <p class="tw-text-xs tw-mt-0.5 tw-mb-0" :style="{ color: styles.descriptionColor || '#9ca3af' }">{{ description }}</p>
        </div>
      </div>
    </div>
  </div>
</template>
