<template>
	<v-dialog
		v-model='show'
		scrollable
		persistent
		no-click-animation
		:width='width'
	>
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
				<TextInput
					v-model='key.description'
					:label='$t("common.labels.description")'
					:rules='[
						(v: string|null) => ValidationRules.required(v, $t("components.accessControl.apiKeys.validation.descriptionMissing")),
					]'
					required
				/>
				<v-checkbox
					v-model='setExpiration'
					:label='$t("components.accessControl.apiKeys.form.expiration")'
				/>
				<label for='datetimeinput'>
					{{ $t('components.accessControl.apiKeys.table.expiration') }}
				</label>
				<VueDatePicker
					id='datetimeinput'
					v-model='expiration'
					class='mb-4'
					:enable-seconds='true'
					:show-now-button='true'
					:teleport='true'
					:disabled='!setExpiration'
					:state='datePickerState'
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
	</v-dialog>
</template>

<script lang='ts' setup>
import { type ApiKeyService } from '@iqrf/iqrf-gateway-webapp-client/services';
import { type ApiKeyConfig, type ApiKeyInfo } from '@iqrf/iqrf-gateway-webapp-client/types';
import { DateTimeUtils } from '@iqrf/iqrf-gateway-webapp-client/utils';
import { mdiPencil, mdiPlus } from '@mdi/js';
import { computed, type PropType, ref, type Ref, watchEffect } from 'vue';
import { useI18n } from 'vue-i18n';
import { VForm } from 'vuetify/components';

import Card from '@/components/Card.vue';
import TextInput from '@/components/TextInput.vue';
import { FormAction } from '@/enums/controls';
import { getModalWidth } from '@/helpers/modal';
import { validateForm } from '@/helpers/validateForm';
import ValidationRules from '@/helpers/ValidationRules';
import { useApiClient } from '@/services/ApiClient';

const emit = defineEmits(['refresh']);
const componentProps = defineProps({
	action: {
		type: String as PropType<FormAction>,
		default: FormAction.Add,
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
});
const i18n = useI18n();
const show: Ref<boolean> = ref(false);
const width = getModalWidth();
const service: ApiKeyService = useApiClient().getApiKeyService();
const form: Ref<typeof VForm | null> = ref(null);
const defaultKey: ApiKeyConfig = {
	description: '',
	expiration: null,
};
const setExpiration: Ref<boolean> = ref(false);
const expiration: Ref<Date | null> = ref(null);
const key: Ref<ApiKeyInfo> = ref(defaultKey);

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
		return i18n.t('components.accessControl.apiKeys.form.addTitle').toString();
	}
	return i18n.t('components.accessControl.apiKeys.form.editTitle').toString();
});
const datePickerState = computed((): false|null => {
	if (!setExpiration.value) {
		return null;
	}
	if (expiration.value !== null && expiration.value !== undefined) {
		return null;
	}
	return false;
});

watchEffect(async(): Promise<void> => {
	if (componentProps.action === FormAction.Add) {
		key.value = {...defaultKey};
	} else if (componentProps.action === FormAction.Edit) {
		if (componentProps.apiKey) {
			key.value = {...componentProps.apiKey};
		} else {
			key.value = {...defaultKey};
		}
	}
	if (key.value.expiration !== null) {
		expiration.value = key.value.expiration.toJSDate();
	} else {
		expiration.value = null;
	}
	setExpiration.value = key.value.expiration !== null;
});

async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value)) {
		return;
	}
	const keyToSave = key.value;
	if (setExpiration.value && expiration.value !== null) {
		keyToSave.expiration = DateTimeUtils.deserialize(expiration.value.toISOString());
	} else {
		keyToSave.expiration = null;
	}
	if (componentProps.action === FormAction.Add) {
		service.create(keyToSave)
			.then(() => {
				close();
				emit('refresh');
			})
			.catch(() => {
				// TODO ERROR
			});
	} else if (componentProps.action === FormAction.Edit) {
		const id = keyToSave.id!;
		delete keyToSave.id;
		service.edit(id, keyToSave)
			.then(() => {
				close();
				emit('refresh');
			})
			.catch(() => {
				// TODO ERROR
			});
	}
}

function close(): void {
	show.value = false;
	key.value = {...defaultKey};
}
</script>