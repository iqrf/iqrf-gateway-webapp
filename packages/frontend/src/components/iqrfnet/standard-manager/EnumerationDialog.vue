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
	<IModalWindow
		v-model='show'
		persistent
	>
		<template #activator>
			<IActionBtn
				:action='Action.Custom'
				:icon='mdiViewList'
				container-type='card-title'
				:tooltip='$t("components.iqrfnet.standard-manager.standard-devices.actions.enumerate")'
				:disabled='disabled'
				@click='open()'
			/>
		</template>
		<ICard>
			<template #title>
				{{ $t('components.iqrfnet.standard-manager.standard-devices.enumeration.title') }}
			</template>
			<v-table
				density='compact'
			>
				<tbody>
					<tr>
						<th>{{ $t('components.iqrfnet.standard-manager.standard-devices.enumeration.step') }}</th>
						<td class='text-end'>
							{{ `${status.step} / 8` }}
						</td>
					</tr>
					<tr>
						<th>{{ $t('components.iqrfnet.standard-manager.standard-devices.enumeration.time') }}</th>
						<td class='text-end'>
							{{ timeString }}
						</td>
					</tr>
				</tbody>
			</v-table>
			<v-divider class='my-2' />
			<div class='text-center'>
				<v-progress-linear
					:model-value='(status.step / 8) * 100'
					:color='status.color'
					height='24'
					rounded
				/>
				{{ status.message }}
			</div>
			<template #actions>
				<IActionBtn
					:action='Action.Close'
					:disabled='!status.finished'
					@click='close()'
				/>
			</template>
		</ICard>
	</IModalWindow>
</template>

<script lang='ts' setup>
import { DbMessages } from '@iqrf/iqrf-gateway-daemon-utils/enums';
import { DbService } from '@iqrf/iqrf-gateway-daemon-utils/services';
import { DaemonApiResponse } from '@iqrf/iqrf-gateway-daemon-utils/types';
import { DaemonMessageOptions } from '@iqrf/iqrf-gateway-daemon-utils/utils';
import {
	Action,
	IActionBtn,
	ICard,
	IModalWindow,
} from '@iqrf/iqrf-vue-ui';
import { mdiViewList } from '@mdi/js';
import { computed, ref, type Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';

import { useDuration } from '@/composables/duration';
import { DaemonApiSendError } from '@/errors/DaemonApiSendError';
import { useDaemonStore } from '@/store/daemonSocket';

const componentProps = withDefaults(
	defineProps<{
		disabled?: boolean;
	}>(),
	{
		disabled: false,
	},
);
const emit = defineEmits<{
	finished: [];
}>();
const daemonStore = useDaemonStore();
const i18n = useI18n();
const { duration, start, stop, reset } = useDuration();
const show: Ref<boolean> = ref(false);
const msgId: Ref<string | null> = ref(null);
const defaultStatus = {
	step: 0,
	message: '',
	color: 'success',
	finished: false,
};
const status = ref({ ...defaultStatus });
const timeString = computed(() => {
	return new Intl.DateTimeFormat('en-GB', {
		hour: '2-digit',
		minute: '2-digit',
		second: '2-digit',
		hour12: false,
		timeZone: 'UTC',
	}).format(new Date(duration.value));
});

daemonStore.$onAction(({ name, after }) => {
	if (name === 'onError' || name === 'onClose') {
		after(() => {
			conclude(
				false,
				i18n.t('components.iqrfnet.standard-manager.standard-devices.messages.enumeration.interrupted'),
			);
		});
		return;
	}
	if (name === 'onMessage') {
		after((rsp: DaemonApiResponse) => {
			if (rsp.data.msgId !== msgId.value) {
				return;
			}
			if (rsp.mType === DbMessages.Enumerate) {
				handleEnumerate(rsp);
			}
		});
	}
});

async function enumerate(): Promise<void> {
	const opts = new DaemonMessageOptions(null);
	try {
		msgId.value = await daemonStore.sendMessage(
			DbService.enumerate(
				{ reenumerate: true, standards: true },
				opts,
			),
		);
	} catch (error) {
		if (error instanceof DaemonApiSendError) {
			console.error(error);
			conclude(false, error.message);
		}
	}
}

function handleEnumerate(rsp: DaemonApiResponse): void {
	if (rsp.data.status !== 0) {
		toast.error(
			i18n.t('components.iqrfnet.standard-manager.standard-devices.messages.enumeration.failed'),
		);
		conclude(false, rsp.data.rsp.errorStr);
		return;
	}
	status.value.step = rsp.data.rsp.step;
	status.value.message = rsp.data.rsp.stepStr;
	if (status.value.step === 8) {
		conclude(true, status.value.message);
		toast.success(
			i18n.t('components.iqrfnet.standard-manager.standard-devices.messages.enumeration.success'),
		);
	}
}

function open(): void {
	show.value = true;
	status.value = { ...defaultStatus };
	start();
	enumerate();
}

function conclude(success: boolean, message: string): void {
	daemonStore.removeMessage(msgId.value);
	status.value.finished = true;
	status.value.message = message;
	status.value.color = success ? 'success' : 'red';
	stop();
}

function close(): void {
	show.value = false;
	reset();
	emit('finished');
}
</script>
