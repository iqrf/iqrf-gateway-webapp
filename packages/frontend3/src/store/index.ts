import { createPinia } from 'pinia';
import piniaPluginPersistedstate from 'pinia-plugin-persistedstate';

// Creates a new Pinia instance
const pinia = createPinia();
pinia.use(piniaPluginPersistedstate);

export default pinia;
