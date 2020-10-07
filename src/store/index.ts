import Vue from 'vue';
import Vuex, {Store} from 'vuex';
import createPersistentState from 'vuex-persistedstate';

import features from './modules/features.module';
import sidebar from './modules/sidebar.module';
import spinner from './modules/spinner.module';
import user from './modules/user.module';
import webSocketClient from './modules/webSocketClient.module';

Vue.use(Vuex);

const store: Store<any> = new Vuex.Store({
	modules: {
		features,
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
