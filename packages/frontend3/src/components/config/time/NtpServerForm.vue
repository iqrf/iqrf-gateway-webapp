<template>
	<v-dialog
		v-model='show'
		persistent
		scrollable
		no-click-animation
		:width='width'
	>
		<template #activator='{ props }'>
			<v-btn
				v-if='action === FormAction.Add'
				v-bind='props'
				:color='iconColor()'
				:icon='activatorIcon()'
			/>
			<v-icon
				v-else
				v-bind='props'
				:color='iconColor()'
				class='me-2'
				size='large'
			>
				{{ activatorIcon() }}
			</v-icon>
		</template>
		<v-form ref='form' @submit.prevent='onSubmit'>
			<Card>
				<template #title>
					{{ $t(`components.configuration.time.ntpServers.${action}`) }}
				</template>
				<TextInput
					v-model='ntpServer'
					label='NTP server address'
					:rules='[
						(v: string|null) => ValidationRules.required(v, $t("components.configuration.time.ntpServers.validation.serverMissing")),
						(v: string|null) => ValidationRules.server(v, $t("components.configuration.time.ntpServers.validation.serverInvalid")),
					]'
					required
				/>
				<template #actions>
					<v-btn
						color='primary'
						type='submit'
						variant='elevated'
					>
						{{ $t(`generic.actions.${action}`) }}
					</v-btn>
					<v-spacer />
					<v-btn
						color='grey-darken-2'
						variant='elevated'
						@click='close'
					>
						{{ $t('generic.button.cancel') }}
					</v-btn>
				</template>
			</Card>
		</v-form>
	</v-dialog>
</template>

<script lang='ts' setup>
import { ref, Ref, watchEffect } from 'vue';
import { VForm } from 'vuetify/components';

import Card from '@/components/Card.vue';
import TextInput from '@/components/TextInput.vue';

import { getModalWidth } from '@/helpers/modal';
import { validateForm } from '@/helpers/validateForm';
import ValidationRules from '@/helpers/ValidationRules';
import { mdiPencil, mdiPlus } from '@mdi/js';
import { FormAction } from '@/enums/controls';

interface Props {
	action: FormAction;
	index?: number;
	server?: string;
}

const emit = defineEmits(['save']);
const props = defineProps<Props>();
const show: Ref<boolean> = ref(false);
const width = getModalWidth();
const form: Ref<typeof VForm | null> = ref(null);
const ntpServer: Ref<string> = ref('');

watchEffect(async (): Promise<void> => {
	if (props.action === FormAction.Add) {
		ntpServer.value = '';
	} else {
		if (props.server !== undefined) {
			ntpServer.value = props.server;
		}
	}
});

function activatorIcon(): string {
	if (props.action == FormAction.Add) {
		return mdiPlus;
	}
	return mdiPencil;
}

function iconColor(): string {
	if (props.action == FormAction.Add) {
		return 'white';
	}
	return 'info';
}

async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value)) {
		return;
	}
	close();
	emit('save', props.index, ntpServer.value);
	ntpServer.value = '';
}

function close(): void {
	show.value = false;
}
</script>
