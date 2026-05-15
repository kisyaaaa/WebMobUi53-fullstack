<script setup>
import { ref, onMounted } from 'vue';
import { usePollStore } from '@/stores/usePollStore';

const props = defineProps({
  pollId: { type: Number, default: null },
});
const emit = defineEmits(['done']);

const {
  currentPoll,
  fetchOne,
  createPoll,
  updatePoll,
  addOption,
  updateOption,
  removeOption,
} = usePollStore();

const localPollId = ref(props.pollId);

const form = ref({
  title: '',
  question: '',
  allow_multiple_choices: false,
  allow_vote_change: false,
  results_public: false,
  duration: null,
});

const newOptionLabel = ref('');
const editingOptionId = ref(null);
const editingOptionLabel = ref('');
const saving = ref(false);

const durationOptions = [
  { value: null, label: 'Pas de limite' },
  { value: 3600, label: '1 heure' },
  { value: 21600, label: '6 heures' },
  { value: 86400, label: '1 jour' },
  { value: 604800, label: '7 jours' },
];

onMounted(async () => {
  if (props.pollId) {
    await fetchOne(props.pollId);
    if (currentPoll.value) {
      form.value = {
        title: currentPoll.value.title ?? '',
        question: currentPoll.value.question,
        allow_multiple_choices: currentPoll.value.allow_multiple_choices,
        allow_vote_change: currentPoll.value.allow_vote_change,
        results_public: currentPoll.value.results_public,
        duration: currentPoll.value.duration,
      };
    }
  }
});

async function save() {
  saving.value = true;
  try {
    if (localPollId.value) {
      await updatePoll(localPollId.value, form.value);
    } else {
      const poll = await createPoll(form.value);
      localPollId.value = poll.id;
    }
  } finally {
    saving.value = false;
  }
}

async function onAddOption() {
  const label = newOptionLabel.value.trim();
  if (!label || !localPollId.value) return;
  await addOption(localPollId.value, label);
  newOptionLabel.value = '';
}

function startEditOption(option) {
  editingOptionId.value = option.id;
  editingOptionLabel.value = option.label;
}

async function saveOptionEdit() {
  const label = editingOptionLabel.value.trim();
  if (!label) return;
  await updateOption(localPollId.value, editingOptionId.value, label);
  editingOptionId.value = null;
}

async function onRemoveOption(optionId) {
  if (confirm('Supprimer cette option ?')) {
    await removeOption(localPollId.value, optionId);
  }
}
</script>

<template>
  <div>
    <header class="flex items-center justify-between mb-4">
      <h1 class="text-2xl font-bold">
        {{ localPollId ? 'Modifier le sondage' : 'Nouveau sondage' }}
      </h1>
      <button
        @click="$emit('done')"
        class="bg-gray-200 px-4 py-2 rounded hover:bg-gray-300"
      >
        ← Retour
      </button>
    </header>

    <form @submit.prevent="save" class="space-y-4 bg-white border rounded p-4">
      <div>
        <label class="block text-sm font-medium mb-1">Titre (optionnel)</label>
        <input
          v-model="form.title"
          type="text"
          maxlength="255"
          class="w-full border rounded px-3 py-2"
        />
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Question *</label>
        <input
          v-model="form.question"
          type="text"
          maxlength="500"
          required
          class="w-full border rounded px-3 py-2"
        />
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Durée</label>
        <select v-model="form.duration" class="w-full border rounded px-3 py-2">
          <option
            v-for="opt in durationOptions"
            :key="opt.label"
            :value="opt.value"
          >
            {{ opt.label }}
          </option>
        </select>
      </div>

      <fieldset class="space-y-2">
        <legend class="text-sm font-medium mb-1">Paramètres</legend>
        <label class="flex items-center gap-2">
          <input v-model="form.allow_multiple_choices" type="checkbox" />
          <span>Autoriser plusieurs choix</span>
        </label>
        <label class="flex items-center gap-2">
          <input v-model="form.allow_vote_change" type="checkbox" />
          <span>Autoriser la modification du vote</span>
        </label>
        <label class="flex items-center gap-2">
          <input v-model="form.results_public" type="checkbox" />
          <span>Résultats publics</span>
        </label>
      </fieldset>

      <button
        type="submit"
        :disabled="saving"
        class="bg-teal-600 text-white px-4 py-2 rounded hover:bg-teal-700 disabled:opacity-50"
      >
        {{ saving ? 'Enregistrement...' : (localPollId ? 'Sauvegarder' : 'Créer') }}
      </button>
    </form>

    <section v-if="localPollId" class="mt-6 bg-white border rounded p-4">
      <h2 class="text-lg font-bold mb-3">Options</h2>

      <ul class="space-y-2 mb-3">
        <li
          v-for="option in currentPoll?.options ?? []"
          :key="option.id"
          class="flex items-center gap-2"
        >
          <template v-if="editingOptionId === option.id">
            <input
              v-model="editingOptionLabel"
              type="text"
              class="flex-1 border rounded px-2 py-1"
              @keyup.enter="saveOptionEdit"
            />
            <button
              @click="saveOptionEdit"
              class="bg-teal-600 text-white px-3 py-1 rounded hover:bg-teal-700"
            >
              OK
            </button>
            <button
              @click="editingOptionId = null"
              class="bg-gray-200 px-3 py-1 rounded hover:bg-gray-300"
            >
              Annuler
            </button>
          </template>
          <template v-else>
            <span class="flex-1">{{ option.label }}</span>
            <button
              v-if="currentPoll?.status === 'draft'"
              @click="startEditOption(option)"
              class="bg-gray-200 px-3 py-1 rounded hover:bg-gray-300"
            >
              Modifier
            </button>
            <button
              v-if="currentPoll?.status === 'draft'"
              @click="onRemoveOption(option.id)"
              class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600"
            >
              Supprimer
            </button>
          </template>
        </li>
      </ul>

      <div v-if="currentPoll?.status === 'draft'" class="flex gap-2">
        <input
          v-model="newOptionLabel"
          type="text"
          placeholder="Nouvelle option"
          maxlength="255"
          class="flex-1 border rounded px-3 py-2"
          @keyup.enter="onAddOption"
        />
        <button
          @click="onAddOption"
          class="bg-teal-600 text-white px-4 py-2 rounded hover:bg-teal-700"
        >
          Ajouter
        </button>
      </div>

      <p v-else class="text-sm text-gray-500 italic">
        Les options ne peuvent plus être modifiées (sondage lancé).
      </p>
    </section>
  </div>
</template>