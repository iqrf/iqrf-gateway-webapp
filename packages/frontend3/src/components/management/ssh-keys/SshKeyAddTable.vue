<template>
	<Card>
		<template #title>
			{{ $t('pages.install.sshKeys.title') }}
		</template>
		<div v-if='install'>
			{{ $t('components.management.sshKeys.installNote') }}
		</div>
		<v-alert
			v-if='loaded !== null'
			:type='loaded ? "info" : "error"'
			variant='tonal'
			:class='install ? "my-4" : "mb-4"'
		>
			<span v-if='loaded'>
				<span v-if='types.length === 0'>
					{{ $t('components.management.sshKeys.noneSupported') }}
				</span>
				<span v-else>
					{{ $t('components.management.sshKeys.supported') }}
					<ul>
						<li
							v-for='key of types'
							:key='key'
						>
							{{ key }}
						</li>
					</ul>
				</span>
			</span>
			<span v-else>
				{{ $t('core.security.ssh.messages.fetchFailed') }}
			</span>
		</v-alert>
		<v-form @submit.prevent='onSubmit'>
			<DataTable
				:headers='headers'
				:items='keys'
				:hover='true'
				:dense='true'
			>
				<template #top>
					<v-toolbar color='primary' density='compact' rounded>
						<v-toolbar-title>
							SSH keys to add
						</v-toolbar-title>
						<v-toolbar-items>
							<SshKeyForm
								:action='FormAction.Add'
								:key-types='types'
								@add='addKey'
							/>
						</v-toolbar-items>
					</v-toolbar>
				</template>
				<template #item.actions='{ item, index }'>
					<span>
						<SshKeyForm
							:action='FormAction.Edit'
							:ssh-key='item'
							:key-types='types'
							@edit='$event => updateKey(index, $event)'
						/>
						<v-tooltip
							activator='parent'
							location='bottom'
						>
							{{ $t('common.buttons.edit') }}
						</v-tooltip>
					</span>
					<span>
						<v-icon
							color='error'
							:icon='mdiDelete'
							size='large'
							class='me-2'
							@click='removeKey'
						/>
						<v-tooltip
							activator='parent'
							location='bottom'
						>
							{{ $t('common.buttons.delete') }}
						</v-tooltip>
					</span>
				</template>
			</DataTable>
			<v-btn
				color='primary'
				type='submit'
				:disabled='keys.length === 0'
			>
				{{ $t('common.buttons.save') }}
			</v-btn>
			<v-btn
				v-if='install'
				class='ml-1'
				color='grey'
				variant='elevated'
				@click='toNextStep'
			>
				{{ $t('common.buttons.skip') }}
			</v-btn>
		</v-form>
	</Card>
</template>

<script lang='ts' setup>
import { ref, Ref } from 'vue';
import Card from '@/components/Card.vue';
import DataTable from '@/components/DataTable.vue';
import { SshKeyCreate } from '@iqrf/iqrf-gateway-webapp-client/types/Gateway';

import SshKeyForm from '@/components/management/ssh-keys/SshKeyForm.vue';
import { FormAction } from '@/enums/controls';
import { basicErrorToast } from '@/helpers/errorToast';
import router from '@/router';
import { useApiClient } from '@/services/ApiClient';
import { SshKeyService } from '@iqrf/iqrf-gateway-webapp-client/services/Gateway';
import { AxiosError } from 'axios';
import { useInstallStore } from '@/store/install';
import { onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { mdiDelete } from '@mdi/js';

const props = defineProps({
	install: {
		type: Boolean,
		default: false,
		required: false,
	},
});
const i18n = useI18n();
const installStore = useInstallStore();
const headers = [
	{key: 'key', title: 'SSH key'},
	{key: 'description', title: 'Description'},
	{key: 'actions', title: i18n.t('generic.actions.title').toString(), align: 'end'},
];
const keys: Ref<SshKeyCreate[]> = ref([]);
const loaded: Ref<boolean | null> = ref(null);
const types: Ref<string[]> = ref([]);

const service: SshKeyService = useApiClient().getGatewayServices().getSshKeyService();

onMounted(() => {
	service.fetchKeyTypes()
		.then((rsp: string[]) => {
			types.value = rsp;
			loaded.value = true;
		})
		.catch(() => loaded.value = false);
});

async function onSubmit(): Promise<void> {
	service.createSshKeys(keys.value)
		.then(() => {
			if (props.install) {
				toNextStep();
			}
		})
		.catch((error: AxiosError) => basicErrorToast(error, 'core.security.ssh.messages.saveFailed'));
}

function toNextStep(): void {
	const nextStep = installStore.getNextStep;
	if (nextStep === null) {
		router.push('/');
		return;
	}
	router.push({name: nextStep.route});
}

function addKey(key: SshKeyCreate): void {
	keys.value.push(key);
}

function updateKey(index: number, key: SshKeyCreate): void {
	keys.value[index] = key;
}

function removeKey(index: number): void {
	keys.value.splice(index, 1);
}

</script>
