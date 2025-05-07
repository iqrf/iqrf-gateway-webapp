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
	<ModalWindow
		v-model='show'
	>
		<template #activator='{ props }'>
			<v-btn
				v-if='action === Action.Add'
				v-bind='props'
				color='green'
				:icon='mdiPlus'
			/>
			<DataTableAction
				v-else
				:action='action'
			/>
		</template>
		<v-form ref='form' v-slot='{ isValid }' @submit.prevent='onSubmit'>
			<Card :action='action'>
				<template #title>
					{{ $t(`components.config.time.ntpServers.${action}`) }}
				</template>
				<TextInput
					v-model='ntpServer'
					label='NTP server address'
					:prepend-inner-icon='mdiServerNetwork'
					:rules='[
						(v: string|null) => ValidationRules.required(v, $t("components.config.time.ntpServers.validation.serverMissing")),
						(v: string|null) => ValidationRules.server(v, $t("components.config.time.ntpServers.validation.serverInvalid")),
					]'
					required
				/>
				<template #actions>
					<CardActionBtn
						:action='action'
						:disabled='!isValid.value'
						type='submit'
					/>
					<v-spacer />
					<CardActionBtn :action='Action.Cancel' @click='close' />
				</template>
			</Card>
		</v-form>
	</ModalWindow>
</template>

<script lang='ts' setup>
import { mdiPlus, mdiServerNetwork } from '@mdi/js';
import { ref, type Ref, watchEffect } from 'vue';
import { VForm } from 'vuetify/components';

import Card from '@/components/layout/card/Card.vue';
import CardActionBtn from '@/components/layout/card/CardActionBtn.vue';
import DataTableAction from '@/components/layout/data-table/DataTableAction.vue';
import TextInput from '@/components/layout/form/TextInput.vue';
import ModalWindow from '@/components/ModalWindow.vue';
import { validateForm } from '@/helpers/validateForm';
import ValidationRules from '@/helpers/ValidationRules';
import { Action } from '@/types/Action';

interface Props {
	action: Action;
	index?: number;
	server?: string;
}

const emit = defineEmits(['save']);
const componentProps = defineProps<Props>();
const show: Ref<boolean> = ref(false);
const form: Ref<VForm | null> = ref(null);
const ntpServer: Ref<string> = ref('');

watchEffect((): void => {
	if (componentProps.action === Action.Add) {
		ntpServer.value = '';
	} else if (componentProps.server !== undefined) {
		ntpServer.value = componentProps.server;
	}
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
