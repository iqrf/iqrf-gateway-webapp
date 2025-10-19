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
	<div>
		<ICard>
			<template #title>
				{{ $t("pages.iqrfnet.send-dpa.title") }}
			</template>
			<v-form
				ref='form'
				v-slot='{ isValid }'
				:disabled='componentState === ComponentState.Action'
				@submit.prevent='onSubmit()'
			>
				<v-row :no-gutters='display.mobile.value'>
					<v-col
						cols='12'
						md='6'
					>
						<INumberInput
							v-if='!useHexNadr'
							v-model='nadr'
							:min='0'
							:max='255'
							:label='$t("components.iqrfnet.send-dpa.nadr")'
							:rules='[
								(v: number|null) => ValidationRules.required(v, $t("components.iqrfnet.send-dpa.validation.nadrMissing")),
								(v: number) => ValidationRules.integer(v, $t("components.iqrfnet.send-dpa.validation.nadrInvalid")),
								(v: number) => ValidationRules.between(v, 0, 255, $t("components.iqrfnet.send-dpa.validation.nadrInvalid")),
							]'
							required
							:readonly='lockNadr'
						>
							<template #prepend>
								<v-tooltip location='bottom'>
									<template #activator='{ props }'>
										<v-icon
											v-bind='props'
											:icon='lockNadr ? mdiLock : mdiLockOpen'
											color='warning'
											@click='lockNadr = !lockNadr'
										/>
									</template>
									{{
										$t(
											`components.iqrfnet.send-dpa.${
												lockNadr ? "unlock" : "lock"
											}`,
										)
									}}
								</v-tooltip>
							</template>
							<template #append-inner>
								<v-tooltip location='bottom'>
									<template #activator='{ props }'>
										<v-icon
											v-bind='props'
											:icon='mdiHexadecimal'
											color='primary'
											size='large'
											@click='changeNadrBase()'
										/>
									</template>
									{{ $t("components.iqrfnet.send-dpa.hexadecimal") }}
								</v-tooltip>
								<v-menu>
									<template #activator='{ props }'>
										<v-icon
											v-bind='props'
											:icon='mdiMenu'
											color='primary'
											size='large'
										/>
									</template>
									<v-list class='py-0' density='compact'>
										<v-list-item density='compact'>
											<b>{{ $t("components.iqrfnet.send-dpa.presets") }}</b>
										</v-list-item>
										<hr>
										<v-list-item
											v-for='preset of nadrPresets'
											:key='preset.title'
											@click='applyNadrPreset(preset.value)'
										>
											{{ preset.title }}
										</v-list-item>
									</v-list>
								</v-menu>
							</template>
						</INumberInput>
						<ITextInput
							v-else
							v-model='nadrHex'
							v-maska='byteMaska'
							:label='$t("components.iqrfnet.send-dpa.nadr")'
							:rules='[
								(v: string|null) => ValidationRules.required(v, $t("components.iqrfnet.send-dpa.validation.nadrHexMissing")),
								(v: string) => validateHexByte(v) || $t("components.iqrfnet.send-dpa.validation.nadrHexInvalid"),
							]'
							required
							:readonly='lockNadr'
						>
							<template #prepend>
								<v-tooltip location='bottom'>
									<template #activator='{ props }'>
										<v-icon
											v-bind='props'
											:icon='lockNadr ? mdiLock : mdiLockOpen'
											color='warning'
											@click='lockNadr = !lockNadr'
										/>
									</template>
									{{
										$t(
											`components.iqrfnet.send-dpa.${
												lockNadr ? "unlock" : "lock"
											}`,
										)
									}}
								</v-tooltip>
							</template>
							<template #append-inner>
								<v-tooltip location='bottom'>
									<template #activator='{ props }'>
										<v-icon
											v-bind='props'
											:icon='mdiNumeric'
											color='primary'
											size='large'
											@click='changeNadrBase()'
										/>
									</template>
									{{ $t("components.iqrfnet.send-dpa.decimal") }}
								</v-tooltip>
								<v-menu>
									<template #activator='{ props }'>
										<v-icon
											v-bind='props'
											:icon='mdiMenu'
											color='primary'
											size='large'
										/>
									</template>
									<v-list class='py-0' density='compact'>
										<v-list-item density='compact'>
											<b>{{ $t("components.iqrfnet.send-dpa.presets") }}</b>
										</v-list-item>
										<hr>
										<v-list-item
											v-for='preset of nadrPresets'
											:key='preset.title'
											@click='applyNadrPreset(preset.value)'
										>
											{{ preset.title }}
										</v-list-item>
									</v-list>
								</v-menu>
							</template>
						</ITextInput>
					</v-col>
					<v-col
						cols='12'
						md='6'
					>
						<INumberInput
							v-if='!useHexPnum'
							v-model='pnum'
							:min='0'
							:max='255'
							:label='$t("components.iqrfnet.send-dpa.pnum")'
							:rules='[
								(v: number|null) => ValidationRules.required(v, $t("components.iqrfnet.send-dpa.validation.pnumMissing")),
								(v: number) => ValidationRules.integer(v, $t("components.iqrfnet.send-dpa.validation.pnumInvalid")),
								(v: number) => ValidationRules.between(v, 0, 255, $t("components.iqrfnet.send-dpa.validation.pnumInvalid")),
							]'
							required
						>
							<template #append-inner>
								<v-tooltip location='bottom'>
									<template #activator='{ props }'>
										<v-icon
											v-bind='props'
											:icon='mdiHexadecimal'
											color='primary'
											size='large'
											@click='changePnumBase()'
										/>
									</template>
									{{ $t("components.iqrfnet.send-dpa.hexadecimal") }}
								</v-tooltip>
							</template>
						</INumberInput>
						<ITextInput
							v-else
							v-model='pnumHex'
							v-maska='byteMaska'
							:label='$t("components.iqrfnet.send-dpa.pnum")'
							:rules='[
								(v: string|null) => ValidationRules.required(v, $t("components.iqrfnet.send-dpa.validation.pnumHexMissing")),
								(v: string) => validateHexByte(v) || $t("components.iqrfnet.send-dpa.validation.pnumHexInvalid"),
							]'
							required
						>
							<template #append-inner>
								<v-tooltip location='bottom'>
									<template #activator='{ props }'>
										<v-icon
											v-bind='props'
											:icon='mdiNumeric'
											color='primary'
											size='large'
											@click='changePnumBase()'
										/>
									</template>
									{{ $t("components.iqrfnet.send-dpa.decimal") }}
								</v-tooltip>
							</template>
						</ITextInput>
					</v-col>
				</v-row>
				<v-row :no-gutters='display.mobile.value'>
					<v-col
						cols='12'
						md='6'
					>
						<INumberInput
							v-if='!useHexPcmd'
							v-model='pcmd'
							:min='0'
							:max='127'
							:label='$t("components.iqrfnet.send-dpa.pcmd")'
							:rules='[
								(v: number|null) => ValidationRules.required(v, $t("components.iqrfnet.send-dpa.validation.pcmdMissing")),
								(v: number) => ValidationRules.integer(v, $t("components.iqrfnet.send-dpa.validation.pcmdInvalid")),
								(v: number) => ValidationRules.between(v, 0, 127, $t("components.iqrfnet.send-dpa.validation.pcmdInvalid")),
							]'
							required
						>
							<template #append-inner>
								<v-tooltip location='bottom'>
									<template #activator='{ props }'>
										<v-icon
											v-bind='props'
											:icon='mdiHexadecimal'
											color='primary'
											size='large'
											@click='changePcmdBase()'
										/>
									</template>
									{{ $t("components.iqrfnet.send-dpa.hexadecimal") }}
								</v-tooltip>
							</template>
						</INumberInput>
						<ITextInput
							v-else
							v-model='pcmdHex'
							v-maska='byteMaska'
							:label='$t("components.iqrfnet.send-dpa.pcmd")'
							:rules='[
								(v: string|null) => ValidationRules.required(v, $t("components.iqrfnet.send-dpa.validation.pcmdHexMissing")),
								(v: string) => validateHexNibble(v) || $t("components.iqrfnet.send-dpa.validation.pcmdHexInvalid"),
							]'
							required
						>
							<template #append-inner>
								<v-tooltip location='bottom'>
									<template #activator='{ props }'>
										<v-icon
											v-bind='props'
											:icon='mdiNumeric'
											color='primary'
											size='large'
											@click='changePcmdBase()'
										/>
									</template>
									{{ $t("components.iqrfnet.send-dpa.decimal") }}
								</v-tooltip>
							</template>
						</ITextInput>
					</v-col>
					<v-col
						cols='12'
						md='6'
					>
						<INumberInput
							v-if='!useHexHwpid'
							v-model='hwpid'
							:min='0'
							:max='65535'
							:label='$t("components.iqrfnet.send-dpa.hwpid")'
							:rules='[
								(v: number|null) => ValidationRules.required(v, $t("components.iqrfnet.send-dpa.validation.hwpidMissing")),
								(v: number) => ValidationRules.integer(v, $t("components.iqrfnet.send-dpa.validation.hwpidInvalid")),
								(v: number) => ValidationRules.between(v, 0, 65535, $t("components.iqrfnet.send-dpa.validation.hwpidInvalid")),
							]'
							required
						>
							<template #append-inner>
								<v-tooltip location='bottom'>
									<template #activator='{ props }'>
										<v-icon
											v-bind='props'
											:icon='mdiHexadecimal'
											color='primary'
											size='large'
											@click='changeHwpidBase()'
										/>
									</template>
									{{ $t("components.iqrfnet.send-dpa.hexadecimal") }}
								</v-tooltip>
								<v-menu v-model='hwpidMenu' eager>
									<template #activator='{ props }'>
										<v-icon
											v-bind='props'
											:icon='mdiMenu'
											color='primary'
											size='large'
										/>
									</template>
									<v-list class='py-0' density='compact'>
										<v-list-item density='compact'>
											<b>{{ $t("components.iqrfnet.send-dpa.presets") }}</b>
										</v-list-item>
										<hr>
										<v-list-item
											v-for='preset of hwpidPresets'
											:key='preset.title'
											@click='applyHwpidPreset(preset.value)'
										>
											{{ preset.title }}
										</v-list-item>
										<ProductBrowser
											:list-activator='true'
											@apply='(item) => applyHwpidPreset(item.hwpid)'
											@close='hwpidMenu = false'
										/>
									</v-list>
								</v-menu>
							</template>
						</INumberInput>
						<ITextInput
							v-else
							v-model='hwpidHex'
							v-maska='hwpidMaska'
							:label='$t("components.iqrfnet.send-dpa.hwpid")'
							:rules='[
								(v: string|null) => ValidationRules.required(v, $t("components.iqrfnet.send-dpa.validation.hwpidHexMissing")),
								(v: string) => validateHexWord(v) || $t("components.iqrfnet.send-dpa.validation.hwpidHexInvalid"),
							]'
							required
						>
							<template #append-inner>
								<v-tooltip location='bottom'>
									<template #activator='{ props }'>
										<v-icon
											v-bind='props'
											:icon='mdiNumeric'
											color='primary'
											size='large'
											@click='changeHwpidBase()'
										/>
									</template>
									{{ $t("components.iqrfnet.send-dpa.decimal") }}
								</v-tooltip>
								<v-menu v-model='hwpidMenu' eager>
									<template #activator='{ props }'>
										<v-icon
											v-bind='props'
											:icon='mdiMenu'
											color='primary'
											size='large'
										/>
									</template>
									<v-list class='py-0' density='compact'>
										<v-list-item density='compact'>
											<b>{{ $t("components.iqrfnet.send-dpa.presets") }}</b>
										</v-list-item>
										<hr>
										<v-list-item
											v-for='preset of hwpidPresets'
											:key='preset.title'
											@click='applyHwpidPreset(preset.value)'
										>
											{{ preset.title }}
										</v-list-item>
										<ProductBrowser
											:list-activator='true'
											@apply='(item) => applyHwpidPreset(item.hwpid)'
											@close='hwpidMenu = false'
										/>
									</v-list>
								</v-menu>
							</template>
						</ITextInput>
					</v-col>
				</v-row>
				<ITextInput
					v-model='pdata'
					v-maska='pdataMaska'
					:label='$t("components.iqrfnet.send-dpa.pdata")'
					:rules='[
						(v: string) => validatePdata(v) || $t("components.iqrfnet.send-dpa.validation.pdataInvalid"),
					]'
				/>
				<v-checkbox
					v-model='useCustomTimeout'
					:label='$t("components.iqrfnet.send-dpa.useCustomTimeout")'
					density='compact'
				/>
				<INumberInput
					v-model='customTimeout'
					:min='1000'
					:label='$t("components.iqrfnet.send-dpa.customTimeout")'
					:disabled='!useCustomTimeout'
					required
				/>
				<IActionBtn
					:action='Action.Custom'
					:icon='mdiSend'
					:loading='componentState === ComponentState.Action'
					:disabled='!isValid.value'
					type='submit'
					:text='$t("common.buttons.send")'
				/>
			</v-form>
		</ICard>
		<PacketMacros class='my-4' @set-packet='applyPacket' />
		<PacketHistory :messages='messages' @clear='clearMessages' />
	</div>
