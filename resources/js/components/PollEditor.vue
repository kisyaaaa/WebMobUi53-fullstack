<script setup>
defineProps({
  pollId: { type: Number, default: null },
});
defineEmits(['done']);
</script>

<template>
  <div>
    <h1 class="text-2xl font-bold mb-4">
      {{ pollId ? `Modifier le sondage #${pollId}` : 'Nouveau sondage' }}
    </h1>
    <p class="text-gray-500 mb-4">Éditeur (à compléter étape 2.5).</p>
    <button
      @click="$emit('done')"
      class="bg-gray-200 px-4 py-2 rounded hover:bg-gray-300"
    >
      ← Retour
    </button>
  </div>
</template>