<script setup>
import { ref, onMounted } from 'vue';
import { ElTable, ElTableColumn, ElTag, ElSwitch, ElButton, ElEmpty, ElPopconfirm } from 'element-plus';
import { Edit, Delete } from '@element-plus/icons-vue';
import BumpModal from '../components/BumpModal.vue';
import bumpService from '../services/bumpService';
import message from '../utils/message';

const { __ } = wp?.i18n || { __: (t) => t };

const bumps = ref([]);
const loading = ref(false);
const selectedBumps = ref([]);
const showModal = ref(false);
const editingBump = ref(null);

// Position label map
const positionLabels = {
  checkout_before_payment: __('Before Payment', 'giant-checkout-offers-for-woocommerce'),
  checkout_after_payment:  __('After Payment', 'giant-checkout-offers-for-woocommerce'),
  checkout_before_review:  __('Before Review', 'giant-checkout-offers-for-woocommerce'),
  checkout_after_review:   __('After Review', 'giant-checkout-offers-for-woocommerce'),
  cart_before_totals:      __('Cart - Before Totals', 'giant-checkout-offers-for-woocommerce'),
  cart_after_totals:       __('Cart - After Totals', 'giant-checkout-offers-for-woocommerce'),
};

/**
 * Load all bumps from API.
 */
const loadBumps = async () => {
  loading.value = true;
  try {
    const res = await bumpService.getAll();
    if (res.success) {
      bumps.value = res.data;
    }
  } catch (err) {
    message.error(__('Failed to load bumps', 'giant-checkout-offers-for-woocommerce'));
  } finally {
    loading.value = false;
  }
};

const handleSelectionChange = (selection) => {
  selectedBumps.value = selection;
};

const handleStatusChange = async (row) => {
  try {
    const res = await bumpService.toggleStatus(row.id);
    if (res.success) {
      const statusText = res.data.status === 'active'
        ? __('enabled', 'giant-checkout-offers-for-woocommerce')
        : __('disabled', 'giant-checkout-offers-for-woocommerce');
      message.success(`"${row.name}" ${__('has been', 'giant-checkout-offers-for-woocommerce')} ${statusText}`);
      row.status = res.data.status;
    }
  } catch (err) {
    row.status = row.status === 'active' ? 'inactive' : 'active';
    message.error(__('Failed to update status', 'giant-checkout-offers-for-woocommerce'));
  }
};

const handleEdit = (row) => {
  editingBump.value = { ...row };
  showModal.value = true;
};

const handleDelete = async (row) => {
  try {
    const res = await bumpService.delete(row.id);
    if (res.success) {
      bumps.value = bumps.value.filter(b => b.id !== row.id);
      message.success(`"${row.name}" ${__('has been deleted', 'giant-checkout-offers-for-woocommerce')}`);
    }
  } catch (err) {
    message.error(__('Failed to delete bump', 'giant-checkout-offers-for-woocommerce'));
  }
};

const handleBulkDelete = async () => {
  if (selectedBumps.value.length === 0) return;

  const deletePromises = selectedBumps.value.map(b => bumpService.delete(b.id));
  try {
    await Promise.all(deletePromises);
    const ids = selectedBumps.value.map(b => b.id);
    bumps.value = bumps.value.filter(b => !ids.includes(b.id));
    message.success(`${ids.length} ${__('bump(s) deleted', 'giant-checkout-offers-for-woocommerce')}`);
    selectedBumps.value = [];
  } catch (err) {
    message.error(__('Failed to delete some bumps', 'giant-checkout-offers-for-woocommerce'));
    loadBumps();
  }
};

const getPositionLabel = (position) => {
  return positionLabels[position] || position;
};

const getDiscountLabel = (row) => {
  if (row.discountType === 'percentage') {
    return `${row.discountValue}%`;
  }
  return `$${row.discountValue}`;
};

const openCreateModal = () => {
  editingBump.value = null;
  showModal.value = true;
};

const handleSave = async (data) => {
  try {
    let res;
    if (data.id && bumps.value.find(b => b.id === data.id)) {
      res = await bumpService.update(data.id, data);
    } else {
      res = await bumpService.create(data);
    }

    if (res.success) {
      message.success(res.message);
      loadBumps();
    }
  } catch (err) {
    message.error(__('Failed to save bump', 'giant-checkout-offers-for-woocommerce'));
  }
};

onMounted(() => {
  loadBumps();
});
</script>

