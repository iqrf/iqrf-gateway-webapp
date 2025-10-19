<template>
	<IModalWindow
		v-model='show'
		persistent
	>
		<ICard>
			<template #title>
				{{ $t('components.iqrfnet.network-manager.autonetwork.result.title') }}
			</template>
			<v-table
				density='compact'
			>
				<tbody>
					<tr>
						<th>{{ $t('components.iqrfnet.network-manager.autonetwork.result.wave') }}</th>
						<td class='text-end'>
							{{ result.waves === 0 ? result.wave : `${result.wave}/${result.waves}` }}
						</td>
					</tr>
					<tr>
						<th>{{ $t('components.iqrfnet.network-manager.autonetwork.result.emptyWaves') }}</th>
						<td class='text-end'>
							{{ result.emptyWaves }}
						</td>
					</tr>
					<tr>
						<th>{{ $t('components.iqrfnet.network-manager.autonetwork.result.total') }}</th>
						<td class='text-end'>
							{{ result.totalNodes }}
						</td>
					</tr>
					<tr>
						<th>{{ $t('components.iqrfnet.network-manager.autonetwork.result.new') }}</th>
						<td class='text-end'>
							{{ result.newNodes }}
						</td>
					</tr>
					<tr>
						<th>{{ $t('components.iqrfnet.network-manager.autonetwork.result.time') }}</th>
						<td class='text-end'>
							{{ timeString }}
						</td>
					</tr>
				</tbody>
			</v-table>
			<v-divider class='my-2' />
			<div class='text-center'>
				<v-progress-linear
					:model-value='result.progress'
					:color='result.progressColor'
					height='24'
					rounded
				/>
				{{ result.status }}
			</div>
			<template #actions>
				<IActionBtn
					:action='Action.Close'
					:disabled='!result.finished'
					@click='close()'
				/>
			</template>
		</ICard>
	</IModalWindow>
</template>

<script lang='ts' setup>
import { Action, IActionBtn, ICard, IModalWindow } from '@iqrf/iqrf-vue-ui';
import { computed, ref, Ref } from 'vue';
import { useI18n } from 'vue-i18n';

import { useDuration } from '@/composables/duration';

defineExpose({
	open,
	updateProgress,
	stopProgress,
});
const emit = defineEmits<{
	updateDevices: [];
}>();
const i18n = useI18n();
const { duration, start, stop, reset } = useDuration();
const show: Ref<boolean> = ref(false);
const defaultResult = ref({
	waves: 0,
	emptyWaves: 0,
	wave: 1,
	totalNodes: 0,
	newNodes: 0,
	progress: 0,
	progressColor: 'success',
	status: i18n.t('components.iqrfnet.network-manager.autonetwork.result.startMessage'),
	finished: false,
});
const result = ref(structuredClone(defaultResult));
const timeString = computed(() => {
	return new Intl.DateTimeFormat('en-GB', {
		hour: '2-digit',
		minute: '2-digit',
		second: '2-digit',
		hour12: false,
		timeZone: 'UTC',
	}).format(new Date(duration.value));
});

function open(): void {
	show.value = true;
	start();
}

function updateProgress(wave: number, lastWave: boolean, progress: number, status: string, totalNodes: number|null = null, newNodes: number|null = null): void {
	result.value.wave = wave;
	result.value.progress = progress;
	if (totalNodes !== null) {
		result.value.totalNodes = totalNodes;
	}
	if (newNodes !== null) {
		if (newNodes === 0) {
			result.value.emptyWaves++;
		} else {
			result.value.newNodes += newNodes;
			result.value.emptyWaves = 0;
		}
	}
	if (lastWave) {
		stop();
		result.value.finished = true;
		result.value.status = i18n.t('components.iqrfnet.network-manager.autonetwork.result.endMessage', { message: status });
	} else {
		result.value.status = status;
	}
}

function stopProgress(status: string): void {
	stop();
	result.value.progressColor = 'red';
	result.value.status = status;
	result.value.finished = true;
}

function close(): void {
	show.value = false;
	reset();
	result.value = { ...defaultResult.value };
	emit('updateDevices');
}

</script>
