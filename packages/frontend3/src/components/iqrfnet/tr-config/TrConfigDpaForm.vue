<!--
Copyright 2017-2024 IQRF Tech s.r.o.
Copyright 2019-2024 MICRORISC s.r.o.

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
	<Card>
		<v-row>
			<v-col>
				<legend class='section-legend'>
					{{ $t('components.iqrfnet.common.embedPeripherals') }}
				</legend>
				<v-checkbox
					v-model='config.embPers.eeprom'
					:label='$t("components.iqrfnet.common.peripherals.eeprom")'
					density='compact'
					hide-details
				/>
				<v-checkbox
					v-model='config.embPers.eeeprom'
					:label='$t("components.iqrfnet.common.peripherals.eeeprom")'
					density='compact'
					hide-details
				/>
				<v-checkbox
					v-model='config.embPers.ram'
					:label='$t("components.iqrfnet.common.peripherals.ram")'
					density='compact'
					hide-details
				/>
				<v-checkbox
					v-model='config.embPers.ledr'
					:label='$t("components.iqrfnet.common.peripherals.ledr")'
					density='compact'
					hide-details
				/>
				<v-checkbox
					v-model='config.embPers.ledg'
					:label='$t("components.iqrfnet.common.peripherals.ledg")'
					density='compact'
					hide-details
				/>
				<v-checkbox
					v-model='config.embPers.spi'
					:label='$t("components.iqrfnet.common.peripherals.spi")'
					density='compact'
					hide-details
				/>
				<v-checkbox
					v-model='config.embPers.io'
					:label='$t("components.iqrfnet.common.peripherals.io")'
					density='compact'
					hide-details
				/>
				<v-checkbox
					v-model='config.embPers.thermometer'
					:label='$t("components.iqrfnet.common.peripherals.thermometer")'
					density='compact'
					hide-details
				/>
				<v-checkbox
					v-model='config.embPers.uart'
					:label='$t("components.iqrfnet.common.peripherals.uart")'
					density='compact'
					hide-details
				/>
			</v-col>
			<v-divider :vertical='!display.mobile.value' />
			<v-col>
				<legend class='section-legend'>
					{{ $t('common.labels.other') }}
				</legend>
				<v-checkbox
					v-model='config.customDpaHandler'
					:label='$t("components.iqrfnet.tr-config.dpa.other.customDpaHandler")'
					density='compact'
					hide-details
				/>
				<v-checkbox
					v-model='config.dpaPeerToPeer'
					:label='$t("components.iqrfnet.tr-config.dpa.other.dp2p")'
					density='compact'
					hide-details
				/>
				<v-checkbox
					v-model='config.peerToPeer'
					:label='$t("components.iqrfnet.tr-config.dpa.other.up2p")'
					density='compact'
					hide-details
				/>
				<v-checkbox
					v-model='config.localFrcReception'
					:label='$t("components.iqrfnet.tr-config.dpa.other.localFrc")'
					density='compact'
					hide-details
				/>
				<v-checkbox
					v-model='config.ioSetup'
					:label='$t("components.iqrfnet.tr-config.dpa.other.ioSetup")'
					density='compact'
					hide-details
				/>
				<v-checkbox
					v-model='config.dpaAutoexec'
					:label='$t("components.iqrfnet.tr-config.dpa.other.autoexec")'
					density='compact'
					hide-details
				/>
				<v-checkbox
					v-model='config.routingOff'
					:label='$t("components.iqrfnet.tr-config.dpa.other.routingOff")'
					density='compact'
					hide-details
				/>
				<v-checkbox
					v-model='config.neverSleep'
					:label='$t("components.iqrfnet.tr-config.dpa.other.neverSleep")'
					density='compact'
					hide-details
				/>
				<SelectInput
					v-model='config.uartBaudrate'
					:label='$t("components.iqrfnet.tr-config.dpa.other.baudRate")'
					:items='baudRateOptions'
				/>
			</v-col>
		</v-row>
		<v-row>
			<v-divider />
			<v-col cols='6'>
				<legend class='section-legend'>
					{{ $t('components.iqrfnet.common.rf') }}
				</legend>
				<SelectInput
					v-model='config.stdAndLpNetwork'
					:label='$t("components.iqrfnet.tr-config.dpa.rf.networkType")'
					:items='networkTypeOptions'
				/>
				<NumberInput
					v-model='config.txPower'
					:label='$t("components.iqrfnet.common.txPower")'
				/>
				<NumberInput
					v-model='config.rxFilter'
					:label='$t("components.iqrfnet.common.rxFilter")'
				/>
				<NumberInput
					v-model='config.lpRxTimeout'
					:label='$t("components.iqrfnet.tr-config.dpa.rf.lpRxTimeout")'
				/>
				<NumberInput
					v-model='config.rfAltDsmChannel'
					:label='$t("components.iqrfnet.tr-config.dpa.rf.altDsmChannel")'
				/>
			</v-col>
			<v-divider :vertical='!display.mobile.value' />
		</v-row>
	</Card>
</template>

<script lang='ts' setup>
import {
	IqmeshTrConfigParams,
} from '@iqrf/iqrf-gateway-daemon-utils/types/iqmesh';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useDisplay } from 'vuetify';

import Card from '@/components/layout/card/Card.vue';
import NumberInput from '@/components/layout/form/NumberInput.vue';
import SelectInput from '@/components/layout/form/SelectInput.vue';

const i18n = useI18n();
const display = useDisplay();
const config = defineModel<IqmeshTrConfigParams>('config', { required: true });

const baudRateOptions = computed(() => {
	const items: number[] = [1_200, 2_400, 4_800, 9_600, 19_200, 38_400, 57_600, 115_200, 230_400];
	return items.map((item: number) => ({
		title: `${item} Bd`,
		value: item,
	}));
});

const networkTypeOptions = [
	{
		value: false,
		title: i18n.t('components.iqrfnet.tr-config.dpa.rf.networkTypes.std'),
	},
	{
		value: true,
		title: i18n.t('components.iqrfnet.tr-config.dpa.rf.networkTypes.stdLp'),
	},
];
</script>
