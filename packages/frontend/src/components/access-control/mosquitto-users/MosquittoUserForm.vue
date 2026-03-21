<!--
Copyright 2017-2026 IQRF Tech s.r.o.
Copyright 2019-2026 MICRORISC s.r.o.

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
		v-model='show'
		persistent
	>
		<template #activator='{ props }'>
			<IActionBtn
				v-bind='props'
				:action='Action.Add'
				container-type='card-title'
				:tooltip='$t("components.accessControl.mosquittoUsers.actions.add")'
				:disabled='disabled'
			/>
		</template>
		<v-form
			ref='form'
			v-slot='{ isValid }'
			:disabled='componentState === ComponentState.Action'
			@submit.prevent='onSubmit()'
		>
			<ICard :action='Action.Add'>
				<template #title>
					{{ $t('components.accessControl.mosquittoUsers.actions.add') }}
				</template>
				<template #actions>
					<IActionBtn
						:action='Action.Add'
						:loading='componentState === ComponentState.Action'
						:disabled='!isValid.value'
						type='submit'
					/>
					<v-spacer />
					<IActionBtn
						:action='Action.Cancel'
						:disabled='componentState === ComponentState.Action'
						@click='close()'
					/>
				</template>
				<ITextInput
					v-model='username'
					:label='$t("components.common.fields.username")'
					:rules='[
						(v: string|null) => ValidationRules.required(v, $t("components.common.validations.username.required")),
					]'
					required
					:prepend-inner-icon='mdiAccount'
				/>
				<IPasswordInput
					v-model='password'
					:label='$t("components.common.fields.password")'
					:rules='[
						(v: string|null) => ValidationRules.required(v, $t("components.common.validations.password.required")),
						(v: string) => ValidationRules.betweenLen(v, 15, 64, $t("components.common.validations.password.betweenLen")),
						(v: string) => ValidationRules.webappUserPassword(v, $t("components.common.validations.password.invalid")),
					]'
					:prepend-inner-icon='mdiKey'
				>
					<template #append>
						<v-tooltip location='left'>
							<template #activator='{ props }'>
								<v-icon v-bind='props' :icon='mdiHelpCircleOutline' />
							</template>
							<div style='white-space: pre-line;'>
								{{ $t('components.common.hints.password.note') }}
								<ul style='padding-left: 1.2rem; margin: 0;'>
									<li>{{ $t('components.common.hints.password.letters') }}</li>
									<li>{{ $t('components.common.hints.password.numbers') }}</li>
									<li>{{ $t('components.common.hints.password.special', { special: '!"#$%&\'()*+,-./:;<=>?@[\\]^_`{|}~' }) }}</li>
								</ul>
							</div>
						</v-tooltip>
					</template>
				</IPasswordInput>
			</ICard>
		</v-form>
	</IModalWindow>
</template>

<script lang='ts' setup>
import { MosquittoUserService } from '@iqrf/iqrf-gateway-webapp-client/services/Security';
import { MosquittoUserCreate } from '@iqrf/iqrf-gateway-webapp-client/types/Security';
import {
	Action,
	ComponentState,
	IActionBtn,
	ICard,
	IModalWindow,
	IPasswordInput,
	ITextInput,
	ValidationRules,
} from '@iqrf/iqrf-vue-ui';
import { mdiAccount, mdiHelpCircleOutline, mdiKey } from '@mdi/js';
import { ref, useTemplateRef } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { VForm } from 'vuetify/components';

import { validateForm } from '@/helpers/validateForm';
import { useApiClient } from '@/services/ApiClient';

const componentProps = withDefaults(
	defineProps<{
		disabled?: boolean;
	}>(),
	{
		disabled: false,
	},
);
const emit = defineEmits<{
	refresh: [];
}>();
const componentState = ref<ComponentState>(ComponentState.Idle);
const i18n = useI18n();
const show = ref<boolean>(false);
const form = useTemplateRef<VForm>('form');
const service: MosquittoUserService = useApiClient()
	.getSecurityServices()
	.getMosquittoUserService();
const username = ref<string>('');
const password = ref<string>('');

async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value)) {
		return;
	}
	componentState.value = ComponentState.Action;
	const params: MosquittoUserCreate = {
		username: username.value,
		password: password.value,
	};
	try {
		await service.create(params);
		toast.success(
			i18n.t('components.accessControl.mosquittoUsers.messages.create.success'),
		);
		close();
		emit('refresh');
	} catch {
		toast.error(
			i18n.t('components.accessControl.mosquittoUsers.messages.create.failed'),
		);
	}
	componentState.value = ComponentState.Idle;
}

function close(): void {
	show.value = false;
	resetForm();
}

function resetForm(): void {
	username.value = '';
	password.value = '';
}

</script>
