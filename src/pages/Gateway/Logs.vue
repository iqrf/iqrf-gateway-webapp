<template>
	<div>
		<header class='d-flex'>
			<h1 class='mr-auto'>
				{{ $t('gateway.log.title') }}
			</h1>
			<div>
				<CButton
					color='primary'
					@click='downloadArchive'
				>
					{{ $t('gateway.log.download') }}
				</CButton> <CButton
					color='primary'
					@click='getLogs'
				>
					{{ $t('forms.refresh') }}
				</CButton>
			</div>
		</header>
		<SystemdJournal v-if='$store.getters["features/isEnabled"]("systemdJournal")' />
		<CCard>			
			<CTabs variant='tabs' :active-tab='activeTab'>
				<CTab :title='$t("service.iqrf-gateway-controller.title")'>
					<LogTab :log='controllerLog' />
				</CTab>
				<CTab :title='$t("service.iqrf-gateway-daemon.title")'>
					<LogTab :log='daemonLog' />
				</CTab>
				<CTabs :title='$t("gateway.log.uploader")'>
					<LogTab :log='uploaderLog' />
				</CTabs>
				<CTabs :title='$t("gateway.log.journal")'>
					<LogTab :log='journal' />
				</CTabs>
			</CTabs>
		</CCard>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CButton, CCard, CTab, CTabs} from '@coreui/vue/src';
import LogTab from '../../components/Gateway/LogTab.vue';
import SystemdJournal from '../../components/Gateway/SystemdJournal.vue';

import {extendedErrorToast} from '../../helpers/errorToast';
import {fileDownloader} from '../../helpers/fileDownloader';

import GatewayService from '../../services/GatewayService';

import {AxiosError, AxiosResponse} from 'axios';
import {MetaInfo} from 'vue-meta';

@Component({
	components: {
		CButton,
		CCard,
		CTab,
		CTabs,
		LogTab,
		SystemdJournal,
	},
	metaInfo(): MetaInfo {
		return {
			title: 'gateway.log.title',
		};
	}
})

/**
 * IQRF Gateway log viewer component
 */
export default class LogViewer extends Vue {

	/**
	 * @var {number} activeTab CoreUI tabs active tab index
	 */
	private activeTab = 0

	/**
	 * @var {string|null} controllerLog Controller log file content
	 */
	private controllerLog: string|null = null

	/**
	 * @var {string|null} daemonLog Daemon log file content
	 */
	private daemonLog: string|null = null

	/**
	 * @var {string|null} uploaderLog Uploader log file content
	 */
	private uploaderLog: string|null = null

	/**
	 * @var {string|null} journal Systemd journal content
	 */
	private journal: string|null = null

	/**
	 * @var {boolean} loaded Indicates that logs have been loaded 
	 */
	private loaded = false;

	mounted(): void {
		this.getLogs();
	}

	private getLogs(): void {
		this.$store.commit('spinner/SHOW');
		GatewayService.getLatestLog()
			.then(
				(response: AxiosResponse) => {
					if (response.data.controller) {
						this.controllerLog = response.data.controller;
					}
					if (response.data.daemon) {
						this.daemonLog = response.data.daemon;
					}
					if (response.data.uploader) {
						this.uploaderLog = response.data.uploader;
					}
					this.journal = response.data.journal;
					this.loaded = true;
					this.$store.commit('spinner/HIDE');
				}
			)
			.catch((error: AxiosError) => extendedErrorToast(error, 'gateway.log.messages.fetchFailed'));
	}
	
	/**
	 * Creates a daemon log blob and prompts file download
	 */
	private downloadArchive(): void {
		this.$store.commit('spinner/SHOW');
		GatewayService.getLogArchive().then(
			(response: AxiosResponse) => {
				const file = fileDownloader(response, 'application/zip', 'iqrf-gateway-logs.zip');
				this.$store.commit('spinner/HIDE');
				file.click();
			}
		).catch(() => (this.$store.commit('spinner/HIDE')));
	}
}
</script>
