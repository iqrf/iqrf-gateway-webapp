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
	<ModalWindow
		v-model='show'
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
		<v-form ref='form' v-slot='{ isValid }' @submit.prevent='onSubmit'>
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
					<FormActionButton
						:action='action'
						:disabled='!isValid.value'
						type='submit'
					/>
					<v-spacer />
					<FormActionButton :action='FormAction.Cancel' @click='close' />
				</template>
			</Card>
		</v-form>
	</ModalWindow>
</template>

<script lang='ts' setup>
import { mdiPencil, mdiPlus } from '@mdi/js';
import { ref, type Ref, watchEffect } from 'vue';
import { VForm } from 'vuetify/components';

import Card from '@/components/Card.vue';
import FormActionButton from '@/components/FormActionButton.vue';
import ModalWindow from '@/components/ModalWindow.vue';
import TextInput from '@/components/TextInput.vue';
import { FormAction } from '@/enums/controls';
import { validateForm } from '@/helpers/validateForm';
import ValidationRules from '@/helpers/ValidationRules';

interface Props {
	action: FormAction;
	index?: number;
	server?: string;
}

const emit = defineEmits(['save']);
const componentProps = defineProps<Props>();
const show: Ref<boolean> = ref(false);
const form: Ref<typeof VForm | null> = ref(null);
const ntpServer: Ref<string> = ref('');

watchEffect(async (): Promise<void> => {
	if (componentProps.action === FormAction.Add) {
		ntpServer.value = '';
	} else {
		if (componentProps.server !== undefined) {
			ntpServer.value = componentProps.server;
		}
	}
});

function activatorIcon(): string {
	if (componentProps.action === FormAction.Add) {
		return mdiPlus;
	}
	return mdiPencil;
}

function iconColor(): string {
	if (componentProps.action === FormAction.Add) {
		return 'black';
	}
	return 'info';
}

async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value)) {
		return;
	}
	close();
	emit('save', componentProps.index, ntpServer.value);
	ntpServer.value = '';
}

function close(): void {
	show.value = false;
}
</script>
