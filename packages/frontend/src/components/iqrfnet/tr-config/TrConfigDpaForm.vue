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
	<ICard>
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
					:label='`${$t("components.iqrfnet.common.peripherals.spi")} (4, 7, 9)`'
					density='compact'
					hide-details
					:disabled='config.deviceAddr === 0 || config.embPers.uart || compare(dpaVersion, "4.14", ">")'
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
					:label='`${$t("components.iqrfnet.common.peripherals.uart")} (7, 9)`'
					density='compact'
					hide-details
					:disabled='config.deviceAddr === 0 || config.embPers.spi'
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
					:disabled='!dpaHandlerDetected'
				/>
				<v-checkbox
					v-model='config.dpaPeerToPeer'
					:label='`${$t("components.iqrfnet.tr-config.dpa.other.dp2p")} (2, 7)`'
					density='compact'
					hide-details
					:disabled='config.deviceAddr === 0 || compare(dpaVersion, "4.10", "<")'
				/>
				<v-checkbox
					v-model='config.peerToPeer'
					:label='$t("components.iqrfnet.tr-config.dpa.other.up2p")'
					density='compact'
					hide-details
				/>
				<v-checkbox
					v-model='config.localFrcReception'
					:label='`${$t("components.iqrfnet.tr-config.dpa.other.localFrc")} (3, 7)`'
					density='compact'
					hide-details
					:disabled='config.deviceAddr === 0 || compare(dpaVersion, "4.15", "<")'
				/>
				<v-checkbox
					v-model='config.ioSetup'
					:label='$t("components.iqrfnet.tr-config.dpa.other.ioSetup")'
					density='compact'
					hide-details
				/>
				<v-checkbox
					v-model='config.dpaAutoexec'
					:label='`${$t("components.iqrfnet.tr-config.dpa.other.autoexec")} (4)`'
					density='compact'
					hide-details
					:disabled='compare(dpaVersion, "4.14", ">")'
				/>
				<v-checkbox
					v-model='config.routingOff'
					:label='$t("components.iqrfnet.tr-config.dpa.other.routingOff")'
					density='compact'
					hide-details
					:disabled='config.deviceAddr === 0'
				/>
				<v-checkbox
					v-model='config.neverSleep'
					:label='`${$t("components.iqrfnet.tr-config.dpa.other.neverSleep")} (1, 7)`'
					density='compact'
					hide-details
					:disabled='config.deviceAddr === 0 || compare(dpaVersion, "3.03", "<")'
				/>
				<v-checkbox
					v-model='config.nodeDpaInterface'
					:label='`${$t("components.iqrfnet.tr-config.dpa.other.nodeDpaInterface")} (2)`'
					density='compact'
					hide-details
					:disabled='compare(dpaVersion, "4.00", ">=")'
				/>
				<ISelectInput
					v-model='config.uartBaudrate'
					:label='`${$t("components.iqrfnet.tr-config.dpa.other.baudRate")} (8)`'
					:items='baudRateOptions'
				/>
			</v-col>
		</v-row>
		<v-row>
			<v-divider />
			<v-col>
				<legend class='section-legend'>
					{{ $t('components.iqrfnet.common.rf') }}
				</legend>
				<v-alert
					v-if='!config.stdAndLpNetwork'
					class='mb-4'
					type='warning'
					variant='tonal'
					:text='$t("components.iqrfnet.tr-config.dpa.notes.interoperability")'
				/>
				<ISelectInput
					v-model='config.stdAndLpNetwork'
					:label='`${$t("components.iqrfnet.tr-config.dpa.rf.networkType")} (6)`'
					:items='networkTypeOptions'
					:disabled='config.deviceAddr !== 0'
				/>
				<INumberInput
					v-model='config.txPower'
					:label='$t("components.iqrfnet.common.txPower")'
					:rules='[
						(v: number|null) => ValidationRules.required(v, $t("components.iqrfnet.common.validation.txPower.required")),
						(v: number) => ValidationRules.integer(v, $t("components.iqrfnet.common.validation.txPower.integer")),
						(v: number) => ValidationRules.between(v, 0, 7, $t("components.iqrfnet.common.validation.txPower.between")),
					]'
					:min='0'
					:max='7'
					required
				/>
				<INumberInput
					v-model='config.rxFilter'
					:label='$t("components.iqrfnet.common.rxFilter")'
					:rules='[
						(v: number|null) => ValidationRules.required(v, $t("components.iqrfnet.common.validation.rxFilter.required")),
						(v: number) => ValidationRules.integer(v, $t("components.iqrfnet.common.validation.rxFilter.integer")),
						(v: number) => ValidationRules.between(v, 0, 64, $t("components.iqrfnet.common.validation.rxFilter.between")),
					]'
					:min='0'
					:max='64'
					required
				/>
				<INumberInput
					v-model='config.lpRxTimeout'
					:label='`${$t("components.iqrfnet.tr-config.dpa.rf.lpRxTimeout")} (7)`'
					:rules='[
						(v: number|null) => ValidationRules.required(v, $t("components.iqrfnet.common.validation.lpRxTimeout.required")),
						(v: number) => ValidationRules.integer(v, $t("components.iqrfnet.common.validation.lpRxTimeout.integer")),
						(v: number) => ValidationRules.between(v, 1, 255, $t("components.iqrfnet.common.validation.lpRxTimeout.between")),
					]'
					:min='1'
					:max='255'
					required
					:disabled='config.deviceAddr === 0'
				/>
				<INumberInput
					v-model='config.rfAltDsmChannel'
					:label='`${$t("components.iqrfnet.tr-config.dpa.rf.altDsmChannel")} (5)`'
					:rules='rfChannelRules'
					:min='0'
					:max='rfChannelMax'
					required
				/>
			</v-col>
			<v-divider :vertical='!display.mobile.value' />
			<v-col>
				<div>
					<em>
						{{ $t('components.iqrfnet.tr-config.dpa.notes.valid303Above') }}<br>
						{{ $t('components.iqrfnet.tr-config.dpa.notes.valid410Above') }}<br>
						{{ $t('components.iqrfnet.tr-config.dpa.notes.valid415Above') }}<br>
						{{ $t('components.iqrfnet.tr-config.dpa.notes.valid414Below') }}<br>
						{{ $t('components.iqrfnet.tr-config.dpa.notes.channelNote') }}<br>
						{{ $t('components.iqrfnet.tr-config.dpa.notes.coordinatorOnly') }}<br>
						{{ $t('components.iqrfnet.tr-config.dpa.notes.nodeOnly') }}<br>
						{{ $t('components.iqrfnet.tr-config.dpa.notes.uartOnly') }}<br>
						{{ $t('components.iqrfnet.tr-config.dpa.notes.exclusive') }}
					</em>
				</div>
			</v-col>
		</v-row>
	</ICard>
