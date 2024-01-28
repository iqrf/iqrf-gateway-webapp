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
				{{ $t('components.accessControl.apiKeys.delete.title') }}
			</template>
			{{ $t('components.accessControl.apiKeys.delete.prompt', {id: apiKey.id}) }}
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
import { type ApiKeyService } from '@iqrf/iqrf-gateway-webapp-client/services';
import { type ApiKeyInfo } from '@iqrf/iqrf-gateway-webapp-client/types';
import { mdiDelete } from '@mdi/js';
import { ref, type Ref , type PropType } from 'vue';
import { toast } from 'vue3-toastify';

import Card from '@/components/Card.vue';
import ModalWindow from '@/components/ModalWindow.vue';
import { useApiClient } from '@/services/ApiClient';


const emit = defineEmits(['refresh']);
const componentProps = defineProps({
	apiKey: {
		type: Object as PropType<ApiKeyInfo>,
		required: true,
	},
});
const show: Ref<boolean> = ref(false);
const service: ApiKeyService = useApiClient().getApiKeyService();

async function onSubmit(): Promise<void> {
	service.delete(componentProps.apiKey.id!)
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
