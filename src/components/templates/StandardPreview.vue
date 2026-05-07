<script setup>
defineProps({
  title: { type: String, default: 'Add this to your order!' },
  productName: { type: String, default: 'Product Name' },
  description: { type: String, default: '' },
  buttonText: { type: String, default: 'Yes, add to my order!' },
  price: { type: String, default: '$19.99' },
  originalPrice: { type: String, default: '$29.99' },
  discountValue: { type: Number, default: 10 },
  discountType: { type: String, default: 'percentage' },
  image: { type: String, default: '' },
  styles: { type: Object, default: () => ({}) },
});
</script>

<template>
  <div class="tw-w-full tw-max-w-[280px]">
    <div
      class="tw-overflow-hidden tw-shadow-sm tw-scale-90"
      :style="{ backgroundColor: styles.backgroundColor, borderWidth: (styles.borderWidth || 1) + 'px', borderStyle: styles.borderStyle || 'dashed', borderColor: styles.borderColor, borderRadius: (styles.borderRadius || 8) + 'px' }"
    >
      <div :style="{ backgroundColor: styles.headerBgColor || styles.buttonColor, padding: '8px 16px' }">
        <p class="tw-text-sm tw-font-semibold tw-m-0" :style="{ color: styles.headerTextColor || '#ffffff' }">{{ title }}</p>
      </div>
      <div class="tw-flex tw-gap-3" :style="{ padding: (styles.padding || 12) + 'px' }">
        <div class="tw-w-12 tw-h-12 tw-bg-gray-100 tw-rounded tw-flex tw-items-center tw-justify-center tw-flex-shrink-0 tw-overflow-hidden">
          <img v-if="image" :src="image" :alt="productName" class="tw-w-full tw-h-full tw-object-cover" />
          <svg v-else class="tw-w-6 tw-h-6 tw-text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        </div>
        <div class="tw-flex-1 tw-min-w-0">
          <h4 class="tw-font-medium tw-text-sm tw-m-0" :style="{ color: styles.textColor }">{{ productName }}</h4>
          <p v-if="description" class="tw-text-xs tw-mt-1 tw-mb-0" :style="{ color: styles.descriptionColor || '#6b7280' }">{{ description }}</p>
          <div class="tw-flex tw-items-center tw-gap-2 tw-mt-1.5">
            <span class="tw-text-sm tw-font-bold" :style="{ color: styles.priceColor || '#16a34a' }">{{ price }}</span>
            <span class="tw-text-xs tw-text-gray-400 tw-line-through">{{ originalPrice }}</span>
          </div>
        </div>
      </div>
      <div :style="{ padding: '0 ' + (styles.padding || 12) + 'px ' + (styles.padding || 12) + 'px' }">
        <label
          class="tw-flex tw-items-center tw-gap-2.5 tw-p-2.5 tw-rounded tw-border tw-cursor-pointer"
          :style="{ backgroundColor: styles.ctaBgColor || '#eff6ff', borderColor: styles.ctaBorderColor || '#bfdbfe' }"
        >
          <input type="checkbox" class="tw-w-4 tw-h-4 tw-rounded" checked disabled />
          <span class="tw-text-xs tw-font-medium" :style="{ color: styles.buttonTextColor || '#374151' }">{{ buttonText }}</span>
        </label>
      </div>
    </div>
  </div>
</template>
