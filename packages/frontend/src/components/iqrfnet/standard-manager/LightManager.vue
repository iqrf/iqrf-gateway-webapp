<template>
	<v-form
		ref='form'
		v-slot='{ isValid }'
		:disabled='componentState === ComponentState.Action'
	>
		<ICard>
			<ISelectInput
				v-model='actionType'
				:items='actionOptions'
				:label='$t("components.iqrfnet.standard-manager.light.dpaCommand")'
			/>
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
			<INumberInput
				v-if='actionType === ActionType.SET_LAI'
				v-model='voltage'
				:label='$t("components.iqrfnet.standard-manager.light.voltage")'
				:rules='[
					(v: number|null) => ValidationRules.required(v, $t("components.iqrfnet.standard-manager.light.validation.voltage.required")),
					(v: number) => ValidationRules.between(v, 0, 10, $t("components.iqrfnet.standard-manager.light.validation.voltage.between")),
				]'
				:min='0'
				:max='10'
				:step='0.001'
				:precision='3'
			/>
			<INumberInput
				v-for='i of commands.length'
				v-else
				:key='`cmd-input-${i}`'
				v-model='commands[i-1]'
				:label='$t("components.iqrfnet.standard-manager.light.command")'
				:rules='[
					(v: number|null) => ValidationRules.required(v, $t("components.iqrfnet.standard-manager.light.validation.command.required")),
					(v: number) => ValidationRules.integer(v, $t("components.iqrfnet.standard-manager.light.validation.command.integer")),
					(v: number) => ValidationRules.between(v, 0, 65535, $t("components.iqrfnet.standard-manager.light.validation.command.between")),
				]'
				:min='0'
				:max='65535'
			>
				<template #append>
					<IActionBtn
						v-if='i === commands.length'
						:action='Action.Add'
						size='small'
						@click='addLdiCommand()'
					/>
					<IActionBtn
						v-if='commands.length > 1'
						:action='Action.Delete'
						size='small'
						@click='removeLdiCommand(i-1)'
					/>
				</template>
			</INumberInput>
			<v-table v-if='responseType === ResponseType.SET_LAI'>
				<tbody>
					<tr>
						<th>{{ $t('components.iqrfnet.standard-manager.light.prevVoltage') }}</th>
						<td>{{ prevVoltage }}</td>
					</tr>
				</tbody>
			</v-table>
			<v-table v-if='responseType === ResponseType.SEND_COMMANDS'>
				<thead>
					<tr>
						<th>{{ $t('components.iqrfnet.standard-manager.light.command') }}</th>
						<th>{{ $t('components.iqrfnet.standard-manager.light.status') }}</th>
						<th>{{ $t('components.iqrfnet.standard-manager.light.value') }}</th>
					</tr>
				</thead>
				<tbody>
					<tr
						v-for='(cmd, idx) of commands'
						:key='`cmd-${idx}`'
					>
						<td>{{ cmd }}</td>
						<td>{{ answers[idx].status }}</td>
						<td>{{ answers[idx].value }}</td>
					</tr>
				</tbody>
			</v-table>
			<template #actions>
				<IActionBtn
					v-if='actionType === ActionType.SEND_COMMANDS'
					:action='Action.Custom'
					color='primary'
					:icon='mdiViewList'
					:loading='componentState === ComponentState.Action && actionType === ActionType.SEND_COMMANDS'
					:disabled='!isValid.value || componentState === ComponentState.Action && actionType !== ActionType.SEND_COMMANDS'
					:text='$t("components.iqrfnet.standard-manager.light.actions.sendCommands")'
					@click='sendCommands()'
				/>
				<IActionBtn
					v-else
					:action='Action.Custom'
					color='primary'
					:icon='mdiDownload'
					:loading='componentState === ComponentState.Action && actionType === ActionType.SET_LAI'
					:disabled='!isValid.value || componentState === ComponentState.Action && actionType !== ActionType.SET_LAI'
					:text='$t("components.iqrfnet.standard-manager.light.actions.setLai")'
					@click='setLai()'
				/>
			</template>
		</ICard>
	</v-form>
</template>

<script lang='ts' setup>
import { StandardLightMessages } from '@iqrf/iqrf-gateway-daemon-utils/enums';
import { LightService } from '@iqrf/iqrf-gateway-daemon-utils/services/standard';
import { DaemonApiResponse } from '@iqrf/iqrf-gateway-daemon-utils/types';
import { DaemonMessageOptions } from '@iqrf/iqrf-gateway-daemon-utils/utils';
import { Action, ComponentState, IActionBtn, ICard, INumberInput, ISelectInput, ValidationRules } from '@iqrf/iqrf-vue-ui';
import { mdiDownload, mdiViewList } from '@mdi/js';
import { onBeforeUnmount, ref, Ref, useTemplateRef } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { VForm } from 'vuetify/components';

import { DaemonApiSendError } from '@/errors/DaemonApiSendError';
import { validateForm } from '@/helpers/validateForm';
import { useDaemonStore } from '@/store/daemonSocket';

enum ActionType {
	SEND_COMMANDS = 0,
	SET_LAI = 1,
}

enum ResponseType {
	SEND_COMMANDS = 0,
	SET_LAI = 1,
}

