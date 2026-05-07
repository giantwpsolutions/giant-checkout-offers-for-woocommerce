<script setup>
defineProps({
  title: { type: String, default: '' },
  productName: { type: String, default: 'Product Name' },
  description: { type: String, default: '' },
  buttonText: { type: String, default: '' },
  price: { type: String, default: '$24.99' },
  originalPrice: { type: String, default: '' },
  discountValue: { type: Number, default: 10 },
  discountType: { type: String, default: 'percentage' },
  image: { type: String, default: '' },
  styles: { type: Object, default: () => ({}) },
});
</script>

<template>
  <div class="tw-w-full tw-max-w-[280px]">
    <div
      class="tw-shadow-md tw-overflow-hidden tw-flex tw-scale-90"
      :style="{ backgroundColor: styles.backgroundColor, borderWidth: (styles.borderWidth || 1) + 'px', borderStyle: styles.borderStyle || 'solid', borderColor: styles.borderColor, borderRadius: (styles.borderRadius || 8) + 'px' }"
    >
      <div class="tw-w-2/5 tw-p-3 tw-flex tw-items-center tw-justify-center" :style="{ backgroundColor: styles.buttonColor }">
        <img v-if="image" :src="image" :alt="productName" class="tw-w-full tw-h-full tw-object-cover" />
        <div v-else class="tw-w-10 tw-h-10 tw-bg-white/30 tw-rounded-lg tw-flex tw-items-center tw-justify-center">
          <svg class="tw-w-5 tw-h-5 tw-text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        </div>
      </div>
      <div class="tw-w-3/5 tw-p-3">
        <div class="tw-flex tw-items-center tw-gap-0.5 tw-mb-1">
          <svg v-for="i in 5" :key="i" class="tw-w-3 tw-h-3" :style="{ color: styles.starColor || '#facc15' }" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
        </div>
        <h4 class="tw-font-bold tw-text-sm tw-m-0" :style="{ color: styles.textColor }">{{ productName }}</h4>
        <p v-if="description" class="tw-text-xs tw-mt-0.5 tw-mb-0" :style="{ color: styles.descriptionColor || '#6b7280' }">{{ description }}</p>
        <div class="tw-flex tw-items-center tw-justify-between tw-mt-1.5">
          <span class="tw-font-bold tw-text-base" :style="{ color: styles.priceColor || '#0d9488' }">{{ price }}</span>
          <div class="tw-w-7 tw-h-4 tw-rounded-full tw-relative" :style="{ backgroundColor: styles.toggleColor || '#14b8a6' }">
            <div class="tw-absolute tw-right-0.5 tw-top-0.5 tw-w-3 tw-h-3 tw-bg-white tw-rounded-full"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