</template>

<script lang='ts' setup>
import { ICard, INumberInput, ISelectInput, ValidationRules } from '@iqrf/iqrf-vue-ui';
import { compare } from 'compare-versions';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useDisplay } from 'vuetify';

import { TrConfiguration } from '@/types/DaemonApi/Iqmesh';

const i18n = useI18n();
const display = useDisplay();
const config = defineModel<TrConfiguration & { deviceAddr: number }>('config', { required: true });
defineProps({
	dpaVersion: {
		type: String,
		required: true,
	},
	dpaHandlerDetected: {
		type: Boolean,
		required: true,
	},
});

const rfChannelMax = computed(() => {
	if (config.value.rfBand === '433') {
		return 16;
	} else if (config.value.rfBand === '868') {
		return 67;
	} else {
		return 255;
	}
});

const rfChannelRules = computed(() => {
	const rules = [
		(v: number|null) => ValidationRules.required(v, i18n.t('components.iqrfnet.common.validation.rfChannel.required')),
		(v: number) => ValidationRules.integer(v, i18n.t('components.iqrfnet.common.validation.rfChannel.integer')),
	];
	if (config.value.rfBand === '433') {
		rules.push(
			(v: number) => ValidationRules.between(v, 0, 16, i18n.t('components.iqrfnet.common.validation.rfChannel.between433')),
		);
	} else if (config.value.rfBand === '868') {
		rules.push(
			(v: number) => ValidationRules.between(v, 0, 67, i18n.t('components.iqrfnet.common.validation.rfChannel.between868')),
		);
	} else {
		rules.push(
			(v: number) => ValidationRules.between(v, 0, 255, i18n.t('components.iqrfnet.common.validation.rfChannel.between916')),
		);
	}
	return rules;
});

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