</template>

<script lang="ts" setup>
import { GenericMessages } from '@iqrf/iqrf-gateway-daemon-utils/enums';
import { GenericService } from '@iqrf/iqrf-gateway-daemon-utils/services';
import { DaemonApiResponse } from '@iqrf/iqrf-gateway-daemon-utils/types';
import { DaemonMessageOptions } from '@iqrf/iqrf-gateway-daemon-utils/utils';
import {
	Action,
	ComponentState,
	IActionBtn,
	ICard,
	INumberInput,
	ITextInput,
	ValidationRules,
} from '@iqrf/iqrf-vue-ui';
import {
	mdiHexadecimal,
	mdiLock,
	mdiLockOpen,
	mdiMenu,
	mdiNumeric,
	mdiSend,
} from '@mdi/js';
import { DateTime } from 'luxon';
import { vMaska } from 'maska/vue';
import { ref, type Ref, useTemplateRef } from 'vue';
import { useDisplay } from 'vuetify';
import { VForm } from 'vuetify/components';

import ProductBrowser from '@/components/iqrfnet/ProductBrowser.vue';
import PacketHistory from '@/components/iqrfnet/send-dpa/PacketHistory.vue';
import PacketMacros from '@/components/iqrfnet/send-dpa/PacketMacros.vue';
import { validateForm } from '@/helpers/validateForm';
import { useDaemonStore } from '@/store/daemonSocket';
import { type DpaPacketTransaction } from '@/types/Iqrfnet';

