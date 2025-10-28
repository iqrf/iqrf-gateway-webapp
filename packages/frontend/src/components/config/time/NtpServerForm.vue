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
				:disabled='disabled'
				:tooltip='$t("components.config.time.ntpServers.actions.add")'
			/>
			<IDataTableAction
				v-else
				v-bind='props'
				:action='action'
				:disabled='disabled'
				:tooltip='$t("components.config.time.ntpServers.actions.edit")'
			/>
		</template>
		<v-form ref='form' v-slot='{ isValid }' @submit.prevent='onSubmit()'>
			<ICard :action='action'>
				<template #title>
					{{ cardTitle }}
				</template>
				<ITextInput
					v-model='ntpServer'
					label='NTP server address'
					:prepend-inner-icon='mdiServerNetwork'
					:rules='[
						(v: string|null) => ValidationRules.required(v, $t("components.config.time.ntpServers.validation.server.required")),
						(v: string) => ValidationRules.host(v, $t("components.config.time.ntpServers.validation.server.invalid")),
					]'
					required
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
import { mdiServerNetwork } from '@mdi/js';
import {
	computed, PropType, ref, type Ref,
	type TemplateRef, useTemplateRef, watch,
} from 'vue';
import { useI18n } from 'vue-i18n';
import { VForm } from 'vuetify/components';

import { validateForm } from '@/helpers/validateForm';

const componentProps = defineProps({
	action: {
		type: String as PropType<Action>,
		required: true,
	},
	index: {
		type: Number,
		required: false,
		default: undefined,
	},
	server: {
		type: String,
		required: false,
		default: undefined,
	},
	disabled: {
		type: Boolean,
		required: false,
		default: false,
	},
});
const emit = defineEmits<{
	save: [index: number|undefined, server: string];
}>();
const i18n = useI18n();
const show: Ref<boolean> = ref(false);
const form: TemplateRef<VForm> = useTemplateRef('form');
const ntpServer: Ref<string> = ref('');

watch(show, (newVal: boolean): void => {
	if (!newVal) {
		return;
	}
	if (componentProps.action === Action.Add) {
		ntpServer.value = '';
	} else if (componentProps.server !== undefined) {
		ntpServer.value = componentProps.server;
	}
});

const cardTitle = computed(() => {
	if (componentProps.action === Action.Add) {
		return i18n.t('components.config.time.ntpServers.actions.add');
	}
	return i18n.t('components.config.time.ntpServers.actions.edit');
});

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
