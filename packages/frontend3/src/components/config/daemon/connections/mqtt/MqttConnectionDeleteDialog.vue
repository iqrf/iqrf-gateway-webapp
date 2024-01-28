<template>
	<ModalWindow v-model='show'>
		<template #activator='{ props }'>
			<v-icon
				id='delete-activator'
				v-bind='props'
				color='error'
				size='large'
				:icon='mdiDelete'
			/>
			<v-tooltip
				activator='#delete-activator'
				location='bottom'
			>
				{{ $t('components.configuration.daemon.connections.actions.delete') }}
			</v-tooltip>
		</template>
		<Card>
			<template #title>
				{{ $t('components.configuration.daemon.connections.mqtt.delete.title') }}
			</template>
			{{ $t('components.configuration.daemon.connections.mqtt.delete.prompt', {name: connectionProfile.instance}) }}
			<template #actions>
				<v-btn
					color='primary'
					variant='elevated'
					:disabled='componentState === ComponentState.Saving'
					@click='onSubmit'
				>
					{{ $t('common.buttons.delete') }}
				</v-btn>
				<v-spacer />
				<v-btn
					color='grey-darken-2'
					variant='elevated'
					:disabled='componentState === ComponentState.Saving'
					@click='close'
				>
					{{ $t('common.buttons.close') }}
				</v-btn>
			</template>
		</Card>
	</ModalWindow>
</template>

<script lang='ts' setup>
import { type IqrfGatewayDaemonService } from '@iqrf/iqrf-gateway-webapp-client/services/Config';
import {
	IqrfGatewayDaemonComponentName,
	type IqrfGatewayDaemonMqttMessaging,
} from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import { mdiDelete } from '@mdi/js';
import {
	ref,
	type Ref,
	type PropType,
} from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import Card from '@/components/Card.vue';
import ModalWindow from '@/components/ModalWindow.vue';
import { useApiClient } from '@/services/ApiClient';
import { ComponentState } from '@/types/ComponentState';

const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const componentProps = defineProps({
	connectionProfile: {
		type: Object as PropType<IqrfGatewayDaemonMqttMessaging>,
		required: true,
	},
});
const emit = defineEmits(['deleted']);
const i18n = useI18n();
const show: Ref<boolean> = ref(false);
const service: IqrfGatewayDaemonService = useApiClient().getConfigServices().getIqrfGatewayDaemonService();

function onSubmit(): void {
	componentState.value = ComponentState.Saving;
	service.deleteInstance(IqrfGatewayDaemonComponentName.IqrfMqttMessaging, componentProps.connectionProfile.instance)
		.then(() => {
			componentState.value = ComponentState.Ready;
			toast.success(
				i18n.t('components.configuration.daemon.connections.mqtt.messages.delete.success', {name: componentProps.connectionProfile.instance}),
			);
			close();
			emit('deleted');
		})
		.catch(() => toast.error('TODO ERROR HANDLING'));
}

function close(): void {
	show.value = false;
}
</script>