const componentState: Ref<ComponentState> = ref(ComponentState.Idle);
const daemonStore = useDaemonStore();
const display = useDisplay();
const form: Ref<VForm|null> = useTemplateRef('form');
const msgId: Ref<string | null> = ref(null);
const messages: Ref<DpaPacketTransaction[]> = ref([]);
const hwpidMenu: Ref<boolean> = ref(false);

daemonStore.$onAction(({ name, after }) => {
	if (name === 'onMessage') {
		after((rsp: DaemonApiResponse) => {
			if (rsp.data.msgId !== msgId.value) {
				return;
			}
			daemonStore.removeMessage(msgId.value);
			componentState.value = ComponentState.Idle;
			if (rsp.mType === GenericMessages.Raw) {
				handleResponse(rsp);
			}
		});
	}
});

// nadr
const lockNadr: Ref<boolean> = ref(false);
const useHexNadr: Ref<boolean> = ref(true);
const nadr: Ref<number> = ref(0);
const nadrHex: Ref<string> = ref('00');
const nadrPresets = [
	{ title: 'Coordinator', value: 0 },
	{ title: 'Local device', value: 252 },
	{ title: 'Temporary', value: 254 },
	{ title: 'Broadcast', value: 255 },
];
// pnum
const useHexPnum: Ref<boolean> = ref(true);
const pnum: Ref<number> = ref(0);
const pnumHex: Ref<string> = ref('00');
// pcmd
const useHexPcmd: Ref<boolean> = ref(true);
const pcmd: Ref<number> = ref(0);
const pcmdHex: Ref<string> = ref('00');
const byteMaska = {
	mask: 'HH',
	tokens: { H: { pattern: /[\da-f]/i } },
};
// hwpid
const useHexHwpid: Ref<boolean> = ref(true);
const hwpid: Ref<number> = ref(0);
const hwpidHex: Ref<string> = ref('ffff');
const hwpidPresets = [
	{ title: 'DPA Plugin without handler', value: 0 },
	{ title: 'Any device', value: 65_535 },
];
const hwpidMaska = {
	mask: 'HHHH',
	tokens: { H: { pattern: /[\da-f]/i } },
};
// pdata
const pdata: Ref<string> = ref('');
const pdataMaska = {
	mask: `${'HH.'.repeat(56)}HH`,
	tokens: { H: { pattern: /[\da-f]/i } },
};
// timeout
const useCustomTimeout: Ref<boolean> = ref(false);
const customTimeout: Ref<number> = ref(1_000);

// nadr functions
function changeNadrBase(): void {
	if (lockNadr.value) {
		return;
	}
	if (useHexNadr.value) {
		const val = Number.parseInt(nadrHex.value, 16);
		nadr.value = Number.isNaN(val) ? 0 : val;
	} else {
		nadrHex.value = numberToHexString(nadr.value, 2);
	}
	useHexNadr.value = !useHexNadr.value;
}

function applyNadrPreset(value: number): void {
	if (lockNadr.value) {
		return;
	}
	if (useHexNadr.value) {
		nadrHex.value = numberToHexString(value, 2);
	} else {
		nadr.value = value;
	}
}

// pnum functions
function changePnumBase(): void {
	if (useHexPnum.value) {
		const val = Number.parseInt(pnumHex.value, 16);
		pnum.value = Number.isNaN(val) ? 0 : val;
	} else {
		pnumHex.value = numberToHexString(pnum.value, 2);
	}
	useHexPnum.value = !useHexPnum.value;
}

// pcmd functions
function changePcmdBase(): void {
	if (useHexPcmd.value) {
		const val = Number.parseInt(pcmdHex.value, 16);
		pcmd.value = Number.isNaN(val) ? 0 : val;
	} else {
		pcmdHex.value = numberToHexString(pcmd.value, 2);
	}
	useHexPcmd.value = !useHexPcmd.value;
}

// hwpid functions
function changeHwpidBase(): void {
	if (useHexHwpid.value) {
		const val = Number.parseInt(hwpidHex.value, 16);
		hwpid.value = Number.isNaN(val) ? 0 : val;
	} else {
		hwpidHex.value = numberToHexString(hwpid.value, 4);
	}
	useHexHwpid.value = !useHexHwpid.value;
}

function applyHwpidPreset(value: number): void {
	if (useHexHwpid.value) {
		hwpidHex.value = numberToHexString(value, 4);
	} else {
		hwpid.value = value;
	}
}

// validation
function validateHexNibble(value: string): boolean {
	const re = /^[0-7][\da-f]$/i;
	return re.test(value);
}

function validateHexByte(value: string): boolean {
	const re = /^[\da-f]{2}$/i;
	return re.test(value);
}

function validateHexWord(value: string): boolean {
	const re = /^[\da-f]{4}$/i;
	return re.test(value);
}

function validatePdata(value: string): boolean {
	if (value.length === 0) {
		return true;
	}
	const re = /^(?:[\da-f]{2}.){0,56}[\da-f]{2}.?$/i;
	return re.test(value);
}

function numberToHexString(value: number | null, digits: number): string {
	if (value === null) {
		return ''.padStart(digits, '0');
	}
	return value.toString(16).padStart(digits, '0');
}

function buildPacket(): string {
	const packetHwpid = useHexHwpid.value
		? hwpidHex.value
		: numberToHexString(hwpid.value, 4);
	const packet = `${
		useHexNadr.value ? nadrHex.value : numberToHexString(nadr.value, 2)
	}.00.${useHexPnum.value ? pnumHex.value : numberToHexString(pnum.value, 2)}.${
		useHexPcmd.value ? pcmdHex.value : numberToHexString(pcmd.value, 2)
	}.${packetHwpid.substring(0, 2)}.${packetHwpid.substring(2, 4)}.${
		pdata.value
	}`;
	return packet.endsWith('.') ? packet.slice(0, -1) : packet;
}

async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value)) {
		return;
	}
	componentState.value = ComponentState.Action;
	const rq = GenericService.raw(
		buildPacket(),
		new DaemonMessageOptions(
			null,
			null,
			null,
			() => {
				msgId.value = null;
				componentState.value = ComponentState.Idle;
			},
		),
	);
	if (useCustomTimeout.value && rq.request !== null) {
		rq.request.data.timeout = customTimeout.value;
	}
	msgId.value = await daemonStore.sendMessage(rq);
	messages.value.push({
		msgId: msgId.value,
		request: rq.request!.data.req!.rData,
		requestTs: DateTime.now().toLocaleString(
			DateTime.DATETIME_SHORT_WITH_SECONDS,
		),
	});
}

function handleResponse(rsp: DaemonApiResponse): void {
	const msgId = rsp.data.msgId;
	const idx = messages.value.findIndex(
		(item: DpaPacketTransaction) => item.msgId === msgId,
	);
	if (idx === -1) {
		return;
	}
	let confirmation = undefined;
	let confirmationTs = undefined;
	let responseTs = undefined;
	if (rsp.data.raw) {
		confirmation = rsp.data.raw[0].confirmation;
		confirmationTs = convertTimestamp(rsp.data.raw[0].confirmationTs);
		responseTs = convertTimestamp(rsp.data.raw[0].responseTs);
	}
	messages.value[idx] = {
		...messages.value[idx],
		confirmation: confirmation,
		confirmationTs: confirmationTs,
		response: rsp.data.rsp.rData,
		responseTs: responseTs,
	};
}

function clearMessages(): void {
	messages.value = [];
}

function applyPacket(value: string): void {
	if (value.length > 17) {
		pdata.value = value.substring(18, value.length);
	} else {
		pdata.value = '';
	}
	value = value.replaceAll('.', '');
	if (!lockNadr.value) {
		nadr.value = Number.parseInt(value.substring(0, 2), 16);
		nadrHex.value = value.substring(0, 2);
	}
	pnum.value = Number.parseInt(value.substring(4, 6), 16);
	pnumHex.value = value.substring(4, 6);
	pcmd.value = Number.parseInt(value.substring(6, 8), 16);
	pcmdHex.value = value.substring(6, 8);
	hwpid.value = Number.parseInt(value.substring(8, 12), 16);
	hwpidHex.value = value.substring(8, 12);
}

function convertTimestamp(ts: string | undefined): string | undefined {
	if (ts === undefined || ts.length === 0) {
		return undefined;
	}
	return DateTime.fromISO(ts).toLocaleString(
		DateTime.DATETIME_SHORT_WITH_SECONDS,
	);
}
</script>
