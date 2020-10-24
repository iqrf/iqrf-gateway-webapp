<template>
	<div>
		<h1>{{ $t('gateway.log.title') }}</h1>
		<CCard v-if='log !== ""' body-wrapper>
			<pre class='log'>{{ log }}</pre>
			<CButton color='primary' @click='downloadArchive()'>
				{{ $t('gateway.log.download') }}
			</CButton>
		</CCard>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {AxiosError, AxiosResponse} from 'axios';
import {CButton, CCard} from '@coreui/vue/src';
import GatewayService from '../../services/GatewayService';
import {fileDownloader} from '../../helpers/fileDownloader';
import { MetaInfo } from 'vue-meta';

@Component({
	components: {
		CButton,
		CCard,
	},
	metaInfo(): MetaInfo {
		return {
			title: 'gateway.log.title',
		};
	}
})

/**
 * IQRF Gateway Daemon log viewer component
 */
export default class DaemonLogViewer extends Vue {
	/**
	 * @var {string} log Daemon log file conent
	 */
	private log = ''

	/**
	 * Vue lifecycle hook created
	 */
	created(): void {
		this.$store.commit('spinner/SHOW');
		GatewayService.getLatestLog()
			.then(
				(response: AxiosResponse) => {
					this.log = response.data;
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
