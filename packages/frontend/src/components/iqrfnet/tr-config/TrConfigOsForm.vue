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
					{{ $t('components.iqrfnet.common.rf') }}
				</legend>
				<ITextInput
					:model-value='rfBand'
					:label='`${$t("components.iqrfnet.tr-config.os.rf.rfBand")} (5)`'
					readonly
				/>
				<INumberInput
					v-model='config.rfChannelA'
					:label='`${$t("components.iqrfnet.tr-config.os.rf.rfChannelA")} (1, 2, 3)`'
					:rules='rfChannelRules'
					:min='0'
					:max='rfChannelMax'
					required
				/>
				<INumberInput
					v-model='config.rfChannelB'
					:label='`${$t("components.iqrfnet.tr-config.os.rf.rfChannelB")} (1, 2, 3, 4)`'
					:rules='rfChannelRules'
					:min='0'
					:max='rfChannelMax'
					required
				/>
				<INumberInput
					v-if='compare(dpaVersion, "3.03", "=") || compare(dpaVersion, "3.04", "=")'
					v-model='config.rfSubChannelA'
					:label='$t("components.iqrfnet.tr-config.os.rf.rfSubChannelA")'
					:rules='rfChannelRules'
					:min='0'
					:max='rfChannelMax'
					required
				/>
				<INumberInput
					v-if='compare(dpaVersion, "3.03", "=") || compare(dpaVersion, "3.04", "=")'
					v-model='config.rfSubChannelB'
					:label='$t("components.iqrfnet.tr-config.os.rf.rfSubChannelB")'
					:rules='rfChannelRules'
					:min='0'
					:max='rfChannelMax'
					required
				/>
			</v-col>
			<v-divider :vertical='!display.mobile.value' />
			<v-col>
				<legend class='section-legend'>
					{{ $t('components.iqrfnet.common.rfpgm') }}
				</legend>
				<v-checkbox
					v-model='config.rfPgmEnableAfterReset'
					:label='$t("components.iqrfnet.tr-config.os.rfpgm.enableAfterReset")'
					density='compact'
					hide-details
				/>
				<v-checkbox
					v-model='config.rfPgmTerminateAfter1Min'
					:label='$t("components.iqrfnet.tr-config.os.rfpgm.terminateAfterMin")'
					density='compact'
					hide-details
				/>
				<v-checkbox
					v-model='config.rfPgmTerminateMcuPin'
					:label='$t("components.iqrfnet.tr-config.os.rfpgm.terminateByMcuPin")'
					density='compact'
					hide-details
				/>
				<v-checkbox
					v-model='config.rfPgmDualChannel'
					:label='$t("components.iqrfnet.tr-config.os.rfpgm.dualChannel")'
					density='compact'
					hide-details
				/>
				<v-checkbox
					v-model='config.rfPgmLpMode'
					:label='$t("components.iqrfnet.tr-config.os.rfpgm.lpMode")'
					density='compact'
					hide-details
				/>
				<v-checkbox
					v-model='config.rfPgmIncorrectUpload'
					:label='`${$t("components.iqrfnet.tr-config.os.rfpgm.incorrectUpload")} (5)`'
					density='compact'
					readonly
					disabled
					hide-details
				/>
			</v-col>
		</v-row>
		<v-row>
			<v-divider />
			<v-col>
				<legend class='section-legend'>
					{{ $t('components.iqrfnet.common.trPeripherals') }}
				</legend>
				<v-checkbox
					v-model='config.thermometerSensorPresent'
					:label='`${$t("components.iqrfnet.tr-config.os.peripherals.thermometer")} (5)`'
					density='compact'
					readonly
					disabled
					hide-details
				/>
				<v-checkbox
					v-model='config.serialEepromPresent'
					:label='`${$t("components.iqrfnet.tr-config.os.peripherals.serialEeprom")} (5)`'
					density='compact'
					readonly
					disabled
					hide-details
				/>
				<v-checkbox
					v-model='config.transceiverILType'
					:label='`${$t("components.iqrfnet.tr-config.os.peripherals.transceiverIL")} (5)`'
					density='compact'
					readonly
					disabled
					hide-details
				/>
			</v-col>
			<v-divider :vertical='!display.mobile.value' />
			<v-col>
				<div>
					<em>
						{{ $t('components.iqrfnet.tr-config.os.notes.rfBand868') }}<br>
						{{ $t('components.iqrfnet.tr-config.os.notes.rfBand916') }}<br>
						{{ $t('components.iqrfnet.tr-config.os.notes.rfBand433') }}<br>
						{{ $t('components.iqrfnet.tr-config.os.notes.rfpgmOnly') }}<br>
						{{ $t('components.iqrfnet.tr-config.os.notes.readOnly') }}
					</em>
				</div>
			</v-col>
		</v-row>
	</ICard>
</template>

<script lang='ts' setup>

import {
	ICard,
	INumberInput,
	ITextInput,
	ValidationRules,
} from '@iqrf/iqrf-vue-ui';
import { compare } from 'compare-versions';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useDisplay } from 'vuetify';

import { TrConfiguration } from '@/types/DaemonApi/Iqmesh';

const config = defineModel<TrConfiguration>('config', { required: true });
defineProps({
	dpaVersion: {
		type: String,
		required: true,
	},
});
const i18n = useI18n();
const display = useDisplay();
const rfBand = computed(() => {
	return (config.value as TrConfiguration).rfBand ?? undefined;
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
</script>
