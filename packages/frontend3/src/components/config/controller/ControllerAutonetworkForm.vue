<template>
	<v-dialog
		v-model='show'
		:persistent='true'
		scrollable
		no-click-animation
		:width='width'
	>
		<template #activator='{ props }'>
			<v-btn
				v-bind='props'
				color='primary'
			>
				{{ $t('components.configuration.controller.form.button.configure') }}
			</v-btn>
		</template>
		<v-form
			v-if='config !== null'
			ref='form'
			@submit.prevent='onSubmit'
		>
			<Card>
				<template #title>
					{{ $t('components.configuration.controller.form.button.actions.autonetwork') }}
				</template>
				<legend>{{ $t("common.labels.general") }}</legend>
				<TextInput
					v-model.number='config.actionRetries'
					type='number'
					:label='$t("components.configuration.controller.form.autonetwork.actionRetries")'
					required
				/>
				<TextInput
					v-model.number='config.discoveryTxPower'
					type='number'
					:label='$t("components.configuration.controller.form.autonetwork.discoveryTxPower")'
					required
				/>
				<v-checkbox
					v-model='config.discoveryBeforeStart'
					:label='$t("components.configuration.controller.form.autonetwork.discoveryBeforeStart")'
					density='compact'
				/>
				<v-checkbox
					v-model='config.skipDiscoveryEachWave'
					:label='$t("components.configuration.controller.form.autonetwork.skipDiscoveryEachWave")'
					density='compact'
				/>
				<v-checkbox
					v-model='config.returnVerbose'
					:label='$t("common.labels.returnVerbose")'
					density='compact'
				/>
				<legend>{{ $t("components.configuration.controller.form.autonetwork.stopConditions") }}</legend>
				<TextInput
					v-model.number='config.stopConditions.emptyWaves'
					type='number'
					:label='$t("components.configuration.controller.form.autonetwork.emptyWaves")'
					required
				/>
				<TextInput
					v-model.number='config.stopConditions.waves'
					type='number'
					:label='$t("components.configuration.controller.form.autonetwork.waves")'
					required
				/>
				<v-checkbox
					v-model='config.stopConditions.abortOnTooManyNodesFound'
					:label='$t("components.configuration.controller.form.autonetwork.abortOnTooManyNodesFound")'
					density='compact'
				/>
				<template #actions>
					<v-btn
						color='primary'
						variant='elevated'
						@click='onSubmit'
					>
						{{ $t('common.buttons.save') }}
					</v-btn>
					<v-spacer />
					<v-btn
						color='grey-darken-2'
						variant='elevated'
						@click='close'
					>
						{{ $t('common.buttons.close') }}
					</v-btn>
				</template>
			</Card>
		</v-form>
	</v-dialog>
</template>

<script lang='ts' setup>
import { type IqrfGatewayControllerApiAutonetworkConfig } from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import { type Ref, ref , watchEffect , type PropType } from 'vue';
import { VForm } from 'vuetify/components';

import Card from '@/components/Card.vue';
import TextInput from '@/components/TextInput.vue';
import { getModalWidth } from '@/helpers/modal';
import { validateForm } from '@/helpers/validateForm';

const emit = defineEmits(['saved']);
const componentProps = defineProps({
	autonetworkConfig: {
		type: Object as PropType<IqrfGatewayControllerApiAutonetworkConfig>,
		required: true,
	},
});
const show: Ref<boolean> = ref(false);
const width = getModalWidth();
const form: Ref<typeof VForm | null> = ref(null);
const config: Ref<IqrfGatewayControllerApiAutonetworkConfig | null> = ref(null);

watchEffect(() => {
	config.value = {...componentProps.autonetworkConfig};
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