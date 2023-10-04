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
				{{ $t('components.management.sshKeys.delete.title') }}
			</template>
			{{ $t('components.management.sshKeys.delete.prompt', {id: sshKey.id}) }}
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
import { ref, Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import Card from '@/components/Card.vue';

import { getModalWidth } from '@/helpers/modal';
import { mdiDelete } from '@mdi/js';
import { PropType } from 'vue';
import { SshKeyInfo } from '@iqrf/iqrf-gateway-webapp-client/types/Gateway';
import { SshKeyService } from '@iqrf/iqrf-gateway-webapp-client/services/Gateway';
import { useApiClient } from '@/services/ApiClient';

const emit = defineEmits(['refresh']);
const props = defineProps({
	sshKey: {
		type: Object as PropType<SshKeyInfo>,
		required: true,
	},
});
const show: Ref<boolean> = ref(false);
const width = getModalWidth();
const service: SshKeyService = useApiClient().getGatewayServices().getSshKeyService();

async function onSubmit(): Promise<void> {
	service.deleteKey(props.sshKey.id)
		.then(() => {
			close();
			emit('refresh');
		})
		.catch(() => {});
}

function close(): void {
	show.value = false;
}
</script>
