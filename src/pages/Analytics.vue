<script setup>
import { ref, onMounted, computed } from 'vue';
import { Line, Doughnut } from 'vue-chartjs';
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  ArcElement,
  Filler,
  Tooltip,
  Legend
} from 'chart.js';
import analyticsService from '../services/analyticsService';

const { __ } = wp?.i18n || { __: (t) => t };

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, ArcElement, Filler, Tooltip, Legend);

const proActive = computed(() => !!window.gcowPluginData?.proActive);

const loading = ref(false);
const period = ref(7);
const analytics = ref(null);

const loadAnalytics = async () => {
  loading.value = true;
  try {
    const res = await analyticsService.get(period.value);
    if (res.success) {
      analytics.value = res.data;
    }
  } catch {
    // fallback
  } finally {
    loading.value = false;
  }
};

const changePeriod = (days) => {
  period.value = days;
  loadAnalytics();
};

onMounted(() => {
  if (proActive.value) loadAnalytics();
});

const currencySymbol = computed(() => analytics.value?.currency_symbol || '$');

// Stats cards
const statCards = computed(() => {
  if (!analytics.value) return [];
  const o = analytics.value.overview;
  const sym = currencySymbol.value;
  return [
    {
      label: __('Total Impressions', 'giant-checkout-offers-for-woocommerce'),
      value: o.impressions.toLocaleString(),
      icon: 'eye',
      color: 'tw-from-blue-500 tw-to-blue-600',
      bg: 'tw-bg-blue-50',
      text: 'tw-text-blue-600',
    },
    {
      label: __('Total Clicks', 'giant-checkout-offers-for-woocommerce'),
      value: o.clicks.toLocaleString(),
      icon: 'cursor',
      color: 'tw-from-indigo-500 tw-to-indigo-600',
      bg: 'tw-bg-indigo-50',
      text: 'tw-text-indigo-600',
    },
    {
      label: __('Total Conversions', 'giant-checkout-offers-for-woocommerce'),
      value: o.conversions.toLocaleString(),
      icon: 'check',
      color: 'tw-from-green-500 tw-to-green-600',
      bg: 'tw-bg-green-50',
      text: 'tw-text-green-600',
    },
    {
      label: __('Conversion Rate', 'giant-checkout-offers-for-woocommerce'),
      value: o.conversion_rate + '%',
      icon: 'percent',
      color: 'tw-from-purple-500 tw-to-purple-600',
      bg: 'tw-bg-purple-50',
      text: 'tw-text-purple-600',
    },
    {
      label: __('Bump Revenue', 'giant-checkout-offers-for-woocommerce'),
      value: sym + o.revenue.toLocaleString(undefined, { minimumFractionDigits: 2 }),
      icon: 'dollar',
      color: 'tw-from-amber-500 tw-to-amber-600',
      bg: 'tw-bg-amber-50',
      text: 'tw-text-amber-600',
    },
  ];
});

// Revenue line chart
const revenueChartData = computed(() => {
  if (!analytics.value?.chart) return null;
  const c = analytics.value.chart;
  const sym = currencySymbol.value;
  return {
    labels: c.labels,
    datasets: [
      {
        label: `${__('Revenue', 'giant-checkout-offers-for-woocommerce')} (${sym})`,
        data: c.revenue,
        borderColor: '#4056E0',
        backgroundColor: 'rgba(64, 86, 224, 0.08)',
        fill: true,
        tension: 0.4,
        pointBackgroundColor: '#4056E0',
        pointBorderColor: '#fff',
        pointBorderWidth: 2,
        pointRadius: 4,
        pointHoverRadius: 6,
      },
    ],
  };
});

const revenueChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { display: false },
    tooltip: {
      backgroundColor: '#1f2937',
      titleFont: { size: 13 },
      bodyFont: { size: 13 },
      padding: 12,
      cornerRadius: 8,
      callbacks: {
        label: (ctx) => ' ' + currencySymbol.value + ctx.parsed.y.toFixed(2),
      },
    },
  },
  scales: {
    x: {
      grid: { display: false },
      ticks: { color: '#9ca3af', font: { size: 12 } },
    },
    y: {
      grid: { color: '#f3f4f6' },
      ticks: {
        color: '#9ca3af',
        font: { size: 12 },
        callback: (v) => currencySymbol.value + v,
      },
      beginAtZero: true,
    },
  },
};

