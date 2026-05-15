<script setup>
import { ref, onMounted } from 'vue';
import { useFetchApi } from '@/composables/useFetchApi';

const props = defineProps({
  poll: { type: Object, required: true },
  token: { type: String, required: true },
});

const emit = defineEmits(['voted']);

const { fetchApi } = useFetchApi();
const selectedSingle = ref(null);
const selectedMultiple = ref([]);
const submitting = ref(false);
const voteError = ref(null);
const alreadyVoted = ref(false);

onMounted(async () => {
  try {
    const myVote = await fetchApi({
      url: `polls/by-token/${props.token}/votes/me`,
    });
    if (myVote.option_ids.length > 0) {
      alreadyVoted.value = true;
      if (props.poll.allow_multiple_choices) {
        selectedMultiple.value = myVote.option_ids;
      } else {
        selectedSingle.value = myVote.option_ids[0];
      }
    }
  } catch (err) {
    // 401 ou autre → on ignore, l'user verra le form vide
  }
});

async function submit() {
  const option_ids = props.poll.allow_multiple_choices
    ? selectedMultiple.value
    : selectedSingle.value ? [selectedSingle.value] : [];

  if (option_ids.length === 0) {
    voteError.value = 'Veuillez sélectionner au moins une option.';
    return;
  }

  submitting.value = true;
  voteError.value = null;
  try {
    await fetchApi({
      url: `polls/by-token/${props.token}/votes`,
      method: 'POST',
      data: { option_ids },
    });
    alreadyVoted.value = true;
    emit('voted');
  } catch (err) {
    voteError.value = err.data?.message ?? 'Erreur lors du vote';
  } finally {
    submitting.value = false;
  }
}

const canSubmit = () => !alreadyVoted.value || props.poll.allow_vote_change;
</script>

<template>
  <form @submit.prevent="submit" class="bg-white border rounded p-4">
    <p v-if="alreadyVoted && !poll.allow_vote_change" class="text-green-700 mb-3">
      ✓ Vous avez voté.
    </p>

    <fieldset :disabled="alreadyVoted && !poll.allow_vote_change" class="space-y-2 mb-4">
      <template v-if="poll.allow_multiple_choices">
        <label
          v-for="option in poll.options"
          :key="option.id"
          class="flex items-center gap-2 cursor-pointer"
        >
          <input
            v-model="selectedMultiple"
            type="checkbox"
            :value="option.id"
          />
          <span>{{ option.label }}</span>
        </label>
      </template>
      <template v-else>
        <label
          v-for="option in poll.options"
          :key="option.id"
          class="flex items-center gap-2 cursor-pointer"
        >
          <input
            v-model="selectedSingle"
            type="radio"
            :value="option.id"
            name="poll-option"
          />
          <span>{{ option.label }}</span>
        </label>
      </template>
    </fieldset>

    <p v-if="voteError" class="text-red-600 text-sm mb-3">{{ voteError }}</p>

    <button
      v-if="canSubmit()"
      type="submit"
      :disabled="submitting"
      class="bg-teal-600 text-white px-4 py-2 rounded hover:bg-teal-700 disabled:opacity-50"
    >
      {{ submitting ? 'Envoi...' : (alreadyVoted ? 'Modifier mon vote' : 'Voter') }}
    </button>
  </form>
</template>