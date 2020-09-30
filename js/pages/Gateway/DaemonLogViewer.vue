<template>
	<CCard v-if='log' body-wrapper>
		<pre class='log'>{{ log }}</pre>
		<CButton color='info' @click='downloadArchive()'>
			{{ $t('gateway.log.download') }}
		</CButton>
	</CCard>
</template>

<script>
import {CButton, CCard} from '@coreui/vue/src';
import GatewayService from '../../services/GatewayService';
import {fileDownloader} from '../../helpers/fileDownloader';

export default {
	name: 'DaemonLogViewer',
	components: {
		CButton,
		CCard,
	},
	data() {
		return {
			log: null
		};
	},
	created() {
		this.$store.commit('spinner/SHOW');
		GatewayService.getLatestLog()
			.then(
				(response) => {
					this.log = response.data;
					this.$store.commit('spinner/HIDE');
				}
			)
			.catch((error) => {
				this.$store.commit('spinner/HIDE');
				if (error.response) {
					if (error.response.data.code === 500) {
						this.$toast.error(this.$t('gateway.log.messages.notFound').toString());
					}
				}
			});
	},
	methods: {
		downloadArchive() {
			this.$store.commit('spinner/SHOW');
			GatewayService.getLogArchive().then(
				(response) => {
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
};
</script>
