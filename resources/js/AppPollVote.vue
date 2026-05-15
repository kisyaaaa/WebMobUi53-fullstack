<script setup>
import { ref, onMounted } from 'vue';
import { useFetchApi } from '@/composables/useFetchApi';
import PollVoteForm from './components/PollVoteForm.vue';

const props = defineProps({
  token: { type: String, required: true },
  isAuthenticated: { type: Boolean, default: false },
});

const { fetchApi } = useFetchApi();
const poll = ref(null);
const error = ref(null);
const loading = ref(true);

onMounted(async () => {
  try {
    poll.value = await fetchApi({ url: `polls/by-token/${props.token}` });
  } catch (err) {
    error.value = err.data?.message ?? 'Sondage introuvable';
  } finally {
    loading.value = false;
  }
});
</script>

<template>
  <main class="max-w-2xl mx-auto p-4">
    <p v-if="loading" class="text-gray-500">Chargement...</p>

    <div
      v-else-if="error"
      class="bg-red-100 border border-red-300 rounded p-4 text-red-700"
    >
      {{ error }}
    </div>

    <div v-else>
      <h1 class="text-2xl font-bold mb-2">{{ poll.question }}</h1>
      <p v-if="poll.title" class="text-sm text-gray-500 mb-4">{{ poll.title }}</p>

      <PollVoteForm
        v-if="poll.status === 'active' && isAuthenticated"
        :poll="poll"
        :token="token"
      />

      <div
        v-else-if="poll.status === 'active' && !isAuthenticated"
        class="bg-yellow-100 border border-yellow-300 rounded p-4 text-yellow-800"
      >
        Connectez-vous pour voter.
      </div>
    </div>
  </main>
</template>