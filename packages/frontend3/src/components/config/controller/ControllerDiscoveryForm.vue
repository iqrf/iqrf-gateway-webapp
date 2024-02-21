<template>
	<ModalWindow v-model='show'>
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
					{{ $t('components.configuration.controller.form.button.actions.discovery') }}
				</template>
				<TextInput
					v-model.number='config.maxAddr'
					type='number'
					:label='$t("components.configuration.controller.form.discovery.maxAddr")'
					required
				/>
				<TextInput
					v-model.number='config.txPower'
					type='number'
					:label='$t("components.configuration.controller.form.discovery.txPower")'
				/>
				<v-checkbox
					v-model='config.returnVerbose'
					:label='$t("common.labels.returnVerbose")'
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
	</ModalWindow>
</template>

<script lang='ts' setup>
import { type IqrfGatewayControllerApiDiscoveryConfig } from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import { type Ref, ref , watchEffect , type PropType } from 'vue';
import { VForm } from 'vuetify/components';

import Card from '@/components/Card.vue';
import ModalWindow from '@/components/ModalWindow.vue';
import TextInput from '@/components/TextInput.vue';
import { validateForm } from '@/helpers/validateForm';

const emit = defineEmits(['saved']);
const componentProps = defineProps({
	discoveryConfig: {
		type: Object as PropType<IqrfGatewayControllerApiDiscoveryConfig>,
		required: true,
	},
});
const show: Ref<boolean> = ref(false);
const form: Ref<typeof VForm | null> = ref(null);
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
