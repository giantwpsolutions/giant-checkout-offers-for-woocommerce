<script setup>
defineProps({
  title: { type: String, default: '' },
  productName: { type: String, default: 'Product Name' },
  description: { type: String, default: '' },
  buttonText: { type: String, default: 'Yes, I Want This!' },
  price: { type: String, default: '$19.99' },
  originalPrice: { type: String, default: '$39.99' },
  discountValue: { type: Number, default: 50 },
  discountType: { type: String, default: 'percentage' },
  image: { type: String, default: '' },
  styles: { type: Object, default: () => ({}) },
});
</script>

<template>
  <div class="tw-w-full tw-max-w-[260px]">
    <div class="tw-relative tw-pt-3 tw-scale-90">
      <div class="tw-absolute tw-top-0 tw-left-1/2 tw-transform tw--translate-x-1/2 tw-z-10">
        <div class="tw-text-[10px] tw-font-bold tw-px-2.5 tw-py-1 tw-rounded-full tw-shadow-md tw-whitespace-nowrap" :style="{ backgroundColor: styles.badgeColor || '#f43f5e', color: styles.badgeTextColor || '#ffffff' }">
          {{ discountValue }}{{ discountType === 'percentage' ? '%' : '$' }} OFF
        </div>
      </div>
      <div
        class="tw-shadow-lg tw-mt-1"
        :style="{ backgroundColor: styles.backgroundColor, borderWidth: (styles.borderWidth || 1) + 'px', borderStyle: styles.borderStyle || 'solid', borderColor: styles.borderColor, borderRadius: (styles.borderRadius || 12) + 'px', padding: (styles.padding || 12) + 'px' }"
      >
        <div class="tw-flex tw-gap-2.5">
          <div class="tw-w-12 tw-h-12 tw-rounded-lg tw-flex tw-items-center tw-justify-center tw-flex-shrink-0 tw-overflow-hidden" :style="{ backgroundColor: (styles.badgeColor || '#f43f5e') + '15' }">
            <img v-if="image" :src="image" :alt="productName" class="tw-w-full tw-h-full tw-object-cover" />
            <svg v-else class="tw-w-6 tw-h-6" :style="{ color: (styles.badgeColor || '#f43f5e') + '80' }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
          </div>
          <div class="tw-flex-1">
            <h4 class="tw-font-bold tw-text-sm tw-m-0" :style="{ color: styles.textColor }">{{ productName }}</h4>
            <p v-if="description" class="tw-text-xs tw-mt-0.5 tw-mb-0" :style="{ color: styles.descriptionColor || '#6b7280' }">{{ description }}</p>
            <div class="tw-flex tw-items-baseline tw-gap-1.5 tw-mt-1">
              <span class="tw-text-base tw-font-bold" :style="{ color: styles.priceColor || '#f43f5e' }">{{ price }}</span>
              <span class="tw-text-xs tw-text-gray-400 tw-line-through">{{ originalPrice }}</span>
              <span class="tw-text-[10px] tw-px-1 tw-rounded tw-font-semibold" :style="{ backgroundColor: (styles.badgeColor || '#f43f5e') + '20', color: styles.badgeColor || '#f43f5e' }">-{{ discountValue }}{{ discountType === 'percentage' ? '%' : '$' }}</span>
            </div>
          </div>
        </div>
        <button class="tw-w-full tw-mt-2.5 tw-font-bold tw-py-2 tw-rounded-lg tw-text-xs" :style="{ backgroundColor: styles.buttonColor, color: styles.buttonTextColor || '#ffffff' }">
          {{ buttonText }}
        </button>
      </div>
    </div>
  </div>
</template>
