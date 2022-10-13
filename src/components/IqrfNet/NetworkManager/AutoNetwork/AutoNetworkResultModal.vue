<!--
Copyright 2017-2021 IQRF Tech s.r.o.
Copyright 2019-2021 MICRORISC s.r.o.

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
	<CModal
		color='primary'
		:show.sync='show'
		:close-on-backdrop='false'
		:fade='false'
	>
		<template #header>
			<h5 class='modal-title'>
				{{ $t('iqrfnet.networkManager.autoNetwork.resultModal.title') }}
			</h5>
		</template>
		<table class='modal-table'>
			<tr>
				<th>{{ $t('iqrfnet.networkManager.autoNetwork.resultModal.wave') }}</th>
				<td>{{ result.waves === 0 ? result.wave : result.wave + '/' + result.waves }}</td>
			</tr>
			<tr>
				<th>{{ $t('iqrfnet.networkManager.autoNetwork.resultModal.empty') }}</th>
				<td>{{ result.emptyWaves }}</td>
			</tr>
			<tr>
				<th>{{ $t('iqrfnet.networkManager.autoNetwork.resultModal.total') }}</th>
				<td>{{ result.totalNodes }}</td>
			</tr>
			<tr>
				<th>{{ $t('iqrfnet.networkManager.autoNetwork.resultModal.new') }}</th>
				<td>{{ result.newNodes }}</td>
			</tr>
			<tr>
				<th>{{ $t('iqrfnet.networkManager.autoNetwork.resultModal.time') }}</th>
				<td>{{ timeString }}</td>
			</tr>
		</table><hr>
		<div class='text-center'>
			<CProgress
				class='autonetwork-progress-bar'
				:color='result.progressColor'
				:value='result.progress'
				:animated='!result.finished'
			/>
			<div>{{ result.status }}</div>
		</div>
		<template #footer>
			<CButton
				color='secondary'
				:disabled='!result.finished'
				@click='deactivateModal'
			>
				{{ $t('forms.close') }}
			</CButton>
		</template>
	</CModal>
</template>

<script lang='ts'>
import {Component} from 'vue-property-decorator';
import {CButton, CModal, CProgress} from '@coreui/vue/src';
import ModalBase from '@/components/ModalBase.vue';

@Component({
	components: {
		CButton,
		CModal,
		CProgress,
	},
})

/**
 * AutoNetwork result modal window component
 */
export default class AutoNetworkResult extends ModalBase {
	/**
	 * @const defaultResult Autonetwork default result
	 */
	private defaultResult = {
		waves: 0,
		emptyWaves: 0,
		wave: 1,
		totalNodes: 0,
		newNodes: 0,
		progress: 0,
		progressColor: 'success',
		status: this.$t('iqrfnet.networkManager.autoNetwork.resultModal.startMessage').toString(),
		finished: false,
	};

	/**
	 * @var result Autonetwork result
	 */
	private result = JSON.parse(JSON.stringify(this.defaultResult));

	/**
	 * @var {number} timerTimeoutId Window timeout ID
	 */
	private timerIntervalId = 0;

	/**
	 * @var {number} startTime Start time timestamp
	 */
	private startTime = 0;

	/**
	 * @var {number} time Current process time in milliseconds
	 */
	private time = 0;

	/**
	 * Converts timer from milliseconds to time string
	 */
	get timeString(): string {
		return new Date(this.time).toISOString().substr(11, 8);
	}

	/**
	 * Passes autonetwork parameters and activates autonetwork result modal
	 * @param {number} waves Number of total waves
	 * @param {number} emptyWaves Number of empty waves
	 */
	public showModal(waves = 0, emptyWaves: number): void {
		this.result.waves = waves;
		this.result.emptyWaves = emptyWaves;
		this.openModal();
		this.startTimer();
	}

	/**
	 * Starts autonetwork process timer
	 */
	private startTimer(): void {
		this.startTime = Date.now();
		this.timerIntervalId = window.setInterval(() => {
			this.time = Date.now() - this.startTime;
		}, 300);
	}

	/**
	 * Stops autonetwork process timer
	 */
	private stopTimer(): void {
		window.clearInterval(this.timerIntervalId);
	}

	/**
	 * Updates autoentwork progress
	 * @param {number} wave Current wave
	 * @param {boolean} lastWave Is the current wave last wave?
	 * @param {number} progress Wave progress
	 * @param {string} status Wave status
	 * @param {number|null} totalNodes Total nodes in network
	 * @param {number|null} newNodes New nodes found in wave
	 */
	public updateProgress(wave: number, lastWave: boolean, progress: number, status: string, totalNodes: number|null = null, newNodes: number|null = null): void {
		this.result.wave = wave;
		this.result.progress = progress;
		if (totalNodes !== null) {
			this.result.totalNodes = totalNodes;
		}
		if (newNodes !== null) {
			this.result.newNodes += newNodes;
		}
		if (lastWave) {
			this.stopTimer();
			this.result.finished = true;
			this.result.status = this.$t('iqrfnet.networkManager.autoNetwork.resultModal.endMessage', {message: status}).toString();
		} else {
			this.result.status = status;
		}
	}

	/**
	 * Stops progress and sets status message
	 * @param {string} status Wave status
	 */
	public stopProgress(status: string): void {
		this.stopTimer();
		this.result.progressColor = 'danger';
		this.result.status = status;
		this.result.finished = true;
	}

	/**
	 * Resets autonetwork result and closes the modal window
	 */
	private deactivateModal(): void {
		this.closeModal();
		this.$emit('finished');
		this.result = JSON.parse(JSON.stringify(this.defaultResult));
	}
}
</script>

<style scoped>
.autonetwork-progress-bar {
	height: 15px;
}

</style>
