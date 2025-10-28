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
		v-model='show'
		persistent
	>
		<template #activator='{ props }'>
			<IActionBtn
				v-if='action === Action.Add'
				v-bind='props'
				:action='action'
				container-type='card-title'
				:tooltip='$t("components.accessControl.apiKeys.actions.add")'
				:disabled='disabled'
			/>
			<IDataTableAction
				v-else
				v-bind='props'
				:action='action'
				:tooltip='$t("components.accessControl.apiKeys.actions.edit")'
				:disabled='disabled'
			/>
		</template>
		<v-form
			ref='form'
			v-slot='{ isValid }'
			:disabled='componentState === ComponentState.Action'
			@submit.prevent='onSubmit()'
		>
			<ICard :action='action'>
				<template #title>
					{{ dialogTitle }}
				</template>
				<ITextInput
					v-model='key.description'
					:prepend-inner-icon='mdiTextShort'
					:label='$t("common.labels.description")'
					:rules='[
						(v: string|null) => ValidationRules.required(v, $t("components.accessControl.apiKeys.validations.description.required")),
					]'
					required
				/>
				<IDateTimeInput
					v-model='expiration'
					:label='$t("components.accessControl.apiKeys.expiration")'
					:min='toRaw(minDate)'
				/>
				<template #actions>
					<IActionBtn
						:action='action'
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
			</ICard>
		</v-form>
	</IModalWindow>
	<ApiKeyDisplayDialog
		ref='displayDialog'
		:api-key='generatedKey'
		@closed='clear'
	/>
</template>

<script lang='ts' setup>
import { type ApiKeyService } from '@iqrf/iqrf-gateway-webapp-client/services/Security';
import {
	type ApiKeyConfig,
	type ApiKeyCreated,
	type ApiKeyInfo,
} from '@iqrf/iqrf-gateway-webapp-client/types/Security';
import { DateTimeUtils } from '@iqrf/iqrf-gateway-webapp-client/utils';
import {
	Action,
	ComponentState,
	IActionBtn,
	ICard,
	IDataTableAction,
	IDateTimeInput,
	IModalWindow,
	ITextInput,
	ValidationRules,
} from '@iqrf/iqrf-vue-ui';
import { mdiTextShort } from '@mdi/js';
import { DateTime } from 'luxon';
import { computed, type PropType, ref, type Ref, type TemplateRef, toRaw, useTemplateRef, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { VForm } from 'vuetify/components';

import ApiKeyDisplayDialog from '@/components/access-control/api-keys/ApiKeyDisplayDialog.vue';
import { validateForm } from '@/helpers/validateForm';
import { useApiClient } from '@/services/ApiClient';

const componentProps = defineProps({
	action: {
		type: String as PropType<Action>,
		default: Action.Add,
		required: false,
	},
	apiKey: {
		type: Object as PropType<ApiKeyConfig | ApiKeyInfo>,
		default: () => ({
			description: '',
			expiration: null,
		}),
		required: false,
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
const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const i18n = useI18n();
const show: Ref<boolean> = ref(false);
const service: ApiKeyService = useApiClient()
	.getSecurityServices()
	.getApiKeyService();
const form: TemplateRef<VForm> = useTemplateRef('form');
const defaultKey: ApiKeyConfig = {
	description: '',
	expiration: null,
};
const expiration: Ref<DateTime | null> = ref(null);
const key: Ref<ApiKeyInfo> = ref(defaultKey);
const generatedKey: Ref<string | null> = ref(null);
const displayDialog: Ref<InstanceType<typeof ApiKeyDisplayDialog>|null> = useTemplateRef('displayDialog');
const minDate: Ref<DateTime | null> = ref(null);

const dialogTitle = computed(() => {
	if (componentProps.action === Action.Add) {
		return i18n.t('components.accessControl.apiKeys.actions.add');
	}
	return i18n.t('components.accessControl.apiKeys.actions.edit');
});

watch(show, (newVal: boolean): void => {
	if (!newVal) {
		return;
	}
	if (componentProps.action === Action.Add) {
		key.value = { ...defaultKey };
	} else if (componentProps.action === Action.Edit) {
		if (componentProps.apiKey) {
			key.value = { ...componentProps.apiKey };
		} else {
			key.value = { ...defaultKey };
		}
	}
	if (key.value.expiration !== null) {
		expiration.value = key.value.expiration;
	} else {
		expiration.value = null;
	}
	minDate.value = DateTime.now();
});

async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value)) {
		return;
	}
	componentState.value = ComponentState.Action;
	const params = { ...key.value };
	console.warn(params, expiration.value);
	if (expiration.value !== null) {
		params.expiration = DateTimeUtils.deserialize(expiration.value.toJSDate().toISOString());
	}
	try {
		if (componentProps.action === Action.Add) {
			const createdKey: ApiKeyCreated = await service.create(params);
			generatedKey.value = createdKey.key;
			toast.success(
				i18n.t('components.accessControl.apiKeys.messages.save.success'),
			);
			close();
			emit('refresh');
			displayDialog.value?.open();
		} else if (componentProps.action === Action.Edit) {
			const id = params.id!;
			delete params.id;
			await service.update(id, params);
			close();
			emit('refresh');
		}
	} catch {
		toast.error(
			i18n.t('components.accessControl.apiKeys.messages.save.failed'),
		);
	}
	componentState.value = ComponentState.Ready;
}

function clear(): void {
	generatedKey.value = null;
}

function close(): void {
	show.value = false;
	key.value = { ...defaultKey };
}
</script>
