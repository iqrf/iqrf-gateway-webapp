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
	<IModalWindow
		v-model='showDialog'
		persistent
	>
		<template #activator='{ props }'>
			<IActionBtn
				v-bind='props'
				:action='Action.Add'
				container-type='card-title'
				:tooltip='$t("components.accessControl.sshKeys.actions.add")'
				:disabled='disabled'
			/>
		</template>
		<v-form
			ref='form'
			v-slot='{ isValid }'
			:disabled='componentState === ComponentState.Action'
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
				<ITextInput
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
				<ITextInput
					v-model='localKey.description'
					label='Description'
					:prepend-inner-icon='mdiTextShort'
				/>
				<template #actions>
					<IActionBtn
						:action='Action.Add'
						:loading='componentState === ComponentState.Action'
						:disabled='!isValid.value || componentState === ComponentState.Action'
						@click='onSubmit()'
					/>
					<v-spacer />
					<IActionBtn
						:action='Action.Cancel'
						:disabled='componentState === ComponentState.Action'
						@click='close()'
					/>
				</template>
			</ICard>
		</v-form>
	</IModalWindow>
</template>

<script lang='ts' setup>
import { type SshKeyService } from '@iqrf/iqrf-gateway-webapp-client/services/Security';
import {
	type SshKeyCreate,
} from '@iqrf/iqrf-gateway-webapp-client/types/Security';
import { SshKeyUtils } from '@iqrf/iqrf-gateway-webapp-client/utils';
import {
	Action,
	ComponentState,
	IActionBtn,
	ICard,
	IModalWindow,
	ITextInput,
	ValidationRules,
} from '@iqrf/iqrf-vue-ui';
import { mdiKey, mdiTextShort } from '@mdi/js';
import { type PropType, ref, type Ref, type TemplateRef, useTemplateRef, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { VForm } from 'vuetify/components';

import { validateForm } from '@/helpers/validateForm';
import { useApiClient } from '@/services/ApiClient';

const componentProps = defineProps({
	keyTypes: {
		type: Array as PropType<string[]>,
		required: true,
	},
	disabled: {
		type: Boolean,
		default: false,
		required: false,
	},
});
const emit = defineEmits<{
	refresh: [];
}>();
const componentState: Ref<ComponentState> = ref(ComponentState.Idle);
const i18n = useI18n();
const showDialog: Ref<boolean> = ref(false);
const form: TemplateRef<VForm> = useTemplateRef('form');
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
	componentState.value = ComponentState.Action;
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
	componentState.value = ComponentState.Idle;
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
