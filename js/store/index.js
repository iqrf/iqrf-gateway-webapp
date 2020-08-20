import Vue from 'vue';
import Vuex from 'vuex';
import createPersistentState from 'vuex-persistedstate';

import daemonStatus from './modules/daemonStatus.module';

Vue.use(Vuex);

const store = new Vuex.Store({
	modules: {
		daemonStatus
	},
	plugins: [createPersistentState()]
});

export default store;
