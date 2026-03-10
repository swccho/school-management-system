<template>
  <PageContainer title="Fees" description="View your fee summary and payment history.">
    <LoadingSkeleton v-if="loading" variant="cards" />
    <div v-else-if="error" class="rounded-lg bg-red-50 p-4 text-sm text-red-700 dark:bg-red-900/20 dark:text-red-400">
      {{ error }}
    </div>
    <EmptyState
      v-else-if="!hasFees"
      title="No fee information"
      description="Fee module may not be configured for your school, or no fees have been added yet."
    />
    <div v-else class="space-y-6">
      <div class="grid gap-4 sm:grid-cols-3">
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
          <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Total fee</p>
          <p class="mt-1 text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ formatMoney(summary.total_amount) }}</p>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
          <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Paid</p>
          <p class="mt-1 text-lg font-semibold text-green-700 dark:text-green-400">{{ formatMoney(summary.paid_amount) }}</p>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
          <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Due</p>
          <p class="mt-1 text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ formatMoney(summary.due_amount) }}</p>
          <StatusBadge v-if="summary.status" :status="summary.status" type="fee" class="mt-1" />
        </div>
      </div>

      <div class="rounded-xl border border-zinc-200 bg-white overflow-hidden dark:border-zinc-800 dark:bg-zinc-900">
        <div class="border-b border-zinc-200 px-4 py-3 dark:border-zinc-800">
          <h2 class="font-semibold text-zinc-900 dark:text-zinc-100">Fee items</h2>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full min-w-[400px] text-sm">
            <thead>
              <tr class="border-b border-zinc-200 dark:border-zinc-800">
                <th class="px-4 py-3 text-left font-medium text-zinc-700 dark:text-zinc-300">Category</th>
                <th class="px-4 py-3 text-left font-medium text-zinc-700 dark:text-zinc-300">Period</th>
                <th class="px-4 py-3 text-right font-medium text-zinc-700 dark:text-zinc-300">Total</th>
                <th class="px-4 py-3 text-right font-medium text-zinc-700 dark:text-zinc-300">Paid</th>
                <th class="px-4 py-3 text-right font-medium text-zinc-700 dark:text-zinc-300">Due</th>
                <th class="px-4 py-3 text-left font-medium text-zinc-700 dark:text-zinc-300">Due date</th>
                <th class="px-4 py-3 text-left font-medium text-zinc-700 dark:text-zinc-300">Status</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="f in fees"
                :key="f.id"
                class="border-b border-zinc-100 dark:border-zinc-800/50 last:border-0"
              >
                <td class="px-4 py-3 font-medium text-zinc-900 dark:text-zinc-100">{{ f.fee_category }}</td>
                <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400">{{ f.period_label }}</td>
                <td class="px-4 py-3 text-right text-zinc-900 dark:text-zinc-100">{{ formatMoney(f.total_amount) }}</td>
                <td class="px-4 py-3 text-right text-green-600 dark:text-green-400">{{ formatMoney(f.paid_amount) }}</td>
                <td class="px-4 py-3 text-right text-zinc-900 dark:text-zinc-100">{{ formatMoney(f.due_amount) }}</td>
                <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400">{{ f.due_date ?? '—' }}</td>
                <td class="px-4 py-3">
                  <StatusBadge :status="f.status" type="fee" />
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div v-if="paymentHistory.length" class="rounded-xl border border-zinc-200 bg-white overflow-hidden dark:border-zinc-800 dark:bg-zinc-900">
        <div class="border-b border-zinc-200 px-4 py-3 dark:border-zinc-800">
          <h2 class="font-semibold text-zinc-900 dark:text-zinc-100">Payment history</h2>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full min-w-[300px] text-sm">
            <thead>
              <tr class="border-b border-zinc-200 dark:border-zinc-800">
                <th class="px-4 py-3 text-left font-medium text-zinc-700 dark:text-zinc-300">Category / Period</th>
                <th class="px-4 py-3 text-right font-medium text-zinc-700 dark:text-zinc-300">Amount</th>
                <th class="px-4 py-3 text-left font-medium text-zinc-700 dark:text-zinc-300">Date</th>
                <th class="px-4 py-3 text-left font-medium text-zinc-700 dark:text-zinc-300">Reference</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="(p, i) in paymentHistory"
                :key="i"
                class="border-b border-zinc-100 dark:border-zinc-800/50 last:border-0"
              >
                <td class="px-4 py-3 text-zinc-900 dark:text-zinc-100">{{ p.fee_category }} · {{ p.period_label }}</td>
                <td class="px-4 py-3 text-right font-medium text-green-600 dark:text-green-400">{{ formatMoney(p.amount) }}</td>
                <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400">{{ formatDate(p.paid_at) }}</td>
                <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400">{{ p.reference ?? '—' }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </PageContainer>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import PageContainer from '../components/PageContainer.vue';
import EmptyState from '../components/EmptyState.vue';
import LoadingSkeleton from '../components/LoadingSkeleton.vue';
import StatusBadge from '../components/StatusBadge.vue';
import { getFees } from '../services/feeService.js';

const loading = ref(true);
const error = ref(null);
const summary = ref({ total_amount: 0, paid_amount: 0, due_amount: 0, status: null });
const fees = ref([]);
const paymentHistory = ref([]);

const hasFees = computed(() => fees.value.length > 0);

function formatMoney(n) {
  if (n == null) return '—';
  return new Intl.NumberFormat(undefined, { style: 'currency', currency: 'BDT', minimumFractionDigits: 2 }).format(n);
}

function formatDate(iso) {
  if (!iso) return '—';
  try {
    return new Date(iso).toLocaleDateString(undefined, { dateStyle: 'medium' });
  } catch {
    return iso;
  }
}

onMounted(async () => {
  try {
    loading.value = true;
    error.value = null;
    const data = await getFees();
    summary.value = data.summary ?? summary.value;
    fees.value = data.fees ?? [];
    paymentHistory.value = data.payment_history ?? [];
  } catch (e) {
    error.value = e.response?.data?.message ?? e.message ?? 'Failed to load fees.';
  } finally {
    loading.value = false;
  }
});
</script>
