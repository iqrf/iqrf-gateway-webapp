<template>
	<ModalWindow v-model='show'>
		<template #activator='{ props }'>
			<v-icon
				v-bind='props'
				color='error'
				size='large'
				:icon='mdiDelete'
			/>
		</template>
		<Card>
			<template #title>
				{{ $t('components.configuration.profiles.delete.title') }}
			</template>
			{{ $t('components.configuration.profiles.delete.prompt', {name: profile.name}) }}
			<template #actions>
				<v-btn
					color='primary'
					variant='elevated'
					@click='onSubmit'
				>
					{{ $t('common.buttons.delete') }}
				</v-btn>
				<v-spacer />
				<v-btn
					color='grey-darken-2'
					variant='elevated'
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
import { type IqrfGatewayDaemonMapping } from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import { mdiDelete } from '@mdi/js';
import { type Ref, ref , type PropType } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import Card from '@/components/Card.vue';
import ModalWindow from '@/components/ModalWindow.vue';
import { useApiClient } from '@/services/ApiClient';

const emit = defineEmits(['deleted']);
const componentProps = defineProps({
	profile: {
		type: Object as PropType<IqrfGatewayDaemonMapping>,
		required: true,
	},
});
const i18n = useI18n();
const show: Ref<boolean> = ref(false);
const service: IqrfGatewayDaemonService = useApiClient().getConfigServices().getIqrfGatewayDaemonService();

async function onSubmit(): Promise<void> {
	if (componentProps.profile.id === undefined) {
		return;
	}
	service.deleteMapping(componentProps.profile.id)
		.then(() => {
			toast.success(
				i18n.t('components.configuration.profiles.messages.delete.success', {name: componentProps.profile.name}),
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
