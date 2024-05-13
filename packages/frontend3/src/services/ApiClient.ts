/**
 * Copyright 2017-2024 IQRF Tech s.r.o.
 * Copyright 2019-2024 MICRORISC s.r.o.
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

import { Client } from '@iqrf/iqrf-gateway-webapp-client';
import { type AxiosError, type AxiosResponse } from 'axios';
import { storeToRefs } from 'pinia';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import UrlBuilder from '@/helpers/urlBuilder';
import router from '@/router';
import { useUserStore } from '@/store/user';

/**
 * Creates IQRF Gateway Webapp API client instance
 * @return {Client} IQRF Gateway Webapp API client instance
 */
export const useApiClient = (): Client => {
	const client: Client = new Client({
		config: {
			baseURL: new UrlBuilder().getRestApiUrl(),
		},
	});
	/// Set authorization JWT token
	const userStore = useUserStore();
	const { getToken: token } = storeToRefs(userStore);
	client.setToken(token.value);
	client.useResponseInterceptor(
		(response: AxiosResponse): AxiosResponse => response,
		(error: AxiosError) => {
			console.error(error);
			// Handle network error
			if (error.response === undefined) {
				return Promise.reject(error);
			}
			// Handle HTTP Error 401 Unauthorized response
			if (error.response.status === 401) {
				const userStore = useUserStore();
				userStore.signOut();
				// Prevent duplicate redirect to sign in page
				if (router.currentRoute.value.name !== 'signIn') {
					router.push({ name: 'signIn', query: { redirect: router.currentRoute.value.path } });
				}
				const i18n = useI18n();
				toast.warning(i18n.t('auth.sign.out.expired').toString());
			}
			// Handle other HTTP errors
			return Promise.reject(error);
		});
	return client;
};