interface LdiCommandAnswer {
	value: number;
	status: number;
}

const componentState: Ref<ComponentState> = ref(ComponentState.Idle);
const daemonStore = useDaemonStore();
const i18n = useI18n();
const form: Ref<VForm|null> = useTemplateRef('form');
const actionType: Ref<ActionType> = ref(ActionType.SEND_COMMANDS);
const address: Ref<number> = ref(0);
const commands: Ref<number[]> = ref([0]);
const answers: Ref<LdiCommandAnswer[]> = ref([]);
const voltage: Ref<number> = ref(0);
const prevVoltage: Ref<number> = ref(0);
const responseType: Ref<ResponseType | null> = ref(null);
const msgId: Ref<string | null> = ref(null);

const actionOptions = ref([
	{
		title: i18n.t('components.iqrfnet.standard-manager.light.actions.sendCommands'),
		value: ActionType.SEND_COMMANDS,
	},
	{
		title: i18n.t('components.iqrfnet.standard-manager.light.actions.setLai'),
		value: ActionType.SET_LAI,
	},
]);

daemonStore.$onAction(({ name, after }) => {
	if (name === 'onMessage') {
		after((rsp: DaemonApiResponse) => {
			if (rsp.data.msgId !== msgId.value) {
				return;
			}
			daemonStore.removeMessage(msgId.value);
			componentState.value = ComponentState.Idle;
			if (rsp.mType === StandardLightMessages.SendLdiCommands) {
				handleSendCommands(rsp);
			} else if (rsp.mType === StandardLightMessages.SetLai) {
				handleSetLai(rsp);
			}
		});
	}
});


function addLdiCommand(): void {
	commands.value.push(0);
}

function removeLdiCommand(index: number): void {
	commands.value.splice(index, 1);
}

async function sendCommands(): Promise<void> {
	if (!await validateForm(form.value)) {
		return;
	}
	actionType.value = ActionType.SEND_COMMANDS;
	responseType.value = null;
	componentState.value = ComponentState.Action;
	const opts = new DaemonMessageOptions(
		null,
		30_000,
		i18n.t('components.iqrfnet.standard-manager.light.messages.sendCommands.timeout'),
		() => {
			msgId.value = null;
			componentState.value = ComponentState.Idle;
		},
	);
	try {
		msgId.value = await daemonStore.sendMessage(
			LightService.sendLdiCommands(
				{ addr: address.value },
				{ commands: commands.value },
				opts,
			),
		);
	} catch (error) {
		if (error instanceof DaemonApiSendError) {
			console.error(error);
			toast.error(error.message);
		}
		componentState.value = ComponentState.Idle;
	}
}

function handleSendCommands(rsp: Record<string, any>): void {
	if (rsp.data.status !== 0) {
		handleError(
			rsp.data.status,
			i18n.t('components.iqrfnet.standard-manager.light.messages.sendCommands.timeout'),
			i18n.t('components.iqrfnet.standard-manager.light.messages.sendCommands.failed'),
		);
		return;
	}
	answers.value = rsp.data.rsp.result.answers;
	toast.success(
		i18n.t('components.iqrfnet.standard-manager.light.messages.sendCommands.success'),
	);
	responseType.value = ResponseType.SEND_COMMANDS;
}

async function setLai(): Promise<void> {
	if (!await validateForm(form.value)) {
		return;
	}
	actionType.value = ActionType.SET_LAI;
	responseType.value = null;
	componentState.value = ComponentState.Action;
	const opts = new DaemonMessageOptions(
		null,
		30_000,
		i18n.t('components.iqrfnet.standard-manager.light.messages.setLai.timeout'),
		() => {
			msgId.value = null;
			componentState.value = ComponentState.Idle;
		},
	);
	try {
		msgId.value = await daemonStore.sendMessage(
			LightService.setLai(
				{ addr: address.value },
				{ voltage: voltage.value },
				opts,
			),
		);
	} catch (error) {
		if (error instanceof DaemonApiSendError) {
			console.error(error);
			toast.error(error.message);
		}
		componentState.value = ComponentState.Idle;
	}
}

function handleSetLai(rsp: DaemonApiResponse): void {
	if (rsp.data.status !== 0) {
		handleError(
			rsp.data.status,
			i18n.t('components.iqrfnet.standard-manager.light.messages.setLai.timeout'),
			i18n.t('components.iqrfnet.standard-manager.light.messages.setLai.failed'),
		);
		return;
	}
	prevVoltage.value = rsp.data.rsp.result.prevVoltage;
	toast.success(
		i18n.t('components.iqrfnet.standard-manager.light.messages.setLai.success'),
	);
	responseType.value = ResponseType.SET_LAI;
}

function handleError(statusCode: number, timeout: string, generalFailure: string): void {
	let message = '';
	switch (statusCode) {
		case -1:
			message = timeout;
			break;
		case 3:
			message = i18n.t('components.iqrfnet.standard-manager.light.messages.pnumError');
			break;
		case 8:
			message = i18n.t('common.messages.noDevice', { address: address.value });
			break;
		default:
			message = generalFailure;
	}
	toast.error(message);
}

onBeforeUnmount(() => {
	daemonStore.removeMessage(msgId.value);
});

</script>
