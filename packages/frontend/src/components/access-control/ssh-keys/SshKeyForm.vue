<!--
Copyright 2017-2025 IQRF Tech s.r.o.
Copyright 2019-2025 MICRORISC s.r.o.

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
	<ModalWindow v-model='showDialog'>
		<template #activator='{ props }'>
			<ICardTitleActionBtn
				v-bind='props'
				:action='Action.Add'
				:tooltip='$t("components.accessControl.sshKeys.actions.add")'
			/>
		</template>
		<v-form
			ref='form'
			v-slot='{ isValid }'
			:disabled='componentState === ComponentState.Saving'
			@submit.prevent='onSubmit()'
		>
			<ICard :action='Action.Add'>
				<template #title>
					{{ $t('components.accessControl.sshKeys.form.addTitle') }}
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
						(v: string|null) => ValidationRules.required(v, $t("components.accessControl.sshKeys.validation.key.required")),
						(v: string) => validateKey(v),
					]'
					required
					@change='updateDescription()'
				/>
				<TextInput
					v-model='localKey.description'
					label='Description'
					:prepend-inner-icon='mdiTextShort'
				/>
				<template #actions>
					<ICardActionBtn
						:action='Action.Add'
						:disabled='!isValid.value || componentState === ComponentState.Saving'
						type='submit'
					/>
					<v-spacer />
					<ICardActionBtn
						:action='Action.Cancel'
						:disabled='componentState === ComponentState.Saving'
						@click='close()'
					/>
				</template>
			</ICard>
		</v-form>
	</ModalWindow>
</template>

<script lang='ts' setup>
import { type SshKeyService } from '@iqrf/iqrf-gateway-webapp-client/services/Security';
import {
	type SshKeyCreate,
} from '@iqrf/iqrf-gateway-webapp-client/types/Security';
import { SshKeyUtils } from '@iqrf/iqrf-gateway-webapp-client/utils';
import {
	Action, ICard, ICardActionBtn,
	ICardTitleActionBtn,
	ValidationRules,
} from '@iqrf/iqrf-vue-ui';
import { mdiKey, mdiTextShort } from '@mdi/js';
import { type PropType, ref, type Ref , watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { VForm } from 'vuetify/components';

import TextInput from '@/components/layout/form/TextInput.vue';
import ModalWindow from '@/components/ModalWindow.vue';
import { validateForm } from '@/helpers/validateForm';
import { useApiClient } from '@/services/ApiClient';
import { ComponentState } from '@/types/ComponentState';

const componentState: Ref<ComponentState> = ref(ComponentState.Ready);
const componentProps = defineProps({
	install: {
		type: Boolean,
		default: false,
		required: false,
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
		required: true,
	},
});
const emit = defineEmits(['refresh']);
const i18n = useI18n();
const showDialog: Ref<boolean> = ref(false);
const form: Ref<VForm | null> = ref(null);
const defaultKey: SshKeyCreate = {
	key: '',
	description: '',
};
const localKey: Ref<SshKeyCreate> = ref(defaultKey);
const service: SshKeyService = useApiClient().getSecurityServices().getSshKeyService();

watch(showDialog, (newVal: boolean): void => {
	if (!newVal) {
		return;
	}
	localKey.value = { ...defaultKey };
});

/**
 * Validates the SSH key
 * @param {string} key SSH key to validate
 * @return {boolean|string} Validation result, true if the key is valid, error message otherwise
 */
function validateKey(key: string): boolean|string {
	try {
		SshKeyUtils.validatePublicKey(key, componentProps.keyTypes);
		return true;
	} catch (error) {
		return (error as Error).message;
	}
}

/**
 * Handles the form submission
 */
async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value)) {
		return;
	}
	componentState.value = ComponentState.Saving;
	try {
		await service.createSshKeys([localKey.value]);
		toast.success(
			i18n.t('components.accessControl.sshKeys.messages.add.success'),
		);
		close();
		emit('refresh');
	} catch {
		toast.error(
			i18n.t('components.accessControl.sshKeys.messages.add.failed'),
		);
	}
	componentState.value = ComponentState.Ready;
}

/**
 * Updates the description of the SSH key
 */
function updateDescription(): void {
	localKey.value.description = localKey.value.key.split(' ').slice(2).join(' ');
}

/**
 * Closes the dialog window
 */
function close(): void {
	showDialog.value = false;
	localKey.value = { ...defaultKey };
}

</script>
