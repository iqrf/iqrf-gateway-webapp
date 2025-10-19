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
					{{ $t('components.config.controller.form.button.actions.autonetwork') }}
				</template>
				<legend>{{ $t("common.labels.general") }}</legend>
				<INumberInput
					v-model='config.actionRetries'
					:label='$t("components.config.controller.form.autonetwork.actionRetries")'
					:rules='[
						(v: number|null) => ValidationRules.required(v, $t("components.config.controller.validation.actionRetries.required")),
						(v: number) => ValidationRules.integer(v, $t("components.config.controller.validation.actionRetries.integer")),
						(v: number) => ValidationRules.between(v, 0, 3, $t("components.config.controller.validation.actionRetries.between")),
					]'
					:min='0'
					:max='3'
					required
					:prepend-inner-icon='mdiRepeat'
				/>
				<INumberInput
					v-model='config.discoveryTxPower'
					:label='$t("components.config.controller.form.autonetwork.discoveryTxPower")'
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
					v-model='config.discoveryBeforeStart'
					:label='$t("components.config.controller.form.autonetwork.discoveryBeforeStart")'
					hide-details
					density='compact'
				/>
				<v-checkbox
					v-model='config.skipDiscoveryEachWave'
					:label='$t("components.config.controller.form.autonetwork.skipDiscoveryEachWave")'
					hide-details
					density='compact'
				/>
				<v-checkbox
					v-model='config.returnVerbose'
					:label='$t("common.labels.returnVerbose")'
					hide-details
					density='compact'
				/>
				<legend>{{ $t("components.config.controller.form.autonetwork.stopConditions") }}</legend>
				<INumberInput
					v-model='config.stopConditions.emptyWaves'
					:label='$t("components.config.controller.form.autonetwork.emptyWaves")'
					:rules='[
						(v: number|null) => ValidationRules.required(v, $t("components.config.controller.validation.emptyWaves.required")),
						(v: number) => ValidationRules.integer(v, $t("components.config.controller.validation.emptyWaves.integer")),
						(v: number) => ValidationRules.between(v, 1, 127, $t("components.config.controller.validation.emptyWaves.between")),
					]'
					:min='1'
					:max='127'
					required
					:prepend-inner-icon='mdiRefresh'
				/>
				<INumberInput
					v-model='config.stopConditions.waves'
					:label='$t("components.config.controller.form.autonetwork.waves")'
					:rules='[
						(v: number|null) => ValidationRules.required(v, $t("components.config.controller.validation.waves.required")),
						(v: number) => ValidationRules.integer(v, $t("components.config.controller.validation.waves.integer")),
						(v: number) => ValidationRules.between(v, 1, 127, $t("components.config.controller.validation.waves.between")),
					]'
					:min='1'
					:max='127'
					required
					:prepend-inner-icon='mdiRefreshCircle'
				/>
				<v-checkbox
					v-model='config.stopConditions.abortOnTooManyNodesFound'
					:label='$t("components.config.controller.form.autonetwork.abortOnTooManyNodesFound")'
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
import { type IqrfGatewayControllerApiAutonetworkConfig } from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import { Action, IActionBtn, ICard, IModalWindow, INumberInput, ValidationRules } from '@iqrf/iqrf-vue-ui';
import { mdiRefresh, mdiRefreshCircle, mdiRepeat, mdiSignalVariant, mdiWrench } from '@mdi/js';
import { type PropType, ref, type Ref, useTemplateRef, watch } from 'vue';
import { VForm } from 'vuetify/components';

import { validateForm } from '@/helpers/validateForm';

const emit = defineEmits<{
	saved: [config: IqrfGatewayControllerApiAutonetworkConfig];
}>();
const componentProps = defineProps({
	autonetworkConfig: {
		type: Object as PropType<IqrfGatewayControllerApiAutonetworkConfig>,
		required: true,
	},
});
const show: Ref<boolean> = ref(false);
const form: Ref<VForm | null> = useTemplateRef('form');
const config: Ref<IqrfGatewayControllerApiAutonetworkConfig | null> = ref(null);

watch(show, (newVal: boolean): void => {
	if (newVal) {
		config.value = structuredClone(componentProps.autonetworkConfig);
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

<style lang='scss' scoped>
legend {
	font-size: large;
	font-weight: 300;
	padding-bottom: 1em;
}

</style>
