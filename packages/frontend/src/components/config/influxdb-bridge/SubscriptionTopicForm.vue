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
				:action='Action.Add'
				container-type='card-title'
				:tooltip='$t("components.config.influxdb-bridge.topics.actions.add")'
				:disabled='disabled'
			/>
			<IDataTableAction
				v-if='action === Action.Edit'
				v-bind='props'
				:action='action'
				:tooltip='$t("components.config.influxdb-bridge.topics.actions.edit")'
				:disabled='disabled'
			/>
		</template>
		<v-form
			ref='form'
			v-slot='{ isValid }'
			@submit.prevent='onSubmit()'
		>
			<ICard :action='action'>
				<template #title>
					{{ titleText }}
				</template>
				<ITextInput
					v-model='subscriptionTopic'
					:label='$t("components.config.influxdb-bridge.topics.topic")'
					:rules='[
						(v: string|null) => ValidationRules.required(v, $t("components.config.influxdb-bridge.validation.topic.required")),
					]'
					required
					:prepend-inner-icon='mdiForum'
				/>
				<template #actions>
					<IActionBtn
						:action='action'
						:disabled='!isValid.value'
						type='submit'
					/>
					<v-spacer />
					<IActionBtn
						:action='Action.Cancel'
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
import { mdiForum } from '@mdi/js';
import { computed, ref, type Ref, useTemplateRef, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { VForm } from 'vuetify/components';

import { validateForm } from '@/helpers/validateForm';

interface Props {
	action: Action;
	index?: number;
	topic?: string;
	disabled?: boolean;
}

const emit = defineEmits<{
	save: [index: number|undefined, topic: string];
}>();
const i18n = useI18n();
const componentProps = defineProps<Props>();
const show: Ref<boolean> = ref(false);
const form: Ref<VForm | null> = useTemplateRef('form');
const subscriptionTopic: Ref<string> = ref('');

const titleText = computed(() => {
	if (componentProps.action === Action.Add) {
		return i18n.t('components.config.influxdb-bridge.topics.actions.add');
	}
	return i18n.t('components.config.influxdb-bridge.topics.actions.edit');
});

watch(show, (newVal: boolean): void => {
	if (!newVal) {
		return;
	}
	if (componentProps.action === Action.Add) {
		subscriptionTopic.value = '';
	} else {
		subscriptionTopic.value = componentProps.topic ?? '';
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
