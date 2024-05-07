<template>
	<Card>
		<template #title>
			{{ $t('components.maintenance.mender.remount.title') }}
		</template>
		<v-btn
			class='mr-2'
			color='primary'
			:disabled='componentState === ComponentState.Saving'
			@click='onSubmit(MenderMountMode.RO)'
		>
			{{ $t('components.maintenance.mender.remount.readonly') }}
		</v-btn>
		<v-btn
			color='primary'
			:disabled='componentState === ComponentState.Saving'
			@click='onSubmit(MenderMountMode.RW)'
		>
			{{ $t('components.maintenance.mender.remount.readwrite') }}
		</v-btn>
	</Card>
</template>

<script lang='ts' setup>
import { type MenderService } from '@iqrf/iqrf-gateway-webapp-client/services';
import { MenderMountMode, type MenderRemount } from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import { type Ref, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import Card from '@/components/Card.vue';
import { useApiClient } from '@/services/ApiClient';
import { ComponentState } from '@/types/ComponentState';

const componentState: Ref<ComponentState> = ref(ComponentState.Ready);
const i18n = useI18n();
const service: MenderService = useApiClient().getMenderService();

function onSubmit(mode: MenderMountMode): void {
	const data: MenderRemount = {
		mode: mode,
	};
	componentState.value = ComponentState.Saving;
	service.remount(data)
		.then(() => {
			toast.success(
				i18n.t('components.maintenance.mender.remount.messages.success'),
			);
			componentState.value = ComponentState.Ready;
		})
		.catch(() => {
			componentState.value = ComponentState.Ready;
			toast.error('TODO REMOUNT ERROR HANDLING');
		});
}
</script>
