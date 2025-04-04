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
	<ModalWindow v-model='show'>
		<template #activator='{ props }'>
			<ControllerActionConfigureBtn v-bind='props' />
		</template>
		<v-form
			v-if='config !== null'
			ref='form'
			@submit.prevent='onSubmit'
		>
			<Card>
				<template #title>
					{{ $t('components.config.controller.form.button.actions.autonetwork') }}
				</template>
				<legend>{{ $t("common.labels.general") }}</legend>
				<NumberInput
					v-model.number='config.actionRetries'
					:label='$t("components.config.controller.form.autonetwork.actionRetries")'
					required
				/>
				<NumberInput
					v-model.number='config.discoveryTxPower'
					:label='$t("components.config.controller.form.autonetwork.discoveryTxPower")'
					required
				/>
				<v-checkbox
					v-model='config.discoveryBeforeStart'
					:label='$t("components.config.controller.form.autonetwork.discoveryBeforeStart")'
				/>
				<v-checkbox
					v-model='config.skipDiscoveryEachWave'
					:label='$t("components.config.controller.form.autonetwork.skipDiscoveryEachWave")'
				/>
				<v-checkbox
					v-model='config.returnVerbose'
					:label='$t("common.labels.returnVerbose")'
				/>
				<legend>{{ $t("components.config.controller.form.autonetwork.stopConditions") }}</legend>
				<NumberInput
					v-model.number='config.stopConditions.emptyWaves'
					:label='$t("components.config.controller.form.autonetwork.emptyWaves")'
					required
				/>
				<NumberInput
					v-model.number='config.stopConditions.waves'
					:label='$t("components.config.controller.form.autonetwork.waves")'
					required
				/>
				<v-checkbox
					v-model='config.stopConditions.abortOnTooManyNodesFound'
					:label='$t("components.config.controller.form.autonetwork.abortOnTooManyNodesFound")'
				/>
				<template #actions>
					<CardActionBtn
						:action='Action.Edit'
						type='submit'
					/>
					<v-spacer />
					<CardActionBtn
						:action='Action.Cancel'
						@click='close'
					/>
				</template>
			</Card>
		</v-form>
	</ModalWindow>
</template>

<script lang='ts' setup>
import { type IqrfGatewayControllerApiAutonetworkConfig } from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import { type PropType, ref , type Ref , watchEffect } from 'vue';
import { VForm } from 'vuetify/components';

import ControllerActionConfigureBtn from '@/components/config/controller/ControllerActionConfigureBtn.vue';
import Card from '@/components/layout/card/Card.vue';
import CardActionBtn from '@/components/layout/card/CardActionBtn.vue';
import NumberInput from '@/components/layout/form/NumberInput.vue';
import ModalWindow from '@/components/ModalWindow.vue';
import { validateForm } from '@/helpers/validateForm';
import { Action } from '@/types/Action';

const emit = defineEmits(['saved']);
const componentProps = defineProps({
	autonetworkConfig: {
		type: Object as PropType<IqrfGatewayControllerApiAutonetworkConfig>,
		required: true,
	},
});
const show: Ref<boolean> = ref(false);
const form: Ref<VForm | null> = ref(null);
const config: Ref<IqrfGatewayControllerApiAutonetworkConfig | null> = ref(null);

watchEffect(() => {
	config.value = { ...componentProps.autonetworkConfig };
});

async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value)) {
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
