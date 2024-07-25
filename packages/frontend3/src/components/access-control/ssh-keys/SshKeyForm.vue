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
	<ModalWindow v-model='show'>
		<template #activator='{ props }'>
			<CardTitleActionBtn
				v-if='action === Action.Add'
				v-bind='props'
				:action='action'
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
		<v-form ref='form' v-slot='{ isValid }' @submit.prevent='onSubmit'>
			<Card :action='action'>
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
					:prepend-inner-icon='mdiKey'
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
					:prepend-inner-icon='mdiTextShort'
				/>
				<template #actions>
					<CardActionBtn
						:action='action'
						:disabled='!isValid.value'
						type='submit'
					/>
					<v-spacer />
					<CardActionBtn
						:action='Action.Cancel'
						@click='close'
					/>
				</template>
			</Card>
		</v-form>
	</ModalWindow>
</template>

<script lang='ts' setup>
import { type SshKeyService } from '@iqrf/iqrf-gateway-webapp-client/services/Security';
import { type SshKeyCreate } from '@iqrf/iqrf-gateway-webapp-client/types/Security';
import { SshKeyUtils } from '@iqrf/iqrf-gateway-webapp-client/utils';
import { mdiKey, mdiPencil, mdiPlus, mdiTextShort } from '@mdi/js';
import { type AxiosError } from 'axios';
import { type PropType, ref, type Ref, watchEffect , computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { VForm } from 'vuetify/components';

import Card from '@/components/layout/card/Card.vue';
import CardActionBtn from '@/components/layout/card/CardActionBtn.vue';
import CardTitleActionBtn from '@/components/layout/card/CardTitleActionBtn.vue';
import TextInput from '@/components/layout/form/TextInput.vue';
import ModalWindow from '@/components/ModalWindow.vue';
import { basicErrorToast } from '@/helpers/errorToast';
import { validateForm } from '@/helpers/validateForm';
import ValidationRules from '@/helpers/ValidationRules';
import { useApiClient } from '@/services/ApiClient';
import { Action } from '@/types/Action';

const emit = defineEmits(['refresh']);
const componentProps = defineProps({
	install: {
		type: Boolean,
		default: false,
		required: false,
	},
	action: {
		type: String as PropType<Action>,
		default: Action.Add,
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
const form: Ref<VForm | null> = ref(null);
const defaultKey: SshKeyCreate = {
	key: '',
	description: '',
};
const localKey: Ref<SshKeyCreate> = ref(defaultKey);
const service: SshKeyService = useApiClient().getSecurityServices().getSshKeyService();

const iconColor = computed(() => {
	if (componentProps.action === Action.Add) {
		return 'white';
	}
	return 'info';
});
const activatorIcon = computed(() => {
	if (componentProps.action === Action.Add) {
		return mdiPlus;
	}
	return mdiPencil;
});
const dialogTitle = computed(() => {
	if (componentProps.action === Action.Add) {
		return i18n.t('components.accessControl.sshKeys.form.addTitle').toString();
	}
	return i18n.t('components.accessControl.sshKeys.form.editTitle').toString();
});

watchEffect((): void => {
	if (componentProps.action === Action.Add) {
		localKey.value = { ...defaultKey };
	} else if (componentProps.action === Action.Edit) {
		if (componentProps.sshKey) {
			localKey.value = { ...componentProps.sshKey };
		} else {
			localKey.value = { ...defaultKey };
		}
	}
});

function validateKey(key: string): boolean|string {
	try {
		SshKeyUtils.validatePublicKey(key, componentProps.keyTypes);
		return true;
	} catch (error) {
		return (error as Error).message;
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
	localKey.value = { ...defaultKey };
}

</script>
