<script setup>
import { ref } from 'vue';
import PollList from './components/PollList.vue';
import PollEditor from './components/PollEditor.vue';
import { usePollStore } from '@/stores/usePollStore';

const props = defineProps({
  polls: { type: Array, default: () => [] },
});

const { setPolls } = usePollStore();
setPolls(props.polls);

const view = ref('list');         // 'list' | 'editor'
const editingPollId = ref(null);  // null = création, sinon id du poll à éditer

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