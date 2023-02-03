/**
 * Copyright 2017-2023 IQRF Tech s.r.o.
 * Copyright 2019-2023 MICRORISC s.r.o.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
import Vue from 'vue';
import Vuex, {Store} from 'vuex';
import createPersistentState from 'vuex-persistedstate';

import blocking from './modules/blocking.module';
import features from './modules/features.module';
import installation from './modules/installation.module';
import sidebar from './modules/sidebar.module';
import spinner from './modules/spinner.module';
import user from './modules/user.module';
import daemonClient from './modules/daemonClient.module';
import monitorClient from './modules/monitorClient.module';
import repository from './modules/repository.module';
import webSocket from './modules/webSocket.module';

Vue.use(Vuex);

const store: Store<any> = new Vuex.Store({
	modules: {
		blocking,
		features,
		installation,
		sidebar,
		spinner,
		user,
		webSocket,
		daemonClient,
		monitorClient,
		repository,
	},
	plugins: [createPersistentState({
		paths: [
			'features',
			'repository',
			'user',
		]
	})]
});

export default store;
