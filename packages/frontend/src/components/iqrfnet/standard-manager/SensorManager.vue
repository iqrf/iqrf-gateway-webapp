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
			<v-table v-if='responseType !== null'>
				<thead>
					<tr>
						<th>{{ $t('components.iqrfnet.standard-manager.sensor.name') }}</th>
						<th>{{ $t('components.iqrfnet.standard-manager.sensor.type') }}</th>
						<th>{{ $t('components.iqrfnet.standard-manager.sensor.index') }}</th>
						<th v-if='responseType === ResponseType.READ'>
							{{ $t('components.iqrfnet.standard-manager.sensor.value') }}
						</th>
					</tr>
				</thead>
				<tbody>
					<tr
						v-for='(item, idx) of sensors'
						:key='`sen-${item.type}-${idx}`'
					>
						<td>{{ item.name }}</td>
						<td>{{ item.type }}</td>
						<td>{{ idx }}</td>
						<td v-if='responseType === ResponseType.READ'>
							{{ item.value }}
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
					:loading='componentState === ComponentState.Action && actionType === ActionType.READ'
					:disabled='!isValid.value || componentState === ComponentState.Action && actionType !== ActionType.READ'
					:text='$t("components.iqrfnet.standard-manager.sensor.actions.read")'
					@click='read()'
				/>
			</template>
		</ICard>
	</v-form>
</template>

<script lang='ts' setup>
import { StandardSensorMessages } from '@iqrf/iqrf-gateway-daemon-utils/enums';
import { SensorService } from '@iqrf/iqrf-gateway-daemon-utils/services/standard';
import { DaemonApiResponse } from '@iqrf/iqrf-gateway-daemon-utils/types';
import { DaemonMessageOptions } from '@iqrf/iqrf-gateway-daemon-utils/utils';
import { Action, ComponentState, IActionBtn, ICard, INumberInput, ValidationRules } from '@iqrf/iqrf-vue-ui';
import { mdiDownload, mdiViewList } from '@mdi/js';
import { onBeforeUnmount, ref, Ref, useTemplateRef } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { VForm } from 'vuetify/components';

import { validateForm } from '@/helpers/validateForm';
import { useDaemonStore } from '@/store/daemonSocket';

enum ActionType {
	ENUM = 0,
	READ = 1,
}

enum ResponseType {
	ENUM = 0,
	READ = 1,
}

interface ISensor {
	id: string;
	name: string;
	shortName: string;
	type: number;
	unit: string;
	decimalPlaces: number;
	frcs: number[];
	value?: number|number[];
	breakdown?: ISensor;
}

const componentState: Ref<ComponentState> = ref(ComponentState.Idle);
const daemonStore = useDaemonStore();
const i18n = useI18n();
const form: Ref<VForm|null> = useTemplateRef('form');
const address: Ref<number> = ref(0);
const actionType: Ref<ActionType | null> = ref(null);
const responseType: Ref<ResponseType | null> = ref(null);
const sensors: Ref<ISensor[]> = ref([]);
const msgId: Ref<string | null> = ref(null);

daemonStore.$onAction(({ name, after }) => {
	if (name === 'onMessage') {
		after((rsp: DaemonApiResponse) => {
			if (rsp.data.msgId !== msgId.value) {
				return;
			}
			daemonStore.removeMessage(msgId.value);
			componentState.value = ComponentState.Idle;
			if (rsp.mType === StandardSensorMessages.Enumerate) {
				handleEnumerate(rsp);
			} else if (rsp.mType === StandardSensorMessages.ReadSensorsWithTypes) {
				handleRead(rsp);
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
		i18n.t('components.iqrfnet.standard-manager.sensor.messages.enumerate.timeout'),
		() => {
			msgId.value = null;
			componentState.value = ComponentState.Idle;
		},
	);
	msgId.value = await daemonStore.sendMessage(
		SensorService.enumerate(
			{ addr: address.value },
			opts,
		),
	);
}

function handleEnumerate(rsp: Record<string, any>): void {
	if (rsp.data.status !== 0) {
		handleError(
			rsp.data.status,
			i18n.t('components.iqrfnet.standard-manager.sensor.messages.enumerate.timeout'),
			i18n.t('components.iqrfnet.standard-manager.sensor.messages.enumerate.failed'),
		);
		return;
	}
	sensors.value = rsp.data.rsp.result.sensors;
	toast.success(
		i18n.t('components.iqrfnet.standard-manager.sensor.messages.enumerate.success'),
	);
	responseType.value = ResponseType.ENUM;
}

async function read(): Promise<void> {
	if (!await validateForm(form.value)) {
		return;
	}
	actionType.value = ActionType.READ;
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
		SensorService.readSensorsWithTypes(
			{ addr: address.value },
			{ sensorIndexes: -1 },
			opts,
		),
	);
}

function handleRead(rsp: DaemonApiResponse): void {
	if (rsp.data.status !== 0) {
		handleError(
			rsp.data.status,
			i18n.t('components.iqrfnet.standard-manager.sensor.messages.read.timeout'),
			i18n.t('components.iqrfnet.standard-manager.sensor.messages.read.failed'),
		);
		return;
	}
	sensors.value = rsp.data.rsp.result.sensors;
	toast.success(
		i18n.t('components.iqrfnet.standard-manager.sensor.messages.read.success'),
	);
	responseType.value = ResponseType.READ;
}

function handleError(statusCode: number, timeout: string, generalFailure: string): void {
	let message = '';
	switch (statusCode) {
		case -1:
			message = timeout;
			break;
		case 3:
			message = i18n.t('components.iqrfnet.standard-manager.sensor.messages.pnumError');
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
