<script setup>
defineProps({
  title: { type: String, default: 'Exclusive Offer' },
  productName: { type: String, default: 'Product Name' },
  description: { type: String, default: '' },
  buttonText: { type: String, default: 'Add to Order' },
  price: { type: String, default: '$29.99' },
  originalPrice: { type: String, default: '$49.99' },
  discountValue: { type: Number, default: 10 },
  discountType: { type: String, default: 'percentage' },
  image: { type: String, default: '' },
  styles: { type: Object, default: () => ({}) },
});
</script>

<template>
  <div class="tw-w-full tw-max-w-[220px]">
    <div
      class="tw-shadow-md tw-relative tw-overflow-hidden tw-scale-90"
      :style="{ backgroundColor: styles.backgroundColor, color: styles.textColor, borderWidth: (styles.borderWidth || 1) + 'px', borderStyle: styles.borderStyle || 'none', borderColor: styles.borderColor, borderRadius: (styles.borderRadius || 8) + 'px', padding: (styles.padding || 16) + 'px' }"
    >
      <div class="tw-absolute tw-top-0 tw-right-0 tw-w-16 tw-h-16 tw-bg-white/10 tw-rounded-full tw--mr-8 tw--mt-8"></div>
      <div class="tw-text-center tw-relative">
        <span class="tw-text-[10px] tw-uppercase tw-tracking-wider tw-opacity-80">{{ title }}</span>
        <div class="tw-w-12 tw-h-12 tw-rounded-lg tw-mx-auto tw-mt-2 tw-flex tw-items-center tw-justify-center tw-overflow-hidden" :class="image ? '' : 'tw-bg-white/20'">
          <img v-if="image" :src="image" :alt="productName" class="tw-w-full tw-h-full tw-object-cover tw-rounded-lg" />
          <svg v-else class="tw-w-6 tw-h-6 tw-text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/></svg>
        </div>
        <h4 class="tw-font-bold tw-text-base tw-mt-2 tw-mb-0.5">{{ productName }}</h4>
        <p v-if="description" class="tw-text-xs tw-mt-0.5 tw-mb-1" :style="{ color: styles.descriptionColor || '#c7d2fe' }">{{ description }}</p>
        <div class="tw-flex tw-items-center tw-justify-center tw-gap-2">
          <span class="tw-text-lg tw-font-bold" :style="{ color: styles.priceColor }">{{ price }}</span>
          <span class="tw-text-xs tw-line-through tw-opacity-60">{{ originalPrice }}</span>
        </div>
        <button class="tw-mt-2 tw-w-full tw-font-bold tw-py-1.5 tw-rounded tw-text-xs" :style="{ backgroundColor: styles.buttonColor, color: styles.buttonTextColor || styles.backgroundColor }">{{ buttonText }}</button>
      </div>
    </div>
  </div>
</template>
