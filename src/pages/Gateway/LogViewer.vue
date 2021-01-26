<template>
	<div>
		<h1>{{ $t('gateway.log.title') }}</h1>
		<CCard>
			<CTabs variant='tabs' :active-tab='activeTab'>
				<CTab 
					v-if='controllerLog !== ""'
					:title='$t("service.iqrf-gateway-controller.title")'
				>
					<CCardBody>
						<pre class='log'>{{ controllerLog }}</pre>
					</CCardBody>
				</CTab>
				<CTab
					v-if='daemonLog !== ""'
					:title='$t("service.iqrf-gateway-daemon.title")'
				>
					<CCardBody>
						<pre class='log'>{{ daemonLog }}</pre>
					</CCardBody>
				</CTab>
			</CTabs>
			<CCardFooter>
				<CButton color='primary' @click='downloadArchive()'>
					{{ $t('gateway.log.download') }}
				</CButton>
			</CCardFooter>
		</CCard>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {AxiosError, AxiosResponse} from 'axios';
import {CButton, CCard, CTab, CTabs} from '@coreui/vue/src';
import GatewayService from '../../services/GatewayService';
import {fileDownloader} from '../../helpers/fileDownloader';
import {MetaInfo} from 'vue-meta';

@Component({
	components: {
		CButton,
		CCard,
		CTab,
		CTabs
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
	 * @var {string} controllerLog Controller log file content
	 */
	private controllerLog = ''

	/**
	 * @var {string} daemonLog Daemon log file content
	 */
	private daemonLog = ''

	/**
	 * Vue lifecycle hook created
	 */
	created(): void {
		this.$store.commit('spinner/SHOW');
		GatewayService.getLatestLog()
			.then(
				(response: AxiosResponse) => {
					this.daemonLog = response.data.daemon;
					if (response.data.controller) {
						this.controllerLog = response.data.controller;
						this.activeTab = 1;
					}
					this.$store.commit('spinner/HIDE');
				}
			)
			.catch((error: AxiosError) => {
				this.$store.commit('spinner/HIDE');
				if (error.response) {
					if (error.response.data.code === 500) {
						this.$toast.error(
							this.$t('gateway.log.messages.notFound').toString()
						);
					}
				}
			});
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
