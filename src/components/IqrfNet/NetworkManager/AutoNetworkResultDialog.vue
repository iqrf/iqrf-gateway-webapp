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
	<v-dialog
		v-model='show'
		width='50%'
		persistent
		no-click-animation
	>
		<v-card>
			<v-card-title>{{ $t('iqrfnet.networkManager.autoNetwork.resultModal.title') }}</v-card-title>
			<v-card-text>
				<table>
					<tbody>
						<tr>
							<th>{{ $t('iqrfnet.networkManager.autoNetwork.resultModal.wave') }}</th>
							<td>{{ waves === 0 ? wave : wave + '/' + waves }}</td>
						</tr>
						<tr>
							<th>{{ $t('iqrfnet.networkManager.autoNetwork.resultModal.empty') }}</th>
							<td>{{ emptyWaves }}</td>
						</tr>
						<tr>
							<th>{{ $t('iqrfnet.networkManager.autoNetwork.resultModal.total') }}</th>
							<td>{{ totalNodes }}</td>
						</tr>
						<tr>
							<th>{{ $t('iqrfnet.networkManager.autoNetwork.resultModal.new') }}</th>
							<td>{{ newNodes }}</td>
						</tr>
						<tr>
							<th>{{ $t('iqrfnet.networkManager.autoNetwork.resultModal.time') }}</th>
							<td>{{ timeString }}</td>
						</tr>
					</tbody>
				</table>
				<v-divider class='my-4' />
				<div class='text-center'>
					<v-progress-linear
						:color='progressColor'
						:value='progress'
						rounded
						height='15'
					/>
					<div>{{ status }}</div>
				</div>
			</v-card-text>
			<v-divider />
			<v-card-actions>
				<v-spacer />
				<v-btn
					:disabled='!finished'
					@click='hideDialog'
				>
					{{ $t('forms.close') }}
				</v-btn>
			</v-card-actions>
		</v-card>
	</v-dialog>
</template>

<script lang='ts'>
import {Component} from 'vue-property-decorator';
import DialogBase from '@/components/DialogBase.vue';

/**
 * AutoNetwork result modal window component
 */
@Component
export default class AutoNetworkResultDialog extends DialogBase {
	/**
	 * @var {string} progressColor Progress bar color
	 */
	private progressColor = 'success';

	/**
	 * @var {number} waves Number of waves
	 */
	private waves = 0;

	/**
	 * @var {number} emptyWaves Number of empty waves
	 */
	private emptyWaves = 0;

	/**
	 * @var {number} wave Current wave
	 */
	private wave = 1;

	/**
	 * @var {number} totalNodes Total nodes in network
	 */
	private totalNodes = 0;

	/**
	 * @var {number} newNodes New nodes added to network
	 */
	private newNodes = 0;

	/**
	 * @var {number} progress Wave progress
	 */
	private progress = 0;

	/**
	 * @var {string} status Wave status
	 */
	private status = this.$t('iqrfnet.networkManager.autoNetwork.resultModal.startMessage').toString();

	/**
	 * @var {string} finished Indicates whether autonetwork process is running
	 */
	private finished = false;

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
	public showDialog(waves = 0, emptyWaves: number): void {
		this.waves = waves;
		this.emptyWaves = emptyWaves;
		this.show = true;
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
	 * @param {number} newNodes New nodes found in this wave
	 */
	public updateProgress(wave: number, lastWave: boolean, progress: number, status: string, totalNodes: number|null = null, newNodes: number|null = null): void {
		this.wave = wave;
		this.progress = progress;
		if (totalNodes !== null) {
			this.totalNodes = totalNodes;
		}
		if (newNodes !== null) {
			this.newNodes += newNodes;
		}
		if (lastWave) {
			this.stopTimer();
			this.finished = true;
			this.status = this.$t('iqrfnet.networkManager.autoNetwork.resultModal.endMessage', {message: status}).toString();
		} else {
			this.status = status;
		}
	}

	/**
	 * Stops progress and sets status message
	 * @param {string} status Wave status
	 */
	public stopProgress(status: string): void {
		this.stopTimer();
		this.progressColor = 'error';
		this.status = status;
		this.finished = true;
	}

	/**
	 * Resets autonetwork result and closes the modal window
	 */
	private hideDialog(): void {
		this.closeDialog();
		this.$emit('finished');
		this.waves = 0;
		this.emptyWaves = 0;
		this.wave = 1;
		this.totalNodes = 0;
		this.newNodes = 0;
		this.progress = 0;
		this.progressColor = 'success';
		this.finished = false;
		this.status = 'AutoNetwork started.';
	}
}
</script>

<style scoped>
table {
	width: 100%;
}

th {
	text-align: left;
}

td {
	text-align: right;
}

</style>
