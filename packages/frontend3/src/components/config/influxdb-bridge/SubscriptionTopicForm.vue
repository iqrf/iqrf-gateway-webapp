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
				id='add-activator'
				v-bind='props'
				:color='iconColor()'
				:icon='activatorIcon()'
			/>
			<v-tooltip
				v-if='action === FormAction.Add'
				activator='#add-activator'
				location='bottom'
			>
				{{ $t('components.configuration.influxdb-bridge.actions.add') }}
			</v-tooltip>
			<v-icon
				v-if='action === FormAction.Edit'
				id='edit-activator'
				v-bind='props'
				:color='iconColor()'
				class='me-2'
				size='large'
			>
				{{ activatorIcon() }}
			</v-icon>
			<v-tooltip
				v-if='action === FormAction.Edit'
				activator='#edit-activator'
				location='bottom'
			>
				{{ $t('components.configuration.influxdb-bridge.actions.edit') }}
			</v-tooltip>
		</template>
		<v-form
			ref='form'
			v-slot='{ isValid }'
			@submit.prevent='onSubmit'
		>
			<Card>
				<template #title>
					{{ $t(`components.configuration.influxdb-bridge.actions.${action}`) }}
				</template>
				<TextInput
					v-model='subscriptionTopic'
					:label='$t("components.configuration.influxdb-bridge.topic")'
					:rules='[
						(v: string|null) => ValidationRules.required(v, $t("components.configuration.influxdb-bridge.validation.topic")),
					]'
					required
				/>
				<template #actions>
					<v-btn
						color='primary'
						type='submit'
						variant='elevated'
						:disabled='!isValid.value'
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
import { mdiPencil, mdiPlus } from '@mdi/js';
import { ref, type Ref, watchEffect } from 'vue';
import { VForm } from 'vuetify/components';

import Card from '@/components/Card.vue';
import ModalWindow from '@/components/ModalWindow.vue';
import TextInput from '@/components/TextInput.vue';
import { FormAction } from '@/enums/controls';
import { validateForm } from '@/helpers/validateForm';
import ValidationRules from '@/helpers/ValidationRules';

interface Props {
	action: FormAction;
	index?: number;
	topic?: string;
}

const emit = defineEmits(['save']);
const componentProps = defineProps<Props>();
const show: Ref<boolean> = ref(false);
const form: Ref<typeof VForm | null> = ref(null);
const subscriptionTopic: Ref<string> = ref('');

watchEffect(async (): Promise<void> => {
	if (componentProps.action === FormAction.Add) {
		subscriptionTopic.value = '';
	} else {
		if (componentProps.topic !== undefined) {
			subscriptionTopic.value = componentProps.topic;
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
		return 'primary';
	}
	return 'info';
}

async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value)) {
		return;
	}
	close();
	emit('save', componentProps.index, subscriptionTopic.value);
	subscriptionTopic.value = '';
}

function close(): void {
	show.value = false;
}
</script>
