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
	>
		<template #activator='{ props }'>
			<v-btn
				v-if='action === Action.Add'
				v-tooltip:bottom='$t("components.config.influxdb-bridge.actions.add")'
				v-bind='props'
				color='green'
				:icon='mdiPlus'
			/>
			<IDataTableAction
				v-if='action === Action.Edit'
				v-bind='props'
				:action='action'
				:tooltip='$t("components.config.influxdb-bridge.actions.edit")'
			/>
		</template>
		<v-form
			ref='form'
			v-slot='{ isValid }'
			@submit.prevent='onSubmit()'
		>
			<ICard :action='action'>
				<template #title>
					{{ $t(`components.config.influxdb-bridge.actions.${action}`) }}
				</template>
				<ITextInput
					v-model='subscriptionTopic'
					:label='$t("components.config.influxdb-bridge.topic")'
					:rules='[
						(v: string|null) => ValidationRules.required(v, $t("components.config.influxdb-bridge.validation.topic")),
					]'
					required
				/>
				<template #actions>
					<IActionBtn
						:action='action'
						container-type='card'
						:disabled='!isValid.value'
						type='submit'
					/>
					<v-spacer />
					<IActionBtn
						:action='Action.Cancel'
						container-type='card'
						@click='close()'
					/>
				</template>
			</ICard>
		</v-form>
	</IModalWindow>
</template>

<script lang='ts' setup>
import {
	Action,
	IActionBtn,
	ICard,
	IDataTableAction,
	IModalWindow,
	ITextInput,
	ValidationRules,
} from '@iqrf/iqrf-vue-ui';
import { mdiPlus } from '@mdi/js';
import { ref, type Ref, watchEffect } from 'vue';
import { VForm } from 'vuetify/components';

import { validateForm } from '@/helpers/validateForm';

interface Props {
	action: Action;
	index?: number;
	topic?: string;
}

const emit = defineEmits(['save']);
const componentProps = defineProps<Props>();
const show: Ref<boolean> = ref(false);
const form: Ref<VForm | null> = ref(null);
const subscriptionTopic: Ref<string> = ref('');

watchEffect((): void => {
	if (componentProps.action === Action.Add) {
		subscriptionTopic.value = '';
	} else if (componentProps.topic !== undefined) {
		subscriptionTopic.value = componentProps.topic;
	}
});
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
