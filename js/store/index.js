import Vue from 'vue';
import Vuex from 'vuex';
import createPersistentState from 'vuex-persistedstate';

import daemonStatus from './modules/daemonStatus.module';
import sidebar from './modules/sidebar.module';
import spinner from './modules/spinner.module';
import user from './modules/user.module';
import webSocketClient from './modules/webSocketClient.module';

Vue.use(Vuex);

const store = new Vuex.Store({
	modules: {
		daemonStatus,
		sidebar,
		spinner,
		user,
		webSocketClient,
	},
	plugins: [createPersistentState()]
});

export default store;
