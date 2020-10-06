<template>
	<div>
		<h1>{{ $t('gateway.log.title') }}</h1>
		<CCard v-if='log' body-wrapper>
			<pre class='log'>{{ log }}</pre>
			<CButton color='primary' @click='downloadArchive()'>
				{{ $t('gateway.log.download') }}
			</CButton>
		</CCard>
	</div>
</template>

<script lang='ts'>
import Vue from 'vue';
import {AxiosError, AxiosResponse} from 'axios';
import {CButton, CCard} from '@coreui/vue/src';
import GatewayService from '../../services/GatewayService';
import {fileDownloader} from '../../helpers/fileDownloader';

export default Vue.extend({
	name: 'DaemonLogViewer',
	components: {
		CButton,
		CCard,
	},
	data(): any {
		return {
			log: null
		};
	},
	created() {
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
	},
	methods: {
		downloadArchive() {
			this.$store.commit('spinner/SHOW');
			GatewayService.getLogArchive().then(
				(response: AxiosResponse) => {
					const file = fileDownloader(response, 'application/zip', 'iqrf-gateway-logs.zip');
					this.$store.commit('spinner/HIDE');
					file.click();
				}
			).catch(() => (this.$store.commit('spinner/HIDE')));
		}
	},
	metaInfo: {
		title: 'gateway.log.title',
	},
});
</script>
