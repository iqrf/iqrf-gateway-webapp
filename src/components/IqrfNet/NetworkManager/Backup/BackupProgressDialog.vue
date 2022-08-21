<template>
	<v-dialog
		v-model='show'
		width='50%'
		persistent
		no-click-animation
	>
		<v-card>
			<v-card-title>{{ $t('iqrfnet.networkManager.backupRestore.backup.dialog.title') }}</v-card-title>
			<v-card-text>
				<v-data-table
					v-model='selected'
					:headers='headers'
					:items='devices'
					item-key='deviceAddr'
					show-select
					selectable-key='online'
				>
					<template #[`item.online`]='{item}'>
						<v-icon :color='item.online ? "success" : "error"'>
							{{ item.online ? 'mdi-check-circle-outline' : 'mdi-close-circle-outline' }}
						</v-icon>
					</template>
					<template #[`item.mid`]='{item}'>
						{{ item.mid ?? $t('forms.notAvailable') }}
					</template>
					<template #[`item.dpaVer`]='{item}'>
						{{ item.dpaVer ? getDpaVersion(item.dpaVer) : $t('forms.notAvailable') }}
					</template>
				</v-data-table>
				<v-divider class='my-4' />
				<div class='text-center'>
					<v-progress-linear
						:value='progress'
						:color='progressColor'
						height='15'
						rounded
					/>
					{{ statusMessage }}
				</div>
			</v-card-text>
			<v-card-actions>
				<v-spacer />
				<v-btn
					:disabled='progress < 100'
					@click='closeDialog'
				>
					{{ $t('forms.close') }}
				</v-btn>
				<v-btn
					color='primary'
					:disabled='progress < 100 || selected.length === 0'
					@click='save'
				>
					{{ $t('forms.save') }}
				</v-btn>
			</v-card-actions>
		</v-card>
	</v-dialog>
</template>

<script lang='ts'>
import {Component, Prop} from 'vue-property-decorator';
import DialogBase from '@/components/DialogBase.vue';

import {DateTime} from 'luxon';
import {saveAs} from 'file-saver';

import {NetworkTarget} from '@/enums/IqrfNet/network';

import {IBackupData} from '@/interfaces/iqmeshServices';
import {DataTableHeader} from 'vuetify';

/**
 * Backup progress dialog component
 */
@Component
export default class BackupProgressDialog extends DialogBase {

	@Prop({type: Number, default: 0}) progress!: number;

	@Prop({type: Array, default: []}) devices!: Array<IBackupData>;

	@Prop({type: String, default: 'unknown'}) webappVersion!: string;

	@Prop({required: true, default: NetworkTarget.COORDINATOR}) target!: NetworkTarget;

	/**
	 * @const {Array<DataTableHeader>} headers Data table headers
	 */
	private headers: Array<DataTableHeader> = [
		{
			value: 'deviceAddr',
			text: this.$t('forms.fields.address').toString(),
		},
		{
			value: 'online',
			text: this.$t('forms.online').toString(),
		},
		{
			value: 'mid',
			text: this.$t('gateway.info.tr.moduleId').toString(),
		},
		{
			value: 'dpaVer',
			text: this.$t('gateway.info.tr.dpa').toString(),
		}
	];

	/**
	 * @var {string} progressColor Progress bar color
	 */
	private progressColor = 'success';

	/**
	 * @var {string} error Error message
	 */
	private error = '';

	/**
	 * @var {Array<IBackupData>} selected Devices selected for backup 
	 */
	private selected: Array<IBackupData> = [];

	/**
	 * @var {string} statusMessage Backup process status message
	 */
	get statusMessage(): string {
		if (this.progress < 100) {
			if (this.progressColor === 'success') {
				return this.$t(`iqrfnet.networkManager.backupRestore.backup.messages.${this.target}Running`).toString();
			}
			return this.$t('iqrfnet.networkManager.backupRestore.backup.messages.failed', {error: this.error}).toString();
		}
		return this.$t('iqrfnet.networkManager.backupRestore.backup.messages.finished').toString();
	}
	
	/**
	 * Shows progress dialog
	 */
	public showDialog(): void {
		this.openDialog();
	}

	/**
	 * Sets error state
	 * @param {string} error Error message
	 */
	public setError(error: string): void {
		this.progress = 100;
		this.error = error;
		this.progressColor = 'error';
	}

	private save(): void {
		const filename = `${this.generateTarget()}_${this.generateMid(this.devices[0].mid)}_${this.generateTimestamp()}.iqrfbkp`;
		let content: Array<string> = [
			'[Backup]',
			`Created=${DateTime.now().toFormat('dd.LL.kkkk HH:mm:ss')} IQRF GW Webapp: ${this.webappVersion}\n`,
		];
		if (this.target === NetworkTarget.COORDINATOR) {
			content = content.concat(this.coordinatorEntry(this.selected[0]));
		} else if (this.target === NetworkTarget.NODE) {
			content = content.concat(this.nodeEntry(this.selected[0]));
		} else {
			content = content.concat(this.coordinatorEntry(this.selected[0]));
			for (let idx = 1; idx < this.selected.length; idx++) {
				content = content.concat(this.nodeEntry(this.selected[idx]));
			}
		}
		const blob = new Blob([content.join('\n') + '\n'], {type: 'text/plain;charset=utf-8'});
		saveAs(blob, filename);
	}

	/**
	 * Generates target string for backup file
	 * @return {string} Target string
	 */
	private generateTarget(): string {
		return this.target.charAt(0).toUpperCase() + this.target.slice(1);
	}

	/**
	 * Generates mid string for backup file
	 * @param {number} mid MID integer
	 * @return {string} MID hex string
	 */
	private generateMid(mid: number): string {
		return mid.toString(16).toUpperCase();
	}

	/**
	 * Generates date timestamp for backup file
	 * @return {string} Date timestamp
	 */
	private generateTimestamp(): string {
		return DateTime.now().toFormat('kkLLdd');
	}

	/**
	 * Creates a coordinator device type backup entry
	 * @param {IBackupData} device Coordinator device to convert to backup entry
	 * @returns {Array<string>} Coordinator backup entry
	 */
	private coordinatorEntry(device: IBackupData): Array<string> {
		return [
			`[${this.generateMid(device.mid)}]`,
			'Device=Coordinator',
			`Version=${this.getDpaVersion(device.dpaVer)}`,
			`DataC=${device.data.toUpperCase()}`,
			`Address=${device.deviceAddr}\n`,
		];
	}

	/**
	 * Creates a node device type backup entry
	 * @param {IBackupData} device Node device to convert to backup entry
	 * @returns {Array<string>} Node device backup data
	 */
	private nodeEntry(device: IBackupData): Array<string> {
		return [
			`[${this.generateMid(device.mid)}]`,
			'Device=Node',
			`Version=${this.getDpaVersion(device.dpaVer)}`,
			`DataN=${device.data.toUpperCase()}`,
			`Address=${device.deviceAddr}\n`,
		];
	}

	/**
	 * Converts DPA version from decimal number to string of hexadecimal characters
	 * @returns {string} dpa version hex string
	 */
	private getDpaVersion(version: number): string {
		const major = version >> 8;
		const minor = version & 0xff;
		return major.toString() + '.' + minor.toString(16).padStart(2, '0');
	}
}
</script>