// Conversions line chart
const conversionsChartData = computed(() => {
  if (!analytics.value?.chart) return null;
  const c = analytics.value.chart;
  return {
    labels: c.labels,
    datasets: [
      {
        label: __('Conversions', 'giant-checkout-offers-for-woocommerce'),
        data: c.conversions,
        borderColor: '#10b981',
        backgroundColor: 'rgba(16, 185, 129, 0.08)',
        fill: true,
        tension: 0.4,
        pointBackgroundColor: '#10b981',
        pointBorderColor: '#fff',
        pointBorderWidth: 2,
        pointRadius: 4,
        pointHoverRadius: 6,
      },
    ],
  };
});

const conversionsChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { display: false },
    tooltip: {
      backgroundColor: '#1f2937',
      titleFont: { size: 13 },
      bodyFont: { size: 13 },
      padding: 12,
      cornerRadius: 8,
    },
  },
  scales: {
    x: {
      grid: { display: false },
      ticks: { color: '#9ca3af', font: { size: 12 } },
    },
    y: {
      grid: { color: '#f3f4f6' },
      ticks: { color: '#9ca3af', font: { size: 12 }, precision: 0 },
      beginAtZero: true,
    },
  },
};

// Doughnut chart for top bumps by revenue
const doughnutChartData = computed(() => {
  if (!analytics.value?.bump_stats?.length) return null;
  const top = analytics.value.bump_stats.slice(0, 5);
  const colors = ['#4056E0', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'];
  return {
    labels: top.map(b => b.name),
    datasets: [
      {
        data: top.map(b => b.revenue),
        backgroundColor: colors.slice(0, top.length),
        borderWidth: 0,
        hoverOffset: 4,
      },
    ],
  };
});

const doughnutChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  cutout: '65%',
  plugins: {
    legend: {
      position: 'bottom',
      labels: {
        padding: 16,
        usePointStyle: true,
        pointStyle: 'circle',
        font: { size: 12 },
        color: '#374151',
      },
    },
    tooltip: {
      backgroundColor: '#1f2937',
      padding: 12,
      cornerRadius: 8,
      callbacks: {
        label: (ctx) => ' ' + currencySymbol.value + ctx.parsed.toFixed(2),
      },
    },
  },
};
</script>

