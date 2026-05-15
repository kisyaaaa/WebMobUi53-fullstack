<script setup>
import { ref } from 'vue';
import { usePollStore } from '@/stores/usePollStore';

defineEmits(['new', 'edit']);
const { polls, deletePoll, startPoll } = usePollStore();

const copiedId = ref(null);

async function onDelete(id) {
  if (confirm('Supprimer ce sondage ?')) {
    await deletePoll(id);
  }
}

async function onStart(id) {
  if (confirm('Lancer ce sondage ? Les options ne pourront plus être modifiées.')) {
    await startPoll(id);
  }
}

async function onCopy(poll) {
  await navigator.clipboard.writeText(poll.share_url);
  copiedId.value = poll.id;
  setTimeout(() => {
    if (copiedId.value === poll.id) copiedId.value = null;
  }, 2000);
}

const statusLabel = {
  draft: 'Brouillon',
  active: 'En cours',
  ended: 'Terminé',
};

const statusColor = {
  draft: 'bg-gray-200 text-gray-700',
  active: 'bg-green-200 text-green-800',
  ended: 'bg-red-200 text-red-800',
};
</script>

<template>
  <div>
    <header class="flex items-center justify-between mb-4">
      <h1 class="text-2xl font-bold">Mes sondages</h1>
      <button
        @click="$emit('new')"
        class="bg-teal-600 text-white px-4 py-2 rounded hover:bg-teal-700"
      >
        + Nouveau
      </button>
    </header>

    <p v-if="polls.length === 0" class="text-gray-500">Aucun sondage.</p>

    <ul v-else class="space-y-3">
      <li
        v-for="poll in polls"
        :key="poll.id"
        class="bg-white border rounded p-4"
      >
        <div class="flex items-center justify-between mb-2">
          <div>
            <p class="font-semibold">{{ poll.question }}</p>
            <span
              :class="statusColor[poll.status]"
              class="inline-block text-xs px-2 py-0.5 rounded mt-1"
            >
              {{ statusLabel[poll.status] }}
            </span>
          </div>

          <div class="flex gap-2">
            <button
              v-if="poll.status === 'draft'"
              @click="onStart(poll.id)"
              class="bg-teal-600 text-white px-3 py-1 rounded hover:bg-teal-700"
            >
              Lancer
            </button>
            <button
              v-if="poll.status !== 'ended'"
              @click="$emit('edit', poll.id)"
              class="bg-gray-200 px-3 py-1 rounded hover:bg-gray-300"
            >
              Modifier
            </button>
            <button
              @click="onDelete(poll.id)"
              class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600"
            >
              Supprimer
            </button>
          </div>
        </div>

        <div
          v-if="poll.status !== 'draft'"
          class="flex items-center gap-2 mt-2 text-sm"
        >
          <input
            type="text"
            :value="poll.share_url"
            readonly
            class="flex-1 border rounded px-2 py-1 bg-gray-50 text-gray-600"
          />
          <button
            @click="onCopy(poll)"
            class="bg-gray-200 px-3 py-1 rounded hover:bg-gray-300"
          >
            {{ copiedId === poll.id ? 'Copié ✓' : 'Copier' }}
          </button>
        </div>
      </li>
    </ul>
  </div>
</template>