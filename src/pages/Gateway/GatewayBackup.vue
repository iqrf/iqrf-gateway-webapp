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
		<h1>{{ $t('gateway.backup.title') }}</h1>
		<CCard>
			<CCardBody>
				<CForm @submit.prevent='backup'>
					<CButton
						color='primary'
						size='sm'
						@click='setAll(true)'
					>
						{{ $t('gateway.backup.form.selectAll') }}
					</CButton> <CButton
						color='secondary'
						size='sm'
						@click='setAll(false)'
					>
						{{ $t('gateway.backup.form.deselectAll') }}
					</CButton>
					<CRow>
						<CCol>
							<h3>{{ $t('gateway.backup.headings.software') }}</h3>
							<CInputCheckbox
								:checked.sync='migration.software.iqrf'
								:label='$t("gateway.backup.form.software.iqrf")'
							/>
							<CInputCheckbox
								v-if='$store.getters["features/isEnabled"]("mender")'
								:checked.sync='migration.software.mender'
								:label='$t("gateway.backup.form.software.mender")'
							/>
							<CInputCheckbox
								v-if='$store.getters["features/isEnabled"]("monit")'
								:checked.sync='migration.software.monit'
								:label='$t("gateway.backup.form.software.monit")'
							/>
							<CInputCheckbox
								v-if='$store.getters["features/isEnabled"]("pixla")'
								:checked.sync='migration.software.pixla'
								:label='$t("gateway.backup.form.software.pixla")'
							/>
						</CCol>
						<CCol>
							<h3>{{ $t('gateway.backup.headings.system') }}</h3>
							<CInputCheckbox
								:checked.sync='migration.system.hostname'
								:label='$t("gateway.backup.form.system.hostname")'
							/>
							<CInputCheckbox
								v-if='$store.getters["features/isEnabled"]("networkManager")'
								:checked.sync='migration.system.network'
								:label='$t("gateway.backup.form.system.network")'
							/>
							<CInputCheckbox
								v-if='$store.getters["features/isEnabled"]("ntp")'
								:checked.sync='migration.system.ntp'
								:label='$t("gateway.backup.form.system.ntp")'
							/>
							<CInputCheckbox
								v-if='$store.getters["features/isEnabled"]("systemdJournal")'
								:checked.sync='migration.system.journal'
								:label='$t("gateway.backup.form.system.journal")'
							/>
						</CCol>
					</CRow>
					<CButton
						color='primary'
						type='submit'
					>
						{{ $t('gateway.backup.title') }}
					</CButton>
				</CForm>
			</CCardBody>
			<CCardBody>
				<CForm>
					<CInputFile
						ref='backupArchive'
						accept='.zip'
						:label='$t("gateway.backup.form.archive")'
						@click='fileInputEmpty'
						@input='fileInputEmpty'
					/>
					<CButton
						color='primary'
						:disabled='inputEmpty'
						@click.prevent='restore'
					>
						{{ $t('gateway.backup.form.restore') }}
					</CButton>
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
		title: 'gateway.backup.title',
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
			ntp: false,
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
	 * Migrates gateway data
	 */
	private backup(): void {
		this.$store.commit('spinner/SHOW');
		let params = this.filterFeatures(JSON.parse(JSON.stringify(this.migration)));
		GatewayService.backup(params)
			.then((response: AxiosResponse) => {
				const fileName = 'iqrf-gateway-backup_' + new Date().toISOString();
				const file = fileDownloader(response, 'application/zip', fileName);
				this.$store.commit('spinner/HIDE');
				file.click();
			})
			.catch((error: AxiosError) => extendedErrorToast(error, 'gateway.backup.messages.backupFailed'));
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
		if (!this.$store.getters['features/isEnabled']('ntp')) {
			params.system.ntp = false;
		}
		if (!this.$store.getters['features/isEnabled']('systemdJournal')) {
			params.system.journal = false;
		}
		return params;
	}

	private restore(): void {
		const files = this.getFiles();
		if (files === null || files.length === 0) {
			return;
		}
		this.$store.commit('spinner/SHOW');
		GatewayService.restore(files[0])
			.then(() => {
				this.$store.commit('spinner/HIDE');
				this.$toast.success(
					this.$t('gateway.backup.messages.restoreSuccess').toString()
				);
			})
			.catch((error: AxiosError) => extendedErrorToast(error, 'gateway.backup.messages.restoreFailed'));
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