<template>
  <!-- Pro gate -->
  <div v-if="!proActive" class="tw-flex tw-flex-col tw-items-center tw-justify-center tw-py-20 tw-text-center">
    <div class="tw-w-16 tw-h-16 tw-bg-gradient-to-br tw-from-amber-400 tw-to-orange-500 tw-rounded-2xl tw-flex tw-items-center tw-justify-center tw-mb-5 tw-shadow-lg">
      <svg class="tw-w-8 tw-h-8 tw-text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
      </svg>
    </div>
    <h2 class="tw-text-2xl tw-font-bold tw-text-gray-900 tw-mb-2">{{ __('Analytics is a Pro Feature', 'giant-checkout-offers-for-woocommerce') }}</h2>
    <p class="tw-text-gray-500 tw-max-w-md tw-mb-6">{{ __('Track impressions, clicks, conversions and revenue for every bump. Upgrade to Smart Order Bump Pro to unlock the full analytics dashboard.', 'giant-checkout-offers-for-woocommerce') }}</p>
    <span class="tw-inline-flex tw-items-center tw-gap-1.5 tw-px-5 tw-py-2 tw-text-white tw-text-sm tw-font-semibold tw-rounded-full tw-shadow-md" style="background: linear-gradient(135deg, #fbbf24, #f97316);">
      <svg class="tw-w-4 tw-h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
      {{ __('Available in Smart Order Bump Pro', 'giant-checkout-offers-for-woocommerce') }}
    </span>
  </div>

  <div v-else class="tw-space-y-6">
    <!-- Header -->
    <div class="tw-flex tw-items-center tw-justify-between">
      <div>
        <h2 class="tw-text-xl tw-font-bold tw-text-gray-900 tw-m-0">{{ __('Analytics', 'giant-checkout-offers-for-woocommerce') }}</h2>
        <p class="tw-text-sm tw-text-gray-500 tw-mt-1 tw-m-0">{{ __('Track your order bump performance and revenue', 'giant-checkout-offers-for-woocommerce') }}</p>
      </div>
      <!-- Period Selector -->
      <div class="tw-inline-flex tw-rounded-lg tw-border tw-border-gray-300 tw-overflow-hidden">
        <button
          v-for="opt in [
            { days: 7,  label: __('7 Days', 'giant-checkout-offers-for-woocommerce') },
            { days: 30, label: __('30 Days', 'giant-checkout-offers-for-woocommerce') },
            { days: 90, label: __('90 Days', 'giant-checkout-offers-for-woocommerce') }
          ]"
          :key="opt.days"
          @click="changePeriod(opt.days)"
          type="button"
          class="tw-px-4 tw-py-2 tw-text-sm tw-font-medium tw-transition-colors tw-border-none tw-cursor-pointer"
          :class="period === opt.days ? 'tw-bg-blue-500 tw-text-white' : 'tw-bg-white tw-text-gray-600 hover:tw-bg-gray-50'"
        >{{ opt.label }}</button>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading && !analytics" class="tw-text-center tw-py-20">
      <svg class="tw-w-8 tw-h-8 tw-mx-auto tw-text-gray-400 tw-animate-spin" fill="none" viewBox="0 0 24 24">
        <circle class="tw-opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="tw-opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
      </svg>
      <p class="tw-text-sm tw-text-gray-500 tw-mt-3">{{ __('Loading analytics...', 'giant-checkout-offers-for-woocommerce') }}</p>
    </div>

    <template v-if="analytics">
      <!-- Stats Cards -->
      <div class="tw-grid tw-grid-cols-5 tw-gap-5">
        <div
          v-for="card in statCards"
          :key="card.label"
          class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-gray-200 tw-p-5"
        >
          <div class="tw-flex tw-items-center tw-justify-between tw-mb-3">
            <span class="tw-text-sm tw-font-medium tw-text-gray-500">{{ card.label }}</span>
            <div class="tw-w-9 tw-h-9 tw-rounded-lg tw-flex tw-items-center tw-justify-center" :class="card.bg">
              <!-- Eye -->
              <svg v-if="card.icon === 'eye'" class="tw-w-5 tw-h-5" :class="card.text" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
              </svg>
              <!-- Cursor / Click -->
              <svg v-if="card.icon === 'cursor'" class="tw-w-5 tw-h-5" :class="card.text" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5" />
              </svg>
              <!-- Check -->
              <svg v-if="card.icon === 'check'" class="tw-w-5 tw-h-5" :class="card.text" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <!-- Percent -->
              <svg v-if="card.icon === 'percent'" class="tw-w-5 tw-h-5" :class="card.text" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
              </svg>
              <!-- Dollar -->
              <svg v-if="card.icon === 'dollar'" class="tw-w-5 tw-h-5" :class="card.text" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
          <div class="tw-text-2xl tw-font-bold tw-text-gray-900">{{ card.value }}</div>
        </div>
      </div>

      <!-- Charts Row -->
      <div class="tw-grid tw-grid-cols-3 tw-gap-5">
        <!-- Revenue Chart -->
        <div class="tw-col-span-2 tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-gray-200 tw-p-6">
          <h3 class="tw-text-base tw-font-semibold tw-text-gray-900 tw-m-0 tw-mb-4">{{ __('Revenue Over Time', 'giant-checkout-offers-for-woocommerce') }}</h3>
          <div style="height: 280px;">
            <Line v-if="revenueChartData" :data="revenueChartData" :options="revenueChartOptions" />
            <div v-else class="tw-flex tw-items-center tw-justify-center tw-h-full tw-text-sm tw-text-gray-400">
              {{ __('No revenue data yet', 'giant-checkout-offers-for-woocommerce') }}
            </div>
          </div>
        </div>

        <!-- Doughnut Chart -->
        <div class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-gray-200 tw-p-6">
          <h3 class="tw-text-base tw-font-semibold tw-text-gray-900 tw-m-0 tw-mb-4">{{ __('Revenue by Bump', 'giant-checkout-offers-for-woocommerce') }}</h3>
          <div style="height: 280px;">
            <Doughnut v-if="doughnutChartData" :data="doughnutChartData" :options="doughnutChartOptions" />
            <div v-else class="tw-flex tw-items-center tw-justify-center tw-h-full tw-text-sm tw-text-gray-400">
              {{ __('No bump data yet', 'giant-checkout-offers-for-woocommerce') }}
            </div>
          </div>
        </div>
      </div>

      <!-- Conversions Chart -->
      <div class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-gray-200 tw-p-6">
        <h3 class="tw-text-base tw-font-semibold tw-text-gray-900 tw-m-0 tw-mb-4">{{ __('Conversions Over Time', 'giant-checkout-offers-for-woocommerce') }}</h3>
        <div style="height: 260px;">
          <Line v-if="conversionsChartData" :data="conversionsChartData" :options="conversionsChartOptions" />
          <div v-else class="tw-flex tw-items-center tw-justify-center tw-h-full tw-text-sm tw-text-gray-400">
            {{ __('No conversion data yet', 'giant-checkout-offers-for-woocommerce') }}
          </div>
        </div>
      </div>

      <!-- Bump Performance Table -->
      <div class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-border tw-border-gray-200 tw-overflow-hidden">
        <div class="tw-px-6 tw-py-4 tw-border-b tw-border-gray-200">
          <h3 class="tw-text-base tw-font-semibold tw-text-gray-900 tw-m-0">{{ __('Bump Performance', 'giant-checkout-offers-for-woocommerce') }}</h3>
        </div>
        <table v-if="analytics.bump_stats.length > 0" class="tw-w-full tw-text-sm">
          <thead>
            <tr class="tw-bg-gray-50">
              <th class="tw-text-left tw-px-6 tw-py-3 tw-font-semibold tw-text-gray-600">{{ __('Bump Name', 'giant-checkout-offers-for-woocommerce') }}</th>
              <th class="tw-text-center tw-px-6 tw-py-3 tw-font-semibold tw-text-gray-600">{{ __('Impressions', 'giant-checkout-offers-for-woocommerce') }}</th>
              <th class="tw-text-center tw-px-6 tw-py-3 tw-font-semibold tw-text-gray-600">{{ __('Conversions', 'giant-checkout-offers-for-woocommerce') }}</th>
              <th class="tw-text-center tw-px-6 tw-py-3 tw-font-semibold tw-text-gray-600">{{ __('Rate', 'giant-checkout-offers-for-woocommerce') }}</th>
              <th class="tw-text-right tw-px-6 tw-py-3 tw-font-semibold tw-text-gray-600">{{ __('Revenue', 'giant-checkout-offers-for-woocommerce') }}</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="bump in analytics.bump_stats"
              :key="bump.bump_id"
              class="tw-border-t tw-border-gray-100 hover:tw-bg-gray-50 tw-transition-colors"
            >
              <td class="tw-px-6 tw-py-3.5 tw-font-medium tw-text-gray-900">{{ bump.name }}</td>
              <td class="tw-text-center tw-px-6 tw-py-3.5 tw-text-gray-600">{{ bump.impressions.toLocaleString() }}</td>
              <td class="tw-text-center tw-px-6 tw-py-3.5 tw-text-gray-600">{{ bump.conversions.toLocaleString() }}</td>
              <td class="tw-text-center tw-px-6 tw-py-3.5">
                <span
                  class="tw-inline-block tw-px-2.5 tw-py-0.5 tw-rounded-full tw-text-xs tw-font-medium"
                  :class="bump.conversion_rate >= 10 ? 'tw-bg-green-100 tw-text-green-700' : bump.conversion_rate >= 5 ? 'tw-bg-yellow-100 tw-text-yellow-700' : 'tw-bg-gray-100 tw-text-gray-600'"
                >{{ bump.conversion_rate }}%</span>
              </td>
              <td class="tw-text-right tw-px-6 tw-py-3.5 tw-font-semibold tw-text-gray-900">{{ currencySymbol }}{{ bump.revenue.toLocaleString(undefined, { minimumFractionDigits: 2 }) }}</td>
            </tr>
          </tbody>
        </table>
        <div v-else class="tw-px-6 tw-py-12 tw-text-center tw-text-sm tw-text-gray-400">
          {{ __('No bump performance data yet. Analytics will appear once bumps start getting impressions.', 'giant-checkout-offers-for-woocommerce') }}
        </div>
      </div>
    </template>
  </div>
</template>
