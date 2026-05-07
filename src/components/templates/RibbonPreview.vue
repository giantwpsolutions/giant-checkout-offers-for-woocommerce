<script setup>
defineProps({
  title: { type: String, default: 'Limited Time' },
  productName: { type: String, default: 'Product Name' },
  description: { type: String, default: '' },
  buttonText: { type: String, default: 'Add' },
  price: { type: String, default: '$29.99' },
  originalPrice: { type: String, default: '$49.99' },
  discountValue: { type: Number, default: 40 },
  discountType: { type: String, default: 'percentage' },
  image: { type: String, default: '' },
  styles: { type: Object, default: () => ({}) },
});
</script>

<template>
  <div class="tw-w-full tw-max-w-[280px]">
    <div
      class="tw-shadow-md tw-relative tw-overflow-hidden tw-scale-90"
      :style="{ backgroundColor: styles.backgroundColor, borderWidth: (styles.borderWidth || 1) + 'px', borderStyle: styles.borderStyle || 'solid', borderColor: styles.borderColor, borderRadius: (styles.borderRadius || 8) + 'px' }"
    >
      <!-- Corner Ribbon -->
      <div class="tw-absolute tw-top-0 tw-right-0 tw-overflow-hidden tw-w-16 tw-h-16">
        <div class="tw-absolute tw-transform tw-rotate-45 tw-text-white tw-text-[10px] tw-font-bold tw-text-center tw-py-0.5 tw-right-[-24px] tw-top-[12px] tw-w-[90px] tw-shadow-sm" :style="{ backgroundColor: styles.ribbonColor || '#ef4444' }">
          {{ discountValue }}{{ discountType === 'percentage' ? '%' : '$' }} OFF
        </div>
      </div>
      <div class="tw-flex tw-gap-2.5" :style="{ padding: (styles.padding || 12) + 'px' }">
        <div class="tw-w-12 tw-h-12 tw-rounded-lg tw-flex tw-items-center tw-justify-center tw-flex-shrink-0 tw-overflow-hidden" :style="{ backgroundColor: (styles.accentColor || '#d97706') + '20' }">
          <img v-if="image" :src="image" :alt="productName" class="tw-w-full tw-h-full tw-object-cover" />
          <svg v-else class="tw-w-6 tw-h-6" :style="{ color: styles.accentColor || '#d97706' }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        </div>
        <div class="tw-flex-1">
          <span class="tw-text-[10px] tw-font-semibold tw-uppercase" :style="{ color: styles.accentColor || '#d97706' }">{{ title }}</span>
          <h4 class="tw-font-bold tw-text-sm tw-m-0" :style="{ color: styles.textColor }">{{ productName }}</h4>
          <p v-if="description" class="tw-text-xs tw-mt-0.5 tw-mb-0" :style="{ color: styles.descriptionColor || '#6b7280' }">{{ description }}</p>
          <div class="tw-flex tw-items-center tw-gap-2 tw-mt-1.5">
            <span class="tw-text-base tw-font-bold" :style="{ color: styles.priceColor || '#111827' }">{{ price }}</span>
            <span class="tw-text-xs tw-text-gray-400 tw-line-through">{{ originalPrice }}</span>
            <button class="tw-font-semibold tw-px-2 tw-py-1 tw-rounded tw-text-[10px]" :style="{ backgroundColor: styles.buttonColor, color: styles.buttonTextColor || '#ffffff' }">{{ buttonText }}</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
