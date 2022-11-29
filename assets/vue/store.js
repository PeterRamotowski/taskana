import { ref } from 'vue';

const userStore = ref(
  JSON.parse(document.querySelector('#appUser').text.trim()),
);

export function useStore() {
  return {
    userStore
  }
}