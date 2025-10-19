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
		ref='readForm'
		:disabled='[ComponentState.Action, ComponentState.Loading].includes(componentState)'
	>
		<ICard>
			<template #title>
				{{ $t('components.iqrfnet.tr-config.readTitle') }}
			</template>
			<INumberInput
				v-model='address'
				:label='$t("components.iqrfnet.common.deviceAddr")'
				:rules='[
					(v: number|null) => ValidationRules.required(v, $t("components.iqrfnet.common.validation.deviceAddr.required")),
					(v: number) => ValidationRules.integer(v, $t("components.iqrfnet.common.validation.deviceAddr.integer")),
					(v: number) => ValidationRules.between(v, 0, 239, $t("components.iqrfnet.common.validation.deviceAddr.between")),
				]'
				:min='0'
				:max='239'
				required
			/>
			<template #actions>
				<IActionBtn
					:action='Action.Download'
					:loading='componentState === ComponentState.Loading'
					:disabled='!isValid.value || componentState === ComponentState.Action'
					@click='readOs()'
				/>
			</template>
		</ICard>
	</v-form>
	<v-form
		v-slot='{ isValid }'
		ref='writeForm'
		:disabled='componentState === ComponentState.Action'
	>
		<ICard
			v-if='[ComponentState.Action, ComponentState.Ready].includes(componentState)'
			class='mt-4'
		>
			<template #title>
				{{ $t('pages.iqrfnet.tr-config.title') }}
			</template>
			<v-alert
				v-if='componentState === ComponentState.FetchFailed'
				type='error'
				variant='tonal'
				:text='$t("components.iqrfnet.tr-config.messages.read.failed")'
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
					<TrConfigOsForm
						:config='config'
						:dpa-version='dpaVersion'
					/>
				</v-window-item>
				<v-window-item :value='1'>
					<TrConfigDpaForm
						:config='config'
						:dpa-version='dpaVersion'
						:dpa-handler-detected='dpaHandlerDetected'
					/>
				</v-window-item>
				<v-window-item :value='2'>
					<ICard>
						<v-row>
							<v-col>
								<v-checkbox
									v-model='usePassword'
									:label='$t("components.iqrfnet.tr-config.security.useAccessPassword")'
									hide-details
									density='compact'
								/>
								<IPasswordInput
									v-if='usePassword'
									v-model='config.accessPassword'
									:label='`${$t("components.iqrfnet.tr-config.security.accessPassword")} (1)`'
									:rules='hexPassword ? [
										(v: string) => ValidationRules.betweenLen(v, 0, 32, $t("components.iqrfnet.tr-config.validation.accessPassword.betweenHex")),
										(v: string) => ValidationRules.regex(v, /^[0-9a-fA-F]+$/, $t("components.iqrfnet.tr-config.validation.accessPassword.regexHex")),
									] : [
										(v: string) => ValidationRules.betweenLen(v, 0, 16, $t("components.iqrfnet.tr-config.validation.accessPassword.betweenAscii")),
										(v: string) => ValidationRules.regex(v, /^[ -~]+$/, $t("components.iqrfnet.tr-config.validation.accessPassword.regexAscii")),
									]'
									:description='hexPassword ? $t("components.iqrfnet.tr-config.security.notes.hexCharset") : $t("components.iqrfnet.tr-config.security.notes.asciiCharset")'
									:required='usePassword'
								>
									<template #prepend>
										<v-icon
											v-tooltip:bottom='hexPassword ? $t("components.iqrfnet.tr-config.security.asciiChange") : $t("components.iqrfnet.tr-config.security.hexChange")'
											:icon='hexPassword ? mdiAlphabeticalVariant : mdiHexadecimal'
											color='warning'
											size='x-large'
											@click='hexPassword = !hexPassword'
										/>
									</template>
								</IPasswordInput>
								<v-checkbox
									v-model='useUserKey'
									:label='$t("components.iqrfnet.tr-config.security.useUserKey")'
									hide-details
									density='compact'
								/>
								<IPasswordInput
									v-if='useUserKey'
									v-model='config.securityUserKey'
									:label='`${$t("components.iqrfnet.tr-config.security.userKey")} (1)`'
									:rules='hexUserKey ? [
										(v: string) => ValidationRules.betweenLen(v, 0, 32, $t("components.iqrfnet.tr-config.validation.userKey.betweenHex")),
										(v: string) => ValidationRules.regex(v, /^[0-9a-fA-F]+$/, $t("components.iqrfnet.tr-config.validation.userKey.regexHex")),
									] : [
										(v: string) => ValidationRules.betweenLen(v, 0, 16, $t("components.iqrfnet.tr-config.validation.userKey.betweenAscii")),
										(v: string) => ValidationRules.regex(v, /^[ -~]+$/, $t("components.iqrfnet.tr-config.validation.userKey.regexAscii")),
									]'
									:description='hexUserKey ? $t("components.iqrfnet.tr-config.security.notes.hexCharset") : $t("components.iqrfnet.tr-config.security.notes.asciiCharset")'
									:required='useUserKey'
								>
									<template #prepend>
										<v-icon
											v-tooltip:bottom='hexUserKey ? $t("components.iqrfnet.tr-config.security.asciiChange") : $t("components.iqrfnet.tr-config.security.hexChange")'
											:icon='hexUserKey ? mdiAlphabeticalVariant : mdiHexadecimal'
											color='warning'
											size='x-large'
											@click='hexUserKey = !hexUserKey'
										/>
									</template>
								</IPasswordInput>
							</v-col>
							<v-divider :vertical='!display.mobile.value' />
							<v-col>
								<div>
									<em>
										{{ $t('components.iqrfnet.tr-config.security.notes.empty') }}
									</em>
								</div>
							</v-col>
						</v-row>
					</ICard>
				</v-window-item>
			</v-window>
			<template
				v-if='[ComponentState.Action, ComponentState.Ready].includes(componentState)'
				#actions
			>
				<IActionBtn
					:action='Action.Upload'
					:loading='componentState === ComponentState.Action'
					:disabled='!isValid.value || componentState === ComponentState.Loading'
					@click='writeConfig()'
				/>
			</template>
		</ICard>
	</v-form>
	<DpaHandlerWarnDialog
		:model-value='dpaEnabledNotDetected'
		:device-addr='config.deviceAddr'
		@restart='restart(config.deviceAddr)'
	/>
