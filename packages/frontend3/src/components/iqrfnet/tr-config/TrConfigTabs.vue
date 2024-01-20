<template>
	<v-form
		v-slot='{ isValid }'
		ref='form'
	>
		<Card>
			<template #title>
				{{ $t('pages.iqrfnet.tr-config.title') }}
			</template>
			<v-tabs v-model='tab'>
				<v-tab :value='0'>
					{{ $t('components.iqrfnet.common.os') }}
				</v-tab>
				<v-tab :value='1'>
					{{ $t('components.iqrfnet.common.dpa') }}
				</v-tab>
				<v-tab :value='2'>
					{{ $t('components.iqrfnet.tr-config.security.title') }}
				</v-tab>
			</v-tabs>
			<v-window v-model='tab'>
				<v-window-item :value='0'>
					<TrConfigOsForm :config='config' />
				</v-window-item>
				<v-window-item :value='1'>
					<TrConfigDpaForm :config='config' />
				</v-window-item>
				<v-window-item :value='2'>
					<TrConfigSecurityForm :config='config' />
				</v-window-item>
			</v-window>
			<template #actions>
				<v-btn
					color='primary'
					variant='elevated'
					:disabled='!isValid.value || [ComponentState.Loading, ComponentState.Reloading, ComponentState.Saving].includes(componentState)'
					@click='onSubmit'
				>
					<v-icon :icon='mdiContentSave' />
					{{ $t('common.buttons.save') }}
				</v-btn>
			</template>
		</Card>
	</v-form>
</template>

<script lang='ts' setup>
import { type TrConfig } from '@iqrf/iqrf-gateway-daemon-utils/types';
import { mdiContentSave } from '@mdi/js';
import { ref, type Ref } from 'vue';
import { VForm } from 'vuetify/components';

import Card from '@/components/Card.vue';
import TrConfigDpaForm from '@/components/iqrfnet/tr-config/TrConfigDpaForm.vue';
import TrConfigOsForm from '@/components/iqrfnet/tr-config/TrConfigOsForm.vue';
import TrConfigSecurityForm from '@/components/iqrfnet/tr-config/TrConfigSecurityForm.vue';
import { validateForm } from '@/helpers/validateForm';
import {ComponentState} from '@/types/ComponentState';


const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const tab: Ref<number> = ref(0);
const form: Ref<typeof VForm | null> = ref(null);
const config: Ref<TrConfig> = ref({
	rfBand: '868', // OS RF
	rfChannelA: 52,
	rfChannelB: 2,
	rfSubChannelA: 1,
	rfSubChannelB: 1,
	rfPgmEnableAfterReset: false, // OS RFPGM
	rfPgmTerminateAfter1Min: true,
	rfPgmTerminateMcuPin: true,
	rfPgmDualChannel: true,
	rfPgmLpMode: false,
	rfPgmIncorrectUpload: false,
	embPers: { // DPA embedded peripherals
		coordinator: false,
		eeprom: false,
		eeeprom: false,
		io: false,
		ledg: false,
		ledr: false,
		node: false,
		os: false,
		pwm: false,
		ram: false,
		spi: false,
		thermometer: false,
		uart: false,
	},
	stdAndLpNetwork: true, // DPA RF
	txPower: 7,
	rxFilter: 5,
	lpRxTimeout: 6,
	rfAltDsmChannel: 0,
	customDpaHandler: false, // DPA other
	dpaPeerToPeer: false,
	peerToPeer: false,
	dpaAutoexec: false,
	localFrcReception: false,
	ioSetup: false,
	routingOff: false,
	nodeDpaInterface: false,
	neverSleep: false,
	uartBaudrate: 9600,
	accessPassword: '', // Security
	securityUserKey: '',
});

async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value) || config.value === null) {
		return;
	}
}

</script>
