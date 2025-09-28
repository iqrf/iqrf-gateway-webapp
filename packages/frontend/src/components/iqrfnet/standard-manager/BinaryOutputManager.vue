<template>
	<v-form
		ref='form'
		v-slot='{ isValid }'
		:disabled='componentState === ComponentState.Action'
	>
		<ICard>
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
				v-model='index'
				:label='$t("components.iqrfnet.standard-manager.binary-output.index")'
				:rules='[
					(v: number|null) => ValidationRules.required(v, $t("components.iqrfnet.standard-manager.binary-output.validation.index.required")),
					(v: number) => ValidationRules.integer(v, $t("components.iqrfnet.standard-manager.binary-output.validation.index.integer")),
					(v: number) => ValidationRules.between(v, 0, 31, $t("components.iqrfnet.standard-manager.binary-output.validation.index.between")),
				]'
				:min='0'
				:max='31'
			/>
			<v-switch
				v-model='state'
				:label='$t("components.iqrfnet.standard-manager.binary-output.state")'
				density='compact'
				color='primary'
				inset
				hide-details
			/>
			<v-table v-if='responseType === ResponseType.ENUM'>
				<tbody>
					<tr>
						<th>{{ $t('components.iqrfnet.standard-manager.binary-output.binoutCount') }}</th>
						<td>{{ count }}</td>
					</tr>
				</tbody>
			</v-table>
			<v-table v-if='responseType === ResponseType.SET_STATE'>
				<tbody>
					<tr>
						<th>{{ $t('components.iqrfnet.standard-manager.binary-output.index') }}</th>
						<td
							v-for='val of states.keys()'
							:key='`index-${val}`'
						>
							<b>{{ val }}</b>
						</td>
					</tr>
					<tr>
						<th>{{ $t('components.iqrfnet.standard-manager.binary-output.state') }}</th>
						<td
							v-for='(val, idx) of states'
							:key='`state-${idx}`'
						>
							<IBooleanIcon :value='val' />
						</td>
					</tr>
				</tbody>
			</v-table>
			<template #actions>
				<IActionBtn
					:action='Action.Custom'
					color='primary'
					:icon='mdiViewList'
					:loading='componentState === ComponentState.Action && actionType === ActionType.ENUM'
					:disabled='!isValid.value || componentState === ComponentState.Action && actionType !== ActionType.ENUM'
					:text='$t("common.buttons.enumerate")'
					@click='enumerate()'
				/>
				<IActionBtn
					:action='Action.Custom'
					color='primary'
					:icon='mdiDownload'
					:loading='componentState === ComponentState.Action && actionType === ActionType.GET_STATES'
					:disabled='!isValid.value || componentState === ComponentState.Action && actionType !== ActionType.GET_STATES'
					:text='$t("components.iqrfnet.standard-manager.binary-output.actions.getStates")'
					@click='getOutputs()'
				/>
				<IActionBtn
					:action='Action.Custom'
					color='primary'
					:icon='mdiUpload'
					:loading='componentState === ComponentState.Action && actionType === ActionType.SET_STATE'
					:disabled='!isValid.value || componentState === ComponentState.Action && actionType !== ActionType.SET_STATE'
					:text='$t("components.iqrfnet.standard-manager.binary-output.actions.setState")'
					@click='setOutput()'
				/>
			</template>
		</ICard>
	</v-form>
</template>

