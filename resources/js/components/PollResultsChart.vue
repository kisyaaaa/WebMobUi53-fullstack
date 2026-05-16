<script setup>
import { computed } from 'vue';

const props = defineProps({
  results: { type: Object, required: true },
});

const total = computed(() => props.results.total_votes || 0);

function percentage(count) {
  if (total.value === 0) return 0;
  return Math.round((count / total.value) * 100);
}
</script>

<template>
  <div class="bg-white border rounded p-4">
    <h2 class="text-lg font-bold mb-3">Résultats</h2>

    <p v-if="total === 0" class="text-gray-500 italic">
      Aucun vote pour le moment.
    </p>

    <ul v-else class="space-y-3">
      <li v-for="option in results.options" :key="option.id">
        <div class="flex justify-between text-sm mb-1">
          <span>{{ option.label }}</span>
          <span class="text-gray-600">
            {{ option.votes_count }} ({{ percentage(option.votes_count) }}%)
          </span>
        </div>
        <div class="w-full h-4 bg-gray-200 rounded overflow-hidden">
          <div
            class="h-full bg-teal-600 transition-all"
            :style="{ width: percentage(option.votes_count) + '%' }"
          ></div>
        </div>
      </li>
    </ul>

    <p class="text-xs text-gray-500 mt-3">
      Total : {{ total }} vote{{ total > 1 ? 's' : '' }}
    </p>
  </div>
</template>