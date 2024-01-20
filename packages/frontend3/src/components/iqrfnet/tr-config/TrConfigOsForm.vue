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
				<TextInput
					v-model='config.rfChannelA'
					type='number'
					:label='$t("components.iqrfnet.tr-config.os.rf.rfChannelA")'
				/>
				<TextInput
					v-model='config.rfChannelB'
					type='number'
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
import { type TrConfig } from '@iqrf/iqrf-gateway-daemon-utils/types';
import { useDisplay } from 'vuetify';

import Card from '@/components/Card.vue';
import TextInput from '@/components/TextInput.vue';

const display = useDisplay();
const config = defineModel<TrConfig>('config', {required: true});
</script>
