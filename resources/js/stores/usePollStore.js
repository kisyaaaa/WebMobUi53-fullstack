import { ref } from 'vue';
import { useFetchApi } from '@/composables/useFetchApi';

const polls = ref([]);
const currentPoll = ref(null);
const error = ref(null);

export function usePollStore() {
  const { fetchApi } = useFetchApi();

  function setPolls(data) {
    polls.value = data;
  }

  async function fetchAll() {
    try {
      polls.value = await fetchApi({ url: 'polls' });
    } catch (err) {
      error.value = err.data?.message ?? 'Erreur de chargement';
    }
  }

  async function fetchOne(id) {
    try {
      currentPoll.value = await fetchApi({ url: `polls/${id}` });
    } catch (err) {
      error.value = err.data?.message ?? 'Erreur de chargement';
    }
  }

  async function createPoll(data) {
    try {
      const poll = await fetchApi({ url: 'polls', method: 'POST', data });
      polls.value = [poll, ...polls.value];
      return poll;
    } catch (err) {
      error.value = err.data?.message ?? 'Erreur de création';
      throw err;
    }
  }

  async function updatePoll(id, data) {
    try {
      const updated = await fetchApi({ url: `polls/${id}`, method: 'PUT', data });
      polls.value = polls.value.map(p => p.id === id ? updated : p);
      if (currentPoll.value?.id === id) currentPoll.value = updated;
      return updated;
    } catch (err) {
      error.value = err.data?.message ?? 'Erreur de mise à jour';
      throw err;
    }
  }

  async function deletePoll(id) {
    try {
      await fetchApi({ url: `polls/${id}`, method: 'DELETE' });
      polls.value = polls.value.filter(p => p.id !== id);
    } catch (err) {
      error.value = err.data?.message ?? 'Erreur de suppression';
    }
  }

  async function startPoll(id) {
    try {
      const started = await fetchApi({ url: `polls/${id}/start`, method: 'POST' });
      polls.value = polls.value.map(p => p.id === id ? started : p);
      if (currentPoll.value?.id === id) currentPoll.value = started;
      return started;
    } catch (err) {
      error.value = err.data?.message ?? 'Erreur de lancement';
      throw err;
    }
  }

  async function addOption(pollId, label) {
    try {
      const option = await fetchApi({
        url: `polls/${pollId}/options`,
        method: 'POST',
        data: { label },
      });
      if (currentPoll.value?.id === pollId) {
        currentPoll.value.options = [...(currentPoll.value.options ?? []), option];
      }
      return option;
    } catch (err) {
      error.value = err.data?.message ?? "Erreur d'ajout d'option";
      throw err;
    }
  }

  async function updateOption(pollId, optionId, label) {
    try {
      const updated = await fetchApi({
        url: `polls/${pollId}/options/${optionId}`,
        method: 'PUT',
        data: { label },
      });
      if (currentPoll.value?.id === pollId) {
        currentPoll.value.options = currentPoll.value.options.map(
          o => o.id === optionId ? updated : o
        );
      }
      return updated;
    } catch (err) {
      error.value = err.data?.message ?? "Erreur de modification d'option";
      throw err;
    }
  }

  async function removeOption(pollId, optionId) {
    try {
      await fetchApi({
        url: `polls/${pollId}/options/${optionId}`,
        method: 'DELETE',
      });
      if (currentPoll.value?.id === pollId) {
        currentPoll.value.options = currentPoll.value.options.filter(o => o.id !== optionId);
      }
    } catch (err) {
      error.value = err.data?.message ?? "Erreur de suppression d'option";
    }
  }

  return {
    polls, currentPoll, error,
    setPolls,
    fetchAll, fetchOne,
    createPoll, updatePoll, deletePoll, startPoll,
    addOption, updateOption, removeOption,
  };
}