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
								v-if='$store.getters["features/isEnabled"]("iqrfGatewayController")'
								:checked.sync='migration.software.controller'
								:label='$t("gateway.backup.form.software.controller")'
							/>
							<CInputCheckbox
								:checked.sync='migration.software.daemon'
								:label='$t("gateway.backup.form.software.daemon")'
							/>
							<CInputCheckbox
								v-if='$store.getters["features/isEnabled"]("iqrfGatewayTranslator")'
								:checked.sync='migration.software.translator'
								:label='$t("gateway.backup.form.software.translator")'
							/>
							<CInputCheckbox
								:checked.sync='migration.software.uploader'
								:label='$t("gateway.backup.form.software.uploader")'
							/>
							<CInputCheckbox
								:checked.sync='migration.software.webapp'
								:label='$t("gateway.backup.form.software.webapp")'
							/>
							<CInputCheckbox
								v-if='$store.getters["features/isEnabled"]("mender")'
								:checked.sync='migration.software.mender'
								:label='$t("gateway.backup.form.software.mender")'
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
								:checked.sync='migration.system.metadata'
								:label='$t("gateway.backup.form.system.metadata")'
							/>
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
		</CCard>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CButton, CCard, CCardBody, CCol, CForm, CInputCheckbox, CRow} from '@coreui/vue/src';

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
			controller: false,
			daemon: false,
			translator: false,
			uploader: false,
			webapp: false,
			mender: false,
			pixla: false,
		},
		system: {
			metadata: false,
			hostname: false,
			network: false,
			ntp: false,
			journal: false,
		},
	}

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
	 * Filters unavailable features from request parameters
	 * @param {IGwBackup} params Request parameters
	 * @return {IGwBackup} Filtered request parameters
	 */
	private filterFeatures(params: IGwBackup): IGwBackup {
		if (!this.$store.getters['features/isEnabled']('iqrfGatewayController')) {
			delete params.software.controller;
		}
		if (!this.$store.getters['features/isEnabled']('iqrfGatewayTranslator')) {
			delete params.software.translator;
		}
		if (!this.$store.getters['features/isEnabled']('mender')) {
			delete params.software.mender;
		}
		if (!this.$store.getters['features/isEnabled']('pixla')) {
			delete params.software.pixla;
		}
		if (!this.$store.getters['features/isEnabled']('networkManager')) {
			delete params.system.network;
		}
		if (!this.$store.getters['features/isEnabled']('ntp')) {
			delete params.system.ntp;
		}
		if (!this.$store.getters['features/isEnabled']('systemdJournal')) {
			delete params.system.journal;
		}
		return params;
	}
}
</script>