</template>

<script lang='ts' setup>
import { EmbedOsMessages, GenericMessages, IqmeshServiceMessages } from '@iqrf/iqrf-gateway-daemon-utils/enums';
import { OsService } from '@iqrf/iqrf-gateway-daemon-utils/services/embed';
import { EnumerationService, TrConfigurationService } from '@iqrf/iqrf-gateway-daemon-utils/services/iqmesh';
import { DaemonApiResponse } from '@iqrf/iqrf-gateway-daemon-utils/types';
import { IqmeshWriteTrConfigParams } from '@iqrf/iqrf-gateway-daemon-utils/types/iqmesh';
import { DaemonMessageOptions } from '@iqrf/iqrf-gateway-daemon-utils/utils';
import { Action, ComponentState, IActionBtn, ICard, INumberInput, IPasswordInput, ValidationRules } from '@iqrf/iqrf-vue-ui';
import { mdiAlphabeticalVariant, mdiHexadecimal } from '@mdi/js';
import { compare } from 'compare-versions';
import { onBeforeUnmount, ref, type Ref, toRaw, useTemplateRef } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { useDisplay } from 'vuetify';
import { VForm } from 'vuetify/components';

import DpaHandlerWarnDialog from '@/components/iqrfnet/tr-config/DpaHandlerWarnDialog.vue';
import TrConfigDpaForm from '@/components/iqrfnet/tr-config/TrConfigDpaForm.vue';
import TrConfigOsForm from '@/components/iqrfnet/tr-config/TrConfigOsForm.vue';
import { validateForm } from '@/helpers/validateForm';
import { useDaemonStore } from '@/store/daemonSocket';
import { DeviceEnumeration, TrConfiguration } from '@/types/DaemonApi/Iqmesh';

