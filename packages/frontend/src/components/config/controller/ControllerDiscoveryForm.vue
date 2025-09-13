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
			<ICard>
				<template #title>
					{{ $t('components.config.controller.form.button.actions.discovery') }}
				</template>
				<NumberInput
					v-model.number='config.maxAddr'
					:label='$t("components.config.controller.form.discovery.maxAddr")'
					required
				/>
				<NumberInput
					v-model.number='config.txPower'
					:label='$t("components.config.controller.form.discovery.txPower")'
				/>
				<v-checkbox
					v-model='config.returnVerbose'
					:label='$t("common.labels.returnVerbose")'
				/>
				<template #actions>
					<CardActionBtn
						:action='Action.Edit'
						type='submit'
					/>
					<v-spacer />
					<ICardActionBtn
						:action='Action.Cancel'
						@click='close'
					/>
				</template>
			</ICard>
		</v-form>
	</ModalWindow>
</template>

<script lang='ts' setup>
import { type IqrfGatewayControllerApiDiscoveryConfig } from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import { Action, ICard, ICardActionBtn } from '@iqrf/iqrf-vue-ui';
import { type PropType, ref , type Ref , watchEffect } from 'vue';
import { VForm } from 'vuetify/components';

import ControllerActionConfigureBtn from '@/components/config/controller/ControllerActionConfigureBtn.vue';
import NumberInput from '@/components/layout/form/NumberInput.vue';
import ModalWindow from '@/components/ModalWindow.vue';
import { validateForm } from '@/helpers/validateForm';

const emit = defineEmits(['saved']);
const componentProps = defineProps({
	discoveryConfig: {
		type: Object as PropType<IqrfGatewayControllerApiDiscoveryConfig>,
		required: true,
	},
});
const show: Ref<boolean> = ref(false);
const form: Ref<VForm | null> = ref(null);
const config: Ref<IqrfGatewayControllerApiDiscoveryConfig | null> = ref(null);

watchEffect(() => {
	config.value = { ...componentProps.discoveryConfig };
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
