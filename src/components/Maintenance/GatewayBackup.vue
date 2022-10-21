<template>
	<div>
		<CCard>
			<CCardBody>
				<div style='display: flex; justify-content: space-between; align-items: center;'>
					<CCardTitle>{{ $t('maintenance.backup.backupTitle') }}</CCardTitle>
					<CButtonToolbar>
						<CButton
							class='mr-1'
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
					</CButtonToolbar>
				</div>
				<CForm>
					<CRow>
						<CCol>
							<h5>{{ $t('maintenance.backup.headings.software') }}</h5>
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
						</CCol>
						<CCol>
							<h5>{{ $t('maintenance.backup.headings.system') }}</h5>
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
						@click='backup'
					>
						{{ $t('maintenance.backup.form.backup') }}
					</CButton>
				</CForm>
			</CCardBody>
		</CCard>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CButton, CCol, CForm, CInputCheckbox, CRow} from '@coreui/vue/src';

import {extendedErrorToast} from '@/helpers/errorToast';
import {fileDownloader} from '@/helpers/fileDownloader';

import BackupService from '@/services/BackupService';

import {AxiosError, AxiosResponse} from 'axios';
import {IBackup} from '@/interfaces/Maintenance/Backup';

@Component({
	components: {
		CButton,
		CCol,
		CForm,
		CInputCheckbox,
		CRow,
	}
})

/**
 * Gateway backup component
 */
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
		if (!this.$store.getters['features/isEnabled']('systemdJournal')) {
			params.system.journal = false;
		}
		return params;
	}

}
</script>
