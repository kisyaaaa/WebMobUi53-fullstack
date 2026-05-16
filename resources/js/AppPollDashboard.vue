<script setup>
import { ref } from 'vue';
import PollList from './components/PollList.vue';
import PollEditor from './components/PollEditor.vue';
import { usePollStore } from '@/stores/usePollStore';

const props = defineProps({
  polls: { type: Array, default: () => [] },
});

const { setPolls, error, clearError } = usePollStore();
setPolls(props.polls);

const view = ref('list');
const editingPollId = ref(null);

function showList() {
  view.value = 'list';
  editingPollId.value = null;
}

function showEditor(id = null) {
  view.value = 'editor';
  editingPollId.value = id;
}
</script>

<template>
  <main class="max-w-4xl mx-auto p-4">
    <div
      v-if="error"
      class="bg-red-100 border border-red-300 rounded p-3 mb-4 text-red-700 flex items-center justify-between"
    >
      <span>{{ error }}</span>
      <button @click="clearError" class="text-red-700 font-bold text-xl leading-none ml-4">
        ×
      </button>
    </div>

    <PollList
      v-if="view === 'list'"
      @new="showEditor()"
      @edit="showEditor($event)"
    />
    <PollEditor
      v-else
      :poll-id="editingPollId"
      @done="showList()"
    />
  </main>
</template>