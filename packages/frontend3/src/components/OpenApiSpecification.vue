<!--
Copyright 2017-2024 IQRF Tech s.r.o.
Copyright 2019-2024 MICRORISC s.r.o.

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software,
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied
See the License for the specific language governing permissions and
limitations under the License.
-->

<template>
	<Card>
		<template #title>
			{{ $t('components.dev.openApi.title') }}
		</template>
		<div id='swagger' />
		<v-alert
			v-if='componentState == ComponentState.FetchFailed'
			variant='tonal'
			type='error'
		>
			{{ $t('components.dev.openApi.messages.fetchFailed') }}
		</v-alert>
	</Card>
</template>

<script setup lang='ts'>
import { storeToRefs } from 'pinia';
import {
	type SwaggerRequest,
	SwaggerUIBundle,
} from 'swagger-ui-dist';
import { onMounted, ref, type Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import Card from '@/components/layout/card/Card.vue';
import UrlBuilder from '@/helpers/urlBuilder';
import { useApiClient } from '@/services/ApiClient';
import { useUserStore } from '@/store/user';
import { ComponentState } from '@/types/ComponentState';

const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const i18n = useI18n();
const userStore = useUserStore();
const { getToken: token } = storeToRefs(userStore);
const service = useApiClient().getOpenApiService();
const urlBuilder: UrlBuilder = new UrlBuilder();

/**
 * Extended Swagger request interface
 */
interface Request extends SwaggerRequest {
	headers: {
		Authorization?: string;
	};
}

/**
 * Fetch OpenAPI specification
 */
function fetch(): void {
	componentState.value = ComponentState.Loading;
	service.fetchSpecification(urlBuilder.getRestApiUrl())
		.then((specification: object): object => {
			componentState.value = ComponentState.Ready;
			SwaggerUIBundle({
				spec: specification,
				dom_id: '#swagger',
				deepLinking: true,
				requestInterceptor: (swaggerRequest: SwaggerRequest): SwaggerRequest => {
					const request = swaggerRequest as Request;
					if (token.value && !request.headers.Authorization) {
						request.headers.Authorization = `Bearer ${token.value}`;
					}
					return request;
				},
			});
			return specification;
		})
		.catch(() => {
			componentState.value = ComponentState.FetchFailed;
			toast.error(i18n.t('components.dev.openApi.messages.fetchFailed').toString());
		});
}

onMounted(fetch);
</script>

<style lang='scss'>
@import url('swagger-ui/dist/swagger-ui.css');

.v-theme--dark {
	.swagger-ui {
		filter: invert(88%) hue-rotate(180deg);

		.highlight-code {
			filter: invert(100%) hue-rotate(180deg) contrast(150%);
		}
	}
}
</style>
