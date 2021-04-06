import Vue from 'vue';
import Vuex, {Store} from 'vuex';
import createPersistentState from 'vuex-persistedstate';

import blocking from './modules/blocking.module';
import features from './modules/features.module';
import installation from './modules/installation.module';
import sidebar from './modules/sidebar.module';
import spinner from './modules/spinner.module';
import user from './modules/user.module';
import webSocketClient from './modules/webSocketClient.module';

Vue.use(Vuex);

const store: Store<any> = new Vuex.Store({
	modules: {
		blocking,
		features,
		installation,
		sidebar,
		spinner,
		user,
		webSocketClient,
	},
	plugins: [createPersistentState({
		paths: [
			'features',
			'user',
		]
	})]
});

export default store;
