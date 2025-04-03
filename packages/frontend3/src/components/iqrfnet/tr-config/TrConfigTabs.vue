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
	<v-form
		v-slot='{ isValid }'
		ref='form'
	>
		<Card>
			<template #title>
				{{ $t('pages.iqrfnet.tr-config.title') }}
			</template>
			<NumberInput
				v-model='addr'
				:label='$t("components.iqrfnet.common.deviceAddr")'
			/>
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
import { IqmeshService } from '@iqrf/iqrf-gateway-daemon-utils/services';
import { type ApiResponseIqmesh,
	type IqmeshEnumerateDeviceResult,
	type IqmeshWriteTrConfParams,
} from '@iqrf/iqrf-gateway-daemon-utils/types';
import { DaemonMessageOptions } from '@iqrf/iqrf-gateway-daemon-utils/utils';
import { mdiContentSave } from '@mdi/js';
import { ref, type Ref } from 'vue';
import { VForm } from 'vuetify/components';

import TrConfigDpaForm from '@/components/iqrfnet/tr-config/TrConfigDpaForm.vue';
import TrConfigOsForm from '@/components/iqrfnet/tr-config/TrConfigOsForm.vue';
import TrConfigSecurityForm from '@/components/iqrfnet/tr-config/TrConfigSecurityForm.vue';
import Card from '@/components/layout/card/Card.vue';
import NumberInput from '@/components/layout/form/NumberInput.vue';
import { validateForm } from '@/helpers/validateForm';
import { useDaemonStore } from '@/store/daemonSocket';
import { ComponentState } from '@/types/ComponentState';

const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const daemonSocket = useDaemonStore();
const msgId: Ref<string | null> = ref(null);
const addr: Ref<number> = ref(0);
const tab: Ref<number> = ref(0);
const form: Ref<VForm | null> = ref(null);
const config: Ref<IqmeshWriteTrConfParams> = ref({
	deviceAddr: 0,
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
	uartBaudrate: 9_600,
	accessPassword: '', // Security
	securityUserKey: '',
});

async function enumerate(): Promise<void> {
	const opts = new DaemonMessageOptions(
		60_000,
		'todo',
		() => {
			msgId.value = null;
		},
	);
	msgId.value = await daemonSocket.sendMessage(IqmeshService.enumerate({}, { deviceAddr: addr.value }, opts));
}

function handleEnumerate(rsp: ApiResponseIqmesh<IqmeshEnumerateDeviceResult>): void {
	switch (rsp.data.status) {
		case 0:
			config.value = { ...config.value, ...rsp.data.rsp.trConfiguration };
			break;
	}
}

async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value) || config.value === null) {
		return;
	}
	console.warn(config.value);
}

</script>
