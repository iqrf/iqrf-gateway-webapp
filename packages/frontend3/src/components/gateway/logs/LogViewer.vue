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
