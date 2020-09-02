import Vue from 'vue';
import Vuex from 'vuex';
import createPersistentState from 'vuex-persistedstate';

import daemonStatus from './modules/daemonStatus.module';
import sidebar from './modules/sidebar.module';
import user from './modules/user.module';
import webSocketClient from './modules/webSocketClient.module';

Vue.use(Vuex);

const store = new Vuex.Store({
	modules: {
		daemonStatus,
		sidebar,
		user,
		webSocketClient,
	},
	plugins: [createPersistentState()]
});

export default store;
