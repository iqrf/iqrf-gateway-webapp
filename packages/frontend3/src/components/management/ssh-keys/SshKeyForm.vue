<template>
	<v-dialog
		v-model='show'
		peristent
		scrollable
		no-click-animation
		width='60%'
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
					v-model='key.key'
					label='SSH key'
					:rules='[
						(v: string|null) => ValidationRules.required(v, "waaaah need key"),
						(v: string) => validateKey(v),
					]'
					required
					@change='updateDescription'
				/>
				<TextInput
					v-model='key.description'
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
	</v-dialog>
</template>

<script lang='ts' setup>
import { PropType, ref, Ref, watchEffect } from 'vue';
import { useI18n } from 'vue-i18n';
import { VForm } from 'vuetify/components';
import Card from '@/components/Card.vue';

import { FormAction } from '@/enums/controls';
import { validateForm } from '@/helpers/validateForm';

import { SshKeyCreate } from '@iqrf/iqrf-gateway-webapp-client/types/Gateway';
import { computed } from 'vue';
import { mdiPencil, mdiPlus } from '@mdi/js';
import TextInput from '@/components/TextInput.vue';
import ValidationRules from '@/helpers/ValidationRules';
import { SshKeyUtils } from '@iqrf/iqrf-gateway-webapp-client/utils';


const emit = defineEmits(['add', 'edit']);
const props = defineProps({
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
const key: Ref<SshKeyCreate> = ref(defaultKey);

const iconColor = computed(() => {
	if (props.action === FormAction.Add) {
		return 'white';
	}
	return 'info';
});
const activatorIcon = computed(() => {
	if (props.action === FormAction.Add) {
		return mdiPlus;
	}
	return mdiPencil;
});
const dialogTitle = computed(() => {
	if (props.action === FormAction.Add) {
		return i18n.t('components.sshKeys.form.addTitle').toString();
	}
	return i18n.t('components.sshKeys.form.editTitle').toString();
});

watchEffect(async(): Promise<void> => {
	if (props.action === FormAction.Add) {
		key.value = {...defaultKey};
	} else if (props.action === FormAction.Edit) {
		if (props.sshKey) {
			key.value = {...props.sshKey};
		} else {
			key.value = {...defaultKey};
		}
	}
});

function validateKey(key: string): boolean|string {
	try {
		SshKeyUtils.validatePublicKey(key, props.keyTypes);
		return true;
	} catch (e) {
		return (e as Error).message;
	}
}

async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value)) {
		return;
	}
	if (props.action === FormAction.Add) {
		emit('add', key.value);
	} else {
		emit('edit', key.value);
	}
	close();
}

function updateDescription(): void {
	key.value.description = key.value.key.split(' ').slice(2).join(' ');
}

function close(): void {
	show.value = false;
	key.value = {...defaultKey};
}

</script>
