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
	<Card>
		<v-row>
			<v-col>
				<legend class='section-legend'>
					{{ $t('components.iqrfnet.common.rf') }}
				</legend>
				<TextInput
					v-model='config.rfBand'
					:label='$t("components.iqrfnet.tr-config.os.rf.rfBand")'
					readonly
				/>
				<NumberInput
					v-model='config.rfChannelA'
					:label='$t("components.iqrfnet.tr-config.os.rf.rfChannelA")'
				/>
				<NumberInput
					v-model='config.rfChannelB'
					:label='$t("components.iqrfnet.tr-config.os.rf.rfChannelB")'
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
					v-model='config.rfPgmEnableAfterReset'
					:label='$t("components.iqrfnet.tr-config.os.rfpgm.terminateAfterMin")'
					density='compact'
					hide-details
				/>
				<v-checkbox
					v-model='config.rfPgmEnableAfterReset'
					:label='$t("components.iqrfnet.tr-config.os.rfpgm.terminateByMcuPin")'
					density='compact'
					hide-details
				/>
				<v-checkbox
					v-model='config.rfPgmEnableAfterReset'
					:label='$t("components.iqrfnet.tr-config.os.rfpgm.dualChannel")'
					density='compact'
					hide-details
				/>
				<v-checkbox
					v-model='config.rfPgmEnableAfterReset'
					:label='$t("components.iqrfnet.tr-config.os.rfpgm.lpMode")'
					density='compact'
					hide-details
				/>
				<v-checkbox
					v-model='config.rfPgmEnableAfterReset'
					:label='$t("components.iqrfnet.tr-config.os.rfpgm.incorrectUpload")'
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
					v-model='config.rfPgmEnableAfterReset'
					:label='$t("components.iqrfnet.common.peripherals.thermometer")'
					density='compact'
					readonly
					disabled
					hide-details
				/>
				<v-checkbox
					v-model='config.rfPgmEnableAfterReset'
					:label='$t("components.iqrfnet.common.peripherals.externalEeprom")'
					density='compact'
					readonly
					disabled
					hide-details
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
import { useDisplay } from 'vuetify';

import Card from '@/components/layout/card/Card.vue';
import NumberInput from '@/components/layout/form/NumberInput.vue';
import TextInput from '@/components/layout/form/TextInput.vue';

const display = useDisplay();
const config = defineModel<IqmeshTrConfigParams>('config', { required: true });
</script>
