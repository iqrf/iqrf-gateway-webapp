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
	<v-skeleton-loader
		:loading='log === null'
		type='text@3, paragraph@2, text@5'
	>
		<v-responsive>
			<pre class='log'>{{ log }}</pre>
		</v-responsive>
	</v-skeleton-loader>
</template>

<script lang='ts' setup>
import { type LogService } from '@iqrf/iqrf-gateway-webapp-client/services/Gateway';
import { onMounted, type PropType, type Ref, ref, watch } from 'vue';
import { toast } from 'vue3-toastify';

import { useApiClient } from '@/services/ApiClient';
import { ComponentState } from '@/types/ComponentState';

const componentProps = defineProps({
	serviceName: {
		type: [String, null] as PropType<string | null>,
		default: null,
		required: true,
	},
});
const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const service: LogService = useApiClient().getGatewayServices().getLogService();
const log: Ref<string | null> = ref(null);

watch(() => componentProps.serviceName, (newVal: string|null) => {
	if (newVal === null) {
		return;
	}
	getServiceLog();
});

function getServiceLog(): void {
	if (componentProps.serviceName === null) {
		return;
	}
	componentState.value = ComponentState.Reloading;
	service.getServiceLog(componentProps.serviceName)
		.then((response: string) => {
			log.value = response;
			componentState.value = ComponentState.Ready;
		})
		.catch(() => toast.error('TODO GET LOG ERROR'));
}

onMounted(() => {
	getServiceLog();
});

</script>
