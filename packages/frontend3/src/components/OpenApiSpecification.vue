<template>
	<Card>
		<template #title>
			{{ $t('components.openApi.title') }}
		</template>
		<div
			id='swagger'
			class='swagger'
		/>
		<v-alert
			v-if='componentState == ComponentState.FetchFailed'
			variant='tonal'
			type='error'
		>
			{{ $t('components.openApi.messages.fetchFailed') }}
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

import 'swagger-ui/dist/swagger-ui.css';

import Card from '@/components/Card.vue';
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
				requestInterceptor: (request: SwaggerRequest) => {
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
			toast.error(i18n.t('components.openApi.messages.fetchFailed').toString());
		});
}

onMounted(fetch);
</script>
