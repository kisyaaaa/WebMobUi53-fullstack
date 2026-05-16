<script setup>
import { ref, onMounted } from 'vue';
import { useFetchApi } from '@/composables/useFetchApi';
import PollVoteForm from './components/PollVoteForm.vue';
import PollResultsChart from './components/PollResultsChart.vue';

const props = defineProps({
  token: { type: String, required: true },
  isAuthenticated: { type: Boolean, default: false },
});

const { fetchApi } = useFetchApi();
const poll = ref(null);
const results = ref(null);
const error = ref(null);
const loading = ref(true);

async function loadPoll() {
  try {
    poll.value = await fetchApi({ url: `polls/by-token/${props.token}` });
  } catch (err) {
    error.value = err.data?.message ?? 'Sondage introuvable';
  } finally {
    loading.value = false;
  }
}

async function loadResults() {
  try {
    results.value = await fetchApi({
      url: `polls/by-token/${props.token}/results`,
    });
  } catch (err) {
    results.value = null;
  }
}

onMounted(async () => {
  await loadPoll();
  if (poll.value) await loadResults();
});
</script>

<template>
  <main class="max-w-2xl mx-auto p-4 space-y-6">
    <p v-if="loading" class="text-gray-500">Chargement...</p>

    <div
      v-else-if="error"
      class="bg-red-100 border border-red-300 rounded p-4 text-red-700"
    >
      {{ error }}
    </div>

    <div v-else class="space-y-6">
      <div>
        <h1 class="text-2xl font-bold mb-2">{{ poll.question }}</h1>
        <p v-if="poll.title" class="text-sm text-gray-500">{{ poll.title }}</p>
      </div>

      <PollVoteForm
        v-if="poll.status === 'active' && isAuthenticated"
        :poll="poll"
        :token="token"
        @voted="loadResults"
      />

      <div
        v-else-if="poll.status === 'active' && !isAuthenticated"
        class="bg-yellow-100 border border-yellow-300 rounded p-4 text-yellow-800"
      >
        Connectez-vous pour voter.
      </div>

      <PollResultsChart v-if="results" :results="results" />
    </div>
  </main>
</template>