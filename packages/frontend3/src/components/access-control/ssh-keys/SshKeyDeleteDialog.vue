<template>
	<v-dialog
		v-model='show'
		scrollable
		persistent
		no-click-animation
		:width='width'
	>
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
				{{ $t('components.accessControl.sshKeys.delete.title') }}
			</template>
			{{ $t('components.accessControl.sshKeys.delete.prompt', {id: sshKey.id}) }}
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
	</v-dialog>
</template>

<script lang='ts' setup>
import { type SshKeyService } from '@iqrf/iqrf-gateway-webapp-client/services/Gateway';
import { type SshKeyInfo } from '@iqrf/iqrf-gateway-webapp-client/types/Gateway';
import { mdiDelete } from '@mdi/js';
import { ref, type Ref , type PropType } from 'vue';
import { toast } from 'vue3-toastify';

import Card from '@/components/Card.vue';
import { getModalWidth } from '@/helpers/modal';
import { useApiClient } from '@/services/ApiClient';

const emit = defineEmits(['refresh']);
const componentProps = defineProps({
	sshKey: {
		type: Object as PropType<SshKeyInfo>,
		required: true,
	},
});
const show: Ref<boolean> = ref(false);
const width = getModalWidth();
const service: SshKeyService = useApiClient().getGatewayServices().getSshKeyService();

async function onSubmit(): Promise<void> {
	service.deleteKey(componentProps.sshKey.id)
		.then(() => {
			close();
			emit('refresh');
		})
		.catch(() => toast.error('TODO ERROR HANDLING'));
}

function close(): void {
	show.value = false;
}
</script>
