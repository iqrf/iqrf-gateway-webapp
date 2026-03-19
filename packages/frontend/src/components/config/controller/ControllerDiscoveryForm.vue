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
				v-bind='props'
				:action='Action.Custom'
				:icon='mdiWrench'
				:text='$t("components.config.controller.form.button.configure")'
			/>
		</template>
		<v-form
			v-if='config !== null'
			ref='form'
			v-slot='{ isValid }'
			@submit.prevent='onSubmit()'
		>
			<ICard>
				<template #title>
					{{ $t('components.config.controller.form.button.actions.discovery') }}
				</template>
				<INumberInput
					v-model='config.maxAddr'
					:label='$t("components.iqrfnet.network-manager.discovery.maxAddr")'
					:rules='[
						(v: number|null) => ValidationRules.required(v, $t("components.config.controller.validation.maxAddr.required")),
						(v: number) => ValidationRules.integer(v, $t("components.config.controller.validation.maxAddr.integer")),
						(v: number) => ValidationRules.between(v, 0, 239, $t("components.config.controller.validation.maxAddr.between")),
					]'
					:min='0'
					:max='239'
					required
					:prepend-inner-icon='mdiNumeric'
				/>
				<INumberInput
					v-model='config.txPower'
					:label='$t("components.iqrfnet.network-manager.discovery.txPower")'
					:rules='[
						(v: number|null) => ValidationRules.required(v, $t("components.config.controller.validation.txPower.required")),
						(v: number) => ValidationRules.integer(v, $t("components.config.controller.validation.txPower.integer")),
						(v: number) => ValidationRules.between(v, 0, 7, $t("components.config.controller.validation.txPower.between")),
					]'
					:min='0'
					:max='7'
					required
					:prepend-inner-icon='mdiSignalVariant'
				/>
				<v-checkbox
					v-model='config.returnVerbose'
					:label='$t("common.labels.returnVerbose")'
					hide-details
					density='compact'
				/>
				<template #actions>
					<IActionBtn
						:action='Action.Edit'
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
import { type IqrfGatewayControllerApiDiscoveryConfig } from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import { Action, IActionBtn, ICard, IModalWindow, INumberInput, ValidationRules } from '@iqrf/iqrf-vue-ui';
import { mdiNumeric, mdiSignalVariant, mdiWrench } from '@mdi/js';
import {
	ref,
	type Ref,
	type TemplateRef,
	useTemplateRef,
	watch,
} from 'vue';
import { VForm } from 'vuetify/components';

import { validateForm } from '@/helpers/validateForm';

const componentProps = defineProps<{
	discoveryConfig: IqrfGatewayControllerApiDiscoveryConfig;
}>();
const emit = defineEmits<{
	saved: [config: IqrfGatewayControllerApiDiscoveryConfig];
}>();
const show: Ref<boolean> = ref(false);
const form: TemplateRef<VForm> = useTemplateRef('form');
const config: Ref<IqrfGatewayControllerApiDiscoveryConfig | null> = ref(null);

watch(show, (newVal: boolean): void => {
	if (newVal) {
		config.value = { ...componentProps.discoveryConfig };
	}
});

async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value) || config.value === null) {
		return;
	}
	emit('saved', config.value);
	close();
}

function close(): void {
	show.value = false;
}
</script>
