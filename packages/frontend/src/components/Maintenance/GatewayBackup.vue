<!--
Copyright 2017-2024 IQRF Tech s.r.o.
Copyright 2019-2024 MICRORISC s.r.o.

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
	<v-card>
		<v-card-title>
			{{ $t('maintenance.backup.backupTitle') }}
			<v-spacer />
			<v-item-group>
				<v-btn
					color='primary'
					small
					@click='setAll(true)'
				>
					{{ $t('maintenance.backup.form.selectAll') }}
				</v-btn> <v-btn
					color='secondary'
					small
					@click='setAll(false)'
				>
					{{ $t('maintenance.backup.form.deselectAll') }}
				</v-btn>
			</v-item-group>
		</v-card-title>
		<v-card-text>
			<v-form @submit.prevent='backup'>
				<v-row>
					<v-col cols='12' md='6'>
						<h5>{{ $t('maintenance.backup.headings.software') }}</h5>
						<v-checkbox
							v-model='migration.software.iqrf'
							:label='$t("maintenance.backup.form.software.iqrf")'
							dense
						/>
						<v-checkbox
							v-if='$store.getters["features/isEnabled"]("mender")'
							v-model='migration.software.mender'
							:label='$t("maintenance.backup.form.software.mender")'
							dense
						/>
						<v-checkbox
							v-if='$store.getters["features/isEnabled"]("monit")'
							v-model='migration.software.monit'
							:label='$t("maintenance.backup.form.software.monit")'
							dense
						/>
					</v-col>
					<v-col cols='12' md='6'>
						<h5>{{ $t('maintenance.backup.headings.system') }}</h5>
						<v-checkbox
							v-model='migration.system.hostname'
							:label='$t("maintenance.backup.form.system.hostname")'
							dense
						/>
						<v-checkbox
							v-if='$store.getters["features/isEnabled"]("networkManager")'
							v-model='migration.system.network'
							:label='$t("maintenance.backup.form.system.network")'
							dense
						/>
						<v-checkbox
							v-model='migration.system.time'
							:label='$t("maintenance.backup.form.system.time")'
							dense
						/>
						<v-checkbox
							v-if='$store.getters["features/isEnabled"]("journal")'
							v-model='migration.system.journal'
							:label='$t("maintenance.backup.form.system.journal")'
							dense
						/>
					</v-col>
				</v-row>
				<v-btn
					color='primary'
					type='submit'
				>
					{{ $t('maintenance.backup.form.backup') }}
				</v-btn>
			</v-form>
		</v-card-text>
	</v-card>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';

import {extendedErrorToast} from '@/helpers/errorToast';
import {fileDownloader} from '@/helpers/fileDownloader';

import BackupService from '@/services/BackupService';

import {AxiosError, AxiosResponse} from 'axios';
import {IBackup} from '@/interfaces/Maintenance/Backup';

/**
 * Gateway backup component
 */
@Component
export default class GatewayBackup extends Vue {
	/**
	 * @var {IBackup} migration Gateway migration request
	 */
	private migration: IBackup = {
		software: {
			iqrf: false,
			monit: false,
			mender: false,
		},
		system: {
			hostname: false,
			network: false,
			time: false,
			journal: false,
		},
	};

	/**
	 * Sets all checkboxes to specified value
	 * @param {boolean} checked Checked value
	 */
	private setAll(checked: boolean): void {
		Object.keys(this.migration.software).forEach((key: string) => {
			return this.migration.software[key] = checked;
		});
		Object.keys(this.migration.system).forEach((key: string) => {
			return this.migration.system[key] = checked;
		});
	}

	/**
	 * Performs gateway backup
	 */
	private backup(): void {
		this.$store.commit('spinner/SHOW');
		this.$store.commit('spinner/UPDATE_TEXT', this.$t('maintenance.backup.messages.backup').toString());
		const params: IBackup = this.filterFeatures(JSON.parse(JSON.stringify(this.migration)));
		BackupService.backup(params)
			.then((response: AxiosResponse) => {
				const fileName = 'iqrf-gateway-backup_' + new Date().toISOString();
				const file = fileDownloader(response, 'application/zip', fileName);
				this.$store.commit('spinner/HIDE');
				file.click();
			})
			.catch((error: AxiosError) => extendedErrorToast(error, 'maintenance.backup.messages.backupFailed'));
	}

	/**
	 * Uploads backup archive and attempts to restore gw
	 */
	private filterFeatures(params: IBackup): IBackup {
		if (!this.$store.getters['features/isEnabled']('monit')) {
			params.software.monit = false;
		}
		if (!this.$store.getters['features/isEnabled']('mender')) {
			params.software.mender = false;
		}
		if (!this.$store.getters['features/isEnabled']('networkManager')) {
			params.system.network = false;
		}
		if (!this.$store.getters['features/isEnabled']('journal')) {
			params.system.journal = false;
		}
		return params;
	}

}
</script>