const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const display = useDisplay();
const daemonStore = useDaemonStore();
const i18n = useI18n();
const msgId: Ref<string | null> = ref(null);
const address: Ref<number> = ref(0);
const tab: Ref<number> = ref(0);
const readForm: Ref<VForm|null> = useTemplateRef('readForm');
const writeForm: Ref<VForm|null> = useTemplateRef('writeForm');
const config: Ref<TrConfiguration & { deviceAddr: number }> = ref({
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
const usePassword: Ref<boolean> = ref(false);
const hexPassword: Ref<boolean> = ref(false);
const useUserKey: Ref<boolean> = ref(false);
const hexUserKey: Ref<boolean> = ref(false);
const dpaHandlerDetected: Ref<boolean> = ref(false);
const dpaEnabledNotDetected: Ref<boolean> = ref(false);
const dpaVersion: Ref<string> = ref('');
const awaitingAsyncReset: Ref<boolean> = ref(false);
let restartTimeout = -1;

daemonStore.$onAction(({ name, after }) => {
	if (name === 'onMessage') {
		after((rsp: DaemonApiResponse) => {
			if (awaitingAsyncReset.value && rsp.mType === GenericMessages.Raw && rsp.data.msgId === 'async') {
				handleRawResponse(rsp);
				return;
			}
			if (rsp.data.msgId !== msgId.value) {
				return;
			}
			daemonStore.removeMessage(msgId.value);
			switch (rsp.mType) {
				case IqmeshServiceMessages.Enumerate:
					componentState.value = ComponentState.Ready;
					handleEnumerate(rsp);
					break;
				case IqmeshServiceMessages.WriteTrConfig:
					componentState.value = ComponentState.Ready;
					handleWriteConfig(rsp);
					break;
				case EmbedOsMessages.Restart:
					handleRestart(rsp);
					break;
				case EmbedOsMessages.Read:
					handleOsRead(rsp);
					break;
			}
		});
	}
});

async function readOs(): Promise<void> {
	if (!await validateForm(readForm.value)) {
		return;
	}
	componentState.value = ComponentState.Loading;
	const opts = new DaemonMessageOptions(
		null,
		30_000,
		i18n.t('components.iqrfnet.tr-config.messages.read.timeout'),
		() => {
			componentState.value = ComponentState.FetchFailed;
			msgId.value = null;
		},
	);
	msgId.value = await daemonStore.sendMessage(
		OsService.read(
			{ addr: address.value, returnVerbose: true },
			opts,
		),
	);
}

function handleOsRead(rsp: DaemonApiResponse): void {
	if (rsp.data.status !== 0) {
		componentState.value = ComponentState.FetchFailed;
		toast.error(
			i18n.t('components.iqrfnet.tr-config.messages.read.failed'),
		);
		return;
	}
	const flags = rsp.data.rsp.result.flags & 8;
	if (flags === 8) {
		dpaEnabledNotDetected.value = true;
		componentState.value = ComponentState.FetchFailed;
	} else {
		enumerate();
	}
}

async function enumerate(): Promise<void> {
	if (!await validateForm(readForm.value)) {
		return;
	}
	tab.value = 0;
	const opts = new DaemonMessageOptions(
		null,
		60_000,
		i18n.t('components.iqrfnet.tr-config.messages.read.timeout'),
		() => {
			componentState.value = ComponentState.FetchFailed;
			msgId.value = null;
		},
	);
	msgId.value = await daemonStore.sendMessage(
		EnumerationService.enumerate(
			{ repeat: 1, returnVerbose: true },
			{ deviceAddr: address.value },
			opts,
		),
	);
}

function handleEnumerate(rsp: DaemonApiResponse): void {
	if (rsp.data.status === 0) {
		const data = rsp.data.rsp as DeviceEnumeration;
		config.value = { ...config.value, ...data.trConfiguration, deviceAddr: data.deviceAddr };
		dpaHandlerDetected.value = data.osRead.flags.dpaHandlerDetected;
		dpaVersion.value = data.peripheralEnumeration.dpaVer;
		componentState.value = ComponentState.Ready;
		toast.success(
			i18n.t('components.iqrfnet.tr-config.messages.read.success'),
		);
		return;
	}
	componentState.value = ComponentState.FetchFailed;
	switch (rsp.data.status) {
		case -1:
		case 1_005:
			toast.error(
				i18n.t('common.messages.offlineDevice', { address: address.value }),
			);
			break;
		case 8:
			toast.error(
				i18n.t('common.messages.noDevice', { address: address.value }),
			);
			break;
		default:
			toast.error(
				i18n.t('components.iqrfnet.tr-config.messages.read.failed'),
			);
	}
}

function prepareConfigData(data: TrConfiguration, addr: number): IqmeshWriteTrConfigParams {
	if (addr === 0) {
		delete data.lpRxTimeout;
		// peripherals
		delete data.embPers.node;
		delete data.embPers.spi;
		delete data.embPers.uart;
		// filter DPA other section
		delete data.dpaPeerToPeer;
		delete data.localFrcReception;
		delete data.routingOff;
		delete data.neverSleep;
	} else {
		// filter RF
		delete data.stdAndLpNetwork;
		// filter peripherals
		delete data.embPers.coordinator;
	}
	if (dpaVersion.value.length > 0) {
		if (!compare(dpaVersion.value, '3.03', '=') && !compare(dpaVersion.value, '3.04', '=')) {
			delete data.rfSubChannelA;
			delete data.rfSubChannelB;
		}
		if (compare(dpaVersion.value, '3.03', '<')) {
			delete data.neverSleep;
		}
		if (compare(dpaVersion.value, '4.00', '>=')) {
			delete data.nodeDpaInterface;
		}
		if (compare(dpaVersion.value, '4.10', '<')) {
			delete data.dpaPeerToPeer;
		}
		if (compare(dpaVersion.value, '4.14', '>')) {
			delete data.dpaAutoexec;
			delete data.embPers.spi;
		}
		if (compare(dpaVersion.value, '4.15', '<')) {
			delete data.localFrcReception;
		}
	}
	if (!usePassword.value) {
		delete data.accessPassword;
	}
	if (!useUserKey.value) {
		delete data.securityUserKey;
	}
	delete data.rfPgmIncorrectUpload;
	delete data.rfBand;
	delete data.embPers.values;
	delete data.thermometerSensorPresent;
	delete data.serialEepromPresent;
	delete data.transcieverILType;
	return data as IqmeshWriteTrConfigParams;
}

async function writeConfig(): Promise<void> {
	if (!await validateForm(writeForm.value)) {
		return;
	}
	componentState.value = ComponentState.Action;
	const params = prepareConfigData(structuredClone(toRaw(config.value)), address.value);
	const opts = new DaemonMessageOptions(
		null,
		255_000,
		i18n.t('components.iqrfnet.tr-config.messages.write.timeout'),
		() => {
			componentState.value = ComponentState.Ready;
			msgId.value = null;
		},
	);
	msgId.value = await daemonStore.sendMessage(
		TrConfigurationService.write(
			{ repeat: 1, returnVerbose: true },
			params,
			opts,
		),
	);
}

function handleWriteConfig(rsp: DaemonApiResponse): void {
	switch (rsp.data.status) {
		case 0:
			toast.success(
				i18n.t('components.iqrfnet.tr-config.messages.write.success'),
			);
			if (rsp.data.rsp.restartNeeded) {
				restart(rsp.data.rsp.deviceAddr);
			}
			break;
		case -1:
			toast.error(
				i18n.t('common.messages.offlineDevice', { address: address.value }),
			);
			break;
		case 8:
			toast.error(
				i18n.t('common.messages.noDevice', { address: address.value }),
			);
			break;
		default:
			toast.error(
				i18n.t('components.iqrfnet.tr-config.messages.write.failed'),
			);
	}
}

async function restart(addr: number): Promise<void> {
	componentState.value = ComponentState.Action;
	const opts = new DaemonMessageOptions(
		null,
		30_000,
		i18n.t('components.iqrfnet.tr-config.messages.restart.timeout'),
		() => {
			componentState.value = ComponentState.Ready;
			msgId.value = null;
		},
	);
	msgId.value = await daemonStore.sendMessage(
		OsService.restart(
			{ addr: addr, returnVerbose: true },
			opts,
		),
	);
}

function handleRestart(rsp: DaemonApiResponse): void {
	if (rsp.data.status !== 0) {
		componentState.value = ComponentState.Ready;
		toast.error(
			i18n.t('components.iqrfnet.tr-config.messages.restart.failed'),
		);
		return;
	}
	if (rsp.data.rsp.nAdr === 0) {
		awaitingAsyncReset.value = true;
		restartTimeout = window.setTimeout(() => {
			awaitingAsyncReset.value = false;
			componentState.value = ComponentState.Ready;
			toast.error(
				i18n.t('components.iqrfnet.tr-config.messages.restart.failed'),
			);
		}, 3_500);
	} else {
		componentState.value = ComponentState.Ready;
		toast.success(
			i18n.t('components.iqrfnet.tr-config.messages.restart.success'),
		);
	}
}

function handleRawResponse(rsp: DaemonApiResponse): void {
	if (rsp.data.rsp.rData.startsWith('00.00.ff.3f')) {
		window.clearTimeout(restartTimeout);
		awaitingAsyncReset.value = false;
		componentState.value = ComponentState.Ready;
		toast.success(
			i18n.t('components.iqrfnet.tr-config.messages.restart.success'),
		);
	}
}

onBeforeUnmount(() => {
	daemonStore.removeMessage(msgId.value);
});

</script>