<template>
  <div class="tw-bg-white tw-rounded-lg tw-shadow tw-overflow-hidden">
    <!-- Bulk Actions Bar -->
    <div v-if="selectedBumps.length > 0" class="tw-px-4 tw-py-3 tw-bg-blue-50 tw-border-b tw-border-blue-100 tw-flex tw-items-center tw-justify-between">
      <span class="tw-text-sm tw-text-blue-700">
        {{ selectedBumps.length }} {{ __('bump(s) selected', 'giant-checkout-offers-for-woocommerce') }}
      </span>
      <ElPopconfirm
        :title="__('Delete selected bumps?', 'giant-checkout-offers-for-woocommerce')"
        :confirm-button-text="__('Delete', 'giant-checkout-offers-for-woocommerce')"
        :cancel-button-text="__('Cancel', 'giant-checkout-offers-for-woocommerce')"
        @confirm="handleBulkDelete"
      >
        <template #reference>
          <ElButton type="danger" size="small">
            <Delete class="tw-w-4 tw-h-4 tw-mr-1" />
            {{ __('Delete Selected', 'giant-checkout-offers-for-woocommerce') }}
          </ElButton>
        </template>
      </ElPopconfirm>
    </div>

    <!-- Table -->
    <ElTable
      :data="bumps"
      style="width: 100%"
      v-if="bumps.length > 0"
      v-loading="loading"
      @selection-change="handleSelectionChange"
      :header-cell-style="{ background: '#f9fafb', color: '#374151', fontWeight: '600' }"
      :row-class-name="({ row }) => row.status !== 'active' ? 'inactive-row' : ''"
    >
      <ElTableColumn type="selection" width="50" />

      <ElTableColumn prop="name" :label="__('Bump Name', 'giant-checkout-offers-for-woocommerce')" min-width="200">
        <template #default="{ row }">
          <div class="tw-py-1">
            <div class="tw-font-medium tw-text-gray-900">{{ row.name }}</div>
            <div class="tw-text-xs tw-text-gray-500 tw-mt-0.5">{{ row.template }} {{ __('template', 'giant-checkout-offers-for-woocommerce') }}</div>
          </div>
        </template>
      </ElTableColumn>

      <ElTableColumn prop="discountValue" :label="__('Discount', 'giant-checkout-offers-for-woocommerce')" width="120" align="center">
        <template #default="{ row }">
          <ElTag type="success" effect="light" round>{{ getDiscountLabel(row) }}</ElTag>
        </template>
      </ElTableColumn>

      <ElTableColumn prop="position" :label="__('Position', 'giant-checkout-offers-for-woocommerce')" width="160" align="center">
        <template #default="{ row }">
          <ElTag effect="plain" round>{{ getPositionLabel(row.position) }}</ElTag>
        </template>
      </ElTableColumn>

      <ElTableColumn prop="status" :label="__('Status', 'giant-checkout-offers-for-woocommerce')" width="120" align="center">
        <template #default="{ row }">
          <ElTag v-if="row.status === 'draft'" type="warning" effect="light" round>{{ __('Draft', 'giant-checkout-offers-for-woocommerce') }}</ElTag>
          <ElSwitch
            v-else
            :model-value="row.status === 'active'"
            @change="handleStatusChange(row)"
            style="--el-switch-on-color: #10b981"
          />
        </template>
      </ElTableColumn>

      <ElTableColumn :label="__('Actions', 'giant-checkout-offers-for-woocommerce')" width="120" align="center">
        <template #default="{ row }">
          <div class="tw-flex tw-items-center tw-justify-center tw-gap-1">
            <ElButton
              type="primary"
              :icon="Edit"
              circle
              size="small"
              @click="handleEdit(row)"
            />
            <ElPopconfirm
              :title="__('Are you sure to delete this bump?', 'giant-checkout-offers-for-woocommerce')"
              :confirm-button-text="__('Delete', 'giant-checkout-offers-for-woocommerce')"
              :cancel-button-text="__('Cancel', 'giant-checkout-offers-for-woocommerce')"
              @confirm="handleDelete(row)"
            >
              <template #reference>
                <ElButton
                  type="danger"
                  :icon="Delete"
                  circle
                  size="small"
                />
              </template>
            </ElPopconfirm>
          </div>
        </template>
      </ElTableColumn>
    </ElTable>

    <!-- Empty State -->
    <div v-else-if="!loading" class="tw-py-20 tw-text-center">
      <ElEmpty :description="__('No order bumps yet', 'giant-checkout-offers-for-woocommerce')">
        <template #image>
          <svg class="tw-w-24 tw-h-24 tw-mx-auto tw-text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z" />
          </svg>
        </template>
        <ElButton type="primary" size="large" @click="openCreateModal">
          {{ __('Create Your First Bump', 'giant-checkout-offers-for-woocommerce') }}
        </ElButton>
      </ElEmpty>
    </div>
  </div>

  <!-- Bump Modal -->
  <BumpModal
    v-model:visible="showModal"
    :edit-data="editingBump"
    @save="handleSave"
  />
</template>

<style scoped>
:deep(.inactive-row) {
  opacity: 0.6;
}

:deep(.el-table__header th) {
  font-weight: 600 !important;
}
</style>
