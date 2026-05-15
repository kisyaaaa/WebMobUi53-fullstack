<script setup>
import { usePollStore } from '@/stores/usePollStore';

defineEmits(['new', 'edit']);
const { polls, deletePoll } = usePollStore();

async function onDelete(id) {
  if (confirm('Supprimer ce sondage ?')) {
    await deletePoll(id);
  }
}
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
        class="bg-white border rounded p-4 flex items-center justify-between"
      >
        <div>
          <p class="font-semibold">{{ poll.question }}</p>
          <p class="text-sm text-gray-500">
            Statut : <span class="font-medium">{{ poll.status }}</span>
          </p>
        </div>
        <div class="flex gap-2">
          <button
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
      </li>
    </ul>
  </div>
</template>