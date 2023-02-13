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

import axios, {AxiosError, AxiosResponse} from 'axios';
import Vue from 'vue';

import store from '@/store';
import router from '@/router';

import UrlBuilder from '@/helpers/urlBuilder';
import i18n from '@/plugins/i18n';

const urlBuilder: UrlBuilder = new UrlBuilder();

axios.defaults.baseURL = urlBuilder.getRestApiUrl();
axios.defaults.timeout = 30000;

axios.interceptors.request.use(async config => {
	if (!store.getters['user/get']) {
		return config;
	}
	const expiration: number = (store.getters['user/getExpiration'] * 1000);
	const now = new Date().getTime();
	if (expiration > now) {
		return config;
	}
	const controller = new AbortController();
	controller.abort();
	store.dispatch('user/signOut')
		.then(async () => {
			await router.push({path: '/sign/in', query: {redirect: router.currentRoute.path}});
			Vue.$toast.warning(
				i18n.t('core.sign.out.expired').toString(),
			);
		});
	return {
		...config,
		signal: controller.signal,
	};
});

axios.interceptors.response.use(
	(response: AxiosResponse) => {
		return response;
	},
	async (error: AxiosError) => {
		if (error.response === undefined) {
			// TODO: Add Network error toaster notification
			return Promise.reject(error);
		}
		if (error.response.status === 401) {
			if (!store.getters['user/get']) {
				return;
			}
			await store.dispatch('user/signOut')
				.then(async () => {
					await router.push({path: '/sign/in', query: {redirect: router.currentRoute.path}});
					Vue.$toast.warning(
						i18n.t('core.sign.out.expired').toString(),
					);
				});
		}
		return Promise.reject(error);
	}
);
