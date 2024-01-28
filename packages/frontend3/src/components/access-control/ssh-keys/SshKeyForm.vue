<template>
	<ModalWindow v-model='show'>
		<template #activator='{ props }'>
			<v-btn
				v-if='action === FormAction.Add'
				v-bind='props'
				:color='iconColor'
				:icon='activatorIcon'
			/>
			<v-icon
				v-else
				v-bind='props'
				:color='iconColor'
				:icon='activatorIcon'
				size='large'
				class='me-2'
			/>
		</template>
		<v-form ref='form' @submit.prevent='onSubmit'>
			<Card>
				<template #title>
					{{ dialogTitle }}
				</template>
				<v-alert
					type='info'
					variant='tonal'
					class='mb-4'
				>
					<span v-if='keyTypes.length === 0'>
						{{ $t('components.accessControl.sshKeys.noneSupported') }}
					</span>
					<span v-else>
						{{ $t('components.accessControl.sshKeys.supported') }}
						<ul>
							<li
								v-for='keyType of keyTypes'
								:key='keyType'
							>
								{{ keyType }}
							</li>
						</ul>
					</span>
				</v-alert>
				<TextInput
					v-model='localKey.key'
					label='SSH key'
					:rules='[
						(v: string|null) => ValidationRules.required(v, "waaaah need key"),
						(v: string) => validateKey(v),
					]'
					required
					@change='updateDescription'
				/>
				<TextInput
					v-model='localKey.description'
					label='Description'
				/>
				<template #actions>
					<v-btn
						color='primary'
						type='submit'
						variant='elevated'
					>
						{{ $t(`common.buttons.${action}`) }}
					</v-btn>
					<v-spacer />
					<v-btn
						color='grey-darken-2'
						variant='elevated'
						@click='close'
					>
						{{ $t('common.buttons.cancel') }}
					</v-btn>
				</template>
			</Card>
		</v-form>
	</ModalWindow>
</template>

<script lang='ts' setup>
import { type SshKeyService } from '@iqrf/iqrf-gateway-webapp-client/services/Gateway';
import { type SshKeyCreate } from '@iqrf/iqrf-gateway-webapp-client/types/Gateway';
import { SshKeyUtils } from '@iqrf/iqrf-gateway-webapp-client/utils';
import { mdiPencil, mdiPlus } from '@mdi/js';
import { type AxiosError } from 'axios';
import { type PropType, ref, type Ref, watchEffect , computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { VForm } from 'vuetify/components';

import Card from '@/components/Card.vue';
import ModalWindow from '@/components/ModalWindow.vue';
import TextInput from '@/components/TextInput.vue';
import { FormAction } from '@/enums/controls';
import { basicErrorToast } from '@/helpers/errorToast';
import { validateForm } from '@/helpers/validateForm';
import ValidationRules from '@/helpers/ValidationRules';
import { useApiClient } from '@/services/ApiClient';

const emit = defineEmits(['refresh']);
const componentProps = defineProps({
	install: {
		type: Boolean,
		default: false,
		required: false,
	},
	action: {
		type: String as PropType<FormAction>,
		default: FormAction.Add,
		required: true,
	},
	sshKey: {
		type: Object as PropType<SshKeyCreate>,
		default: () => ({
			key: '',
			description: '',
		}),
		required: false,
	},
	keyTypes: {
		type: Array as PropType<string[]>,
		default: () => [],
		required: true,
	},
});
const i18n = useI18n();
const show: Ref<boolean> = ref(false);
const form: Ref<typeof VForm | null> = ref(null);
const defaultKey: SshKeyCreate = {
	key: '',
	description: '',
};
const localKey: Ref<SshKeyCreate> = ref(defaultKey);
const service: SshKeyService = useApiClient().getGatewayServices().getSshKeyService();

const iconColor = computed(() => {
	if (componentProps.action === FormAction.Add) {
		return 'white';
	}
	return 'info';
});
const activatorIcon = computed(() => {
	if (componentProps.action === FormAction.Add) {
		return mdiPlus;
	}
	return mdiPencil;
});
const dialogTitle = computed(() => {
	if (componentProps.action === FormAction.Add) {
		return i18n.t('components.accessControl.sshKeys.form.addTitle').toString();
	}
	return i18n.t('components.accessControl.sshKeys.form.editTitle').toString();
});

watchEffect(async(): Promise<void> => {
	if (componentProps.action === FormAction.Add) {
		localKey.value = {...defaultKey};
	} else if (componentProps.action === FormAction.Edit) {
		if (componentProps.sshKey) {
			localKey.value = {...componentProps.sshKey};
		} else {
			localKey.value = {...defaultKey};
		}
	}
});

function validateKey(key: string): boolean|string {
	try {
		SshKeyUtils.validatePublicKey(key, componentProps.keyTypes);
		return true;
	} catch (e) {
		return (e as Error).message;
	}
}

async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value)) {
		return;
	}
	service.createSshKeys([localKey.value])
		.then(() => {
			close();
			emit('refresh');
		})
		.catch((error: AxiosError) => basicErrorToast(error, 'core.security.ssh.messages.saveFailed'));
}

function updateDescription(): void {
	localKey.value.description = localKey.value.key.split(' ').slice(2).join(' ');
}

function close(): void {
	show.value = false;
	localKey.value = {...defaultKey};
}

</script>