<script lang='ts' setup>
import { StandardBinaryOutputMessages } from '@iqrf/iqrf-gateway-daemon-utils/enums';
import { BinaryOutputService } from '@iqrf/iqrf-gateway-daemon-utils/services/standard';
import { DaemonApiResponse } from '@iqrf/iqrf-gateway-daemon-utils/types';
import { DaemonMessageOptions } from '@iqrf/iqrf-gateway-daemon-utils/utils';
import { Action, ComponentState, IActionBtn, IBooleanIcon, ICard, INumberInput, ValidationRules } from '@iqrf/iqrf-vue-ui';
import { mdiDownload, mdiUpload, mdiViewList } from '@mdi/js';
import { onBeforeUnmount, ref, Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { VForm } from 'vuetify/components';

import { validateForm } from '@/helpers/validateForm';
import { useDaemonStore } from '@/store/daemonSocket';

enum ActionType {
	ENUM = 0,
	GET_STATES = 1,
	SET_STATE = 2,
}

enum ResponseType {
	ENUM = 0,
	SET_STATE = 1,
}

const componentState: Ref<ComponentState> = ref(ComponentState.Idle);
const daemonStore = useDaemonStore();
const i18n = useI18n();
const form: Ref<VForm | null> = ref(null);
const address: Ref<number> = ref(0);
const index: Ref<number> = ref(0);
const state: Ref<boolean> = ref(false);
const count: Ref<number> = ref(0);
const states: Ref<boolean[]> = ref(new Array(32).fill(false));
const actionType: Ref<ActionType | null> = ref(null);
const responseType: Ref<ResponseType | null> = ref(null);
const msgId: Ref<string | null> = ref(null);

daemonStore.$onAction(({ name, after }) => {
	if (name === 'onMessage') {
		after((rsp: DaemonApiResponse) => {
			if (rsp.data.msgId !== msgId.value) {
				return;
			}
			daemonStore.removeMessage(msgId.value);
			componentState.value = ComponentState.Idle;
			if (rsp.mType === StandardBinaryOutputMessages.Enumerate) {
				handleEnumerate(rsp);
			} else if (rsp.mType === StandardBinaryOutputMessages.SetOutput) {
				handleSetOutput(rsp);
			}
		});
	}
});

async function enumerate(): Promise<void> {
	if (!await validateForm(form.value)) {
		return;
	}
	actionType.value = ActionType.ENUM;
	responseType.value = null;
	componentState.value = ComponentState.Action;
	const opts = new DaemonMessageOptions(
		null,
		30_000,
		i18n.t('components.iqrfnet.standard-manager.binary-output.messages.enumerate.timeout'),
		() => {
			msgId.value = null;
			componentState.value = ComponentState.Idle;
		},
	);
	msgId.value = await daemonStore.sendMessage(
		BinaryOutputService.enumerate(
			{ addr: address.value },
			opts,
		),
	);
}

function handleEnumerate(rsp: Record<string, any>): void {
	if (rsp.data.status !== 0) {
		handleError(
			rsp.data.status,
			i18n.t('components.iqrfnet.standard-manager.binary-output.messages.enumerate.timeout'),
			i18n.t('components.iqrfnet.standard-manager.binary-output.messages.enumerate.failed'),
		);
		return;
	}
	count.value = rsp.data.rsp.result.binOuts;
	toast.success(
		i18n.t('components.iqrfnet.standard-manager.binary-output.messages.enumerate.success'),
	);
	responseType.value = ResponseType.ENUM;
}

async function getOutputs(): Promise<void> {
	if (!await validateForm(form.value)) {
		return;
	}
	actionType.value = ActionType.GET_STATES;
	responseType.value = null;
	componentState.value = ComponentState.Action;
	const opts = new DaemonMessageOptions(
		null,
		30_000,
		i18n.t('components.iqrfnet.standard-manager.binary-output.messages.getStates.timeout'),
		() => {
			msgId.value = null;
			componentState.value = ComponentState.Idle;
		},
	);
	msgId.value = await daemonStore.sendMessage(
		BinaryOutputService.setOutput(
			{ addr: address.value },
			{ binOuts: [] },
			opts,
		),
	);
}

async function setOutput(): Promise<void> {
	if (!await validateForm(form.value)) {
		return;
	}
	actionType.value = ActionType.SET_STATE;
	componentState.value = ComponentState.Action;
	const opts = new DaemonMessageOptions(
		null,
		30_000,
		i18n.t('components.iqrfnet.standard-manager.binary-output.messages.getStates.timeout'),
		() => {
			msgId.value = null;
			componentState.value = ComponentState.Idle;
		},
	);
	msgId.value = await daemonStore.sendMessage(
		BinaryOutputService.setOutput(
			{ addr: address.value },
			{ binOuts: [{ index: index.value, state: state.value }] },
			opts,
		),
	);
}

function parseSetOutput(result: boolean[]): void {
	for(let i = 0; i < result.length; ++i) {
		states.value[i] = result[i];
	}
}

function handleSetOutput(rsp: Record<string, any>): void {
	if (rsp.data.status !== 0) {
		handleError(
			rsp.data.status,
			i18n.t('components.iqrfnet.standard-manager.binary-output.messages.states.timeout'),
			i18n.t('components.iqrfnet.standard-manager.binary-output.messages.states.failed'),
		);
		return;
	}
	parseSetOutput(rsp.data.rsp.result.prevVals);
	toast.success(
		i18n.t('components.iqrfnet.standard-manager.binary-output.messages.states.success'),
	);
	responseType.value = ResponseType.SET_STATE;
}

function handleError(statusCode: number, timeout: string, generalFailure: string): void {
	let message = '';
	switch(statusCode) {
		case -1:
			message = timeout;
			break;
		case 3:
			message = i18n.t('components.iqrfnet.standard-manager.binary-output.messages.pnumError');
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
