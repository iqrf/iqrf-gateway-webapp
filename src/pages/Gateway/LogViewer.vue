<template>
	<div>
		<h1>{{ $t('gateway.log.title') }}</h1>
		<CCard>
			<CCardHeader>
				<CButton 
					v-if='controllerLog'
					color='primary'
					@click='switchCollapse()'
				>
					{{ $t('service.iqrf-gateway-controller.title') }}
				</CButton> <CButton 
					v-if='daemonLog'
					color='primary'
					@click='switchCollapse()'
				>
					{{ $t('service.iqrf-gateway-daemon.title') }}
				</CButton>
			</CCardHeader>
			<CCardBody v-if='daemonLog !== ""'>
				<CCollapse :show='controllerShow' transition='linear' :duration='{show: 0, hide: 0}'>
					<pre class='log'>{{ controllerLog }}</pre>
				</CCollapse>
				<CCollapse :show='daemonShow' transition='linear' :duration='{show: 0, hide: 0}'>
					<pre class='log'>{{ daemonLog }}</pre>
				</CCollapse>
				<CButton color='primary' @click='downloadArchive()'>
					{{ $t('gateway.log.download') }}
				</CButton>
			</CCardBody>
		</CCard>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {AxiosError, AxiosResponse} from 'axios';
import {CButton, CCard, CCardBody, CCardHeader, CCollapse} from '@coreui/vue/src';
import GatewayService from '../../services/GatewayService';
import {fileDownloader} from '../../helpers/fileDownloader';
import {MetaInfo} from 'vue-meta';

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CCollapse,
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
	 * @var {string} controllerLog Controller log file content
	 */
	private controllerLog = ''

	private controllerShow = false;

	/**
	 * @var {string} daemonLog Daemon log file content
	 */
	private daemonLog = ''

	private daemonShow = false;

	/**
	 * Vue lifecycle hook created
	 */
	created(): void {
		this.$store.commit('spinner/SHOW');
		GatewayService.getLatestLog()
			.then(
				(response: AxiosResponse) => {
					if (response.data.controller) {
						this.controllerLog = response.data.controller;
						this.controllerShow = true;
					}
					this.daemonLog = response.data.daemon;
					if (this.controllerShow === false) {
						this.daemonShow = true;
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

	private switchCollapse(): void {
		if (this.controllerLog !== '') {
			this.controllerShow = !this.controllerShow;
		}
		this.daemonShow = !this.daemonShow;
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
