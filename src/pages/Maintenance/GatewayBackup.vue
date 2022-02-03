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
	<div>
		<h1>{{ $t('maintenance.backup.title') }}</h1>
		<CCard>
			<CCardBody>
				<CForm @submit.prevent='backup'>
					<div class='form-group'>
						<CButton
							color='primary'
							size='sm'
							@click='setAll(true)'
						>
							{{ $t('maintenance.backup.form.selectAll') }}
						</CButton> <CButton
							color='secondary'
							size='sm'
							@click='setAll(false)'
						>
							{{ $t('maintenance.backup.form.deselectAll') }}
						</CButton>
					</div>
					<CRow>
						<CCol>
							<h3>{{ $t('maintenance.backup.headings.software') }}</h3>
							<CInputCheckbox
								:checked.sync='migration.software.iqrf'
								:label='$t("maintenance.backup.form.software.iqrf")'
							/>
							<CInputCheckbox
								v-if='$store.getters["features/isEnabled"]("mender")'
								:checked.sync='migration.software.mender'
								:label='$t("maintenance.backup.form.software.mender")'
							/>
							<CInputCheckbox
								v-if='$store.getters["features/isEnabled"]("monit")'
								:checked.sync='migration.software.monit'
								:label='$t("maintenance.backup.form.software.monit")'
							/>
							<CInputCheckbox
								v-if='$store.getters["features/isEnabled"]("pixla")'
								:checked.sync='migration.software.pixla'
								:label='$t("maintenance.backup.form.software.pixla")'
							/>
						</CCol>
						<CCol>
							<h3>{{ $t('maintenance.backup.headings.system') }}</h3>
							<CInputCheckbox
								:checked.sync='migration.system.hostname'
								:label='$t("maintenance.backup.form.system.hostname")'
							/>
							<CInputCheckbox
								v-if='$store.getters["features/isEnabled"]("networkManager")'
								:checked.sync='migration.system.network'
								:label='$t("maintenance.backup.form.system.network")'
							/>
							<CInputCheckbox
								:checked.sync='migration.system.time'
								:label='$t("maintenance.backup.form.system.time")'
							/>
							<CInputCheckbox
								v-if='$store.getters["features/isEnabled"]("systemdJournal")'
								:checked.sync='migration.system.journal'
								:label='$t("maintenance.backup.form.system.journal")'
							/>
						</CCol>
					</CRow>
					<CButton
						color='primary'
						type='submit'
					>
						{{ $t('maintenance.backup.form.backup') }}
					</CButton>
				</CForm>
			</CCardBody>
		</CCard>
		<CCard>
			<CCardBody>
				<CForm>
					<div class='form-group'>
						<CInputFile
							ref='backupArchive'
							accept='.zip'
							:label='$t("maintenance.backup.form.archive")'
							@click='fileInputEmpty'
							@input='fileInputEmpty'
						/>
						<CButton
							color='primary'
							:disabled='inputEmpty'
							@click.prevent='restore'
						>
							{{ $t('maintenance.backup.form.restore') }}
						</CButton>
					</div>
					<i>{{ $t('maintenance.backup.messages.restoreNote') }}</i>
				</CForm>
			</CCardBody>
		</CCard>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CButton, CCard, CCardBody, CCol, CForm, CInputCheckbox, CInputFile, CRow} from '@coreui/vue/src';

import {extendedErrorToast} from '../../helpers/errorToast';
import {fileDownloader} from '../../helpers/fileDownloader';
import GatewayService from '../../services/GatewayService';

import {AxiosError, AxiosResponse} from 'axios';
import {IGwBackup} from '../../interfaces/backup';

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CCol,
		CForm,
		CInputCheckbox,
		CInputFile,
		CRow
	},
	metaInfo: {
		title: 'maintenance.backup.title',
	},
})

/**
 * Gateway backup component
 */
export default class GatewayBackup extends Vue {

	/**
	 * @var {IGwBackup} migration Gateway migration request
	 */
	private migration: IGwBackup = {
		software: {
			iqrf: false,
			monit: false,
			mender: false,
			pixla: false,
		},
		system: {
			hostname: false,
			network: false,
			time: false,
			journal: false,
		},
	}

	/**
	 * @var {boolean} inputEmpty Indicates whether backup archive file input is empty
	 */
	private inputEmpty = true

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
		let params = this.filterFeatures(JSON.parse(JSON.stringify(this.migration)));
		GatewayService.backup(params)
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
	private filterFeatures(params: IGwBackup): IGwBackup {
		if (!this.$store.getters['features/isEnabled']('monit')) {
			params.software.monit = false;
		}
		if (!this.$store.getters['features/isEnabled']('mender')) {
			params.software.mender = false;
		}
		if (!this.$store.getters['features/isEnabled']('pixla')) {
			params.software.pixla = false;
		}
		if (!this.$store.getters['features/isEnabled']('networkManager')) {
			params.system.network = false;
		}
		if (!this.$store.getters['features/isEnabled']('systemdJournal')) {
			params.system.journal = false;
		}
		return params;
	}

	/**
	 * Performs gateway restore
	 */
	private restore(): void {
		const files = this.getFiles();
		if (files === null || files.length === 0) {
			return;
		}
		this.$store.commit('spinner/SHOW');
		this.$store.commit('spinner/UPDATE_TEXT', this.$t('maintenance.backup.messages.restore').toString());
		GatewayService.restore(files[0])
			.then((response: AxiosResponse) => {
				const time = new Date(response.data.timestamp * 1000).toLocaleTimeString();
				this.$store.commit('spinner/HIDE');
				this.$toast.success(
					this.$t('maintenance.backup.messages.restoreSuccess', {time: time}).toString()
				);
			})
			.catch((error: AxiosError) => extendedErrorToast(error, 'maintenance.backup.messages.restoreFailed'));
	}

	/**
	 * Extracts uploaded files from form configuration file input
	 * @returns {FileList} List of uploaded files
	 */
	private getFiles(): FileList {
		const input = ((this.$refs.backupArchive as CInputFile).$el.children[1] as HTMLInputElement);
		return (input.files as FileList);
	}

	/**
	 * Checks if form configuration file input is empty
	 */
	private fileInputEmpty(): void {
		const files = this.getFiles();
		this.inputEmpty = files.length === 0;
	}

}
</script>
