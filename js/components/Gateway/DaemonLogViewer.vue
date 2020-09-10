<template>
	<CCard v-if='log' body-wrapper>
		<pre class='log'>{{ log }}</pre>
		<CButton color='info' @click='downloadArchive()'>
			{{ $t('gateway.log.download') }}
		</CButton>
	</CCard>
</template>

<script>
import {CButton, CCard} from '@coreui/vue';
import GatewayService from '../../services/GatewayService';

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
			.catch(() => this.$store.commit('spinner/HIDE'));
	},
	methods: {
		downloadArchive() {
			this.$store.commit('spinner/SHOW');
			GatewayService.getLogArchive().then(
				(response) => {
					const contentDisposition = response.headers['content-disposition'];
					let fileName = 'iqrf-gateway-logs.zip';
					if (contentDisposition) {
						const fileNameMatch = contentDisposition.match(/filename="(.+)"/);
						if (fileNameMatch.length === 2) {
							fileName = fileNameMatch[1];
						}
					}
					const blob = new Blob([response.data], {type: 'application/zip'});
					const fileUrl = window.URL.createObjectURL(blob);
					const file = document.createElement('a');
					file.href = fileUrl;
					file.setAttribute('download', fileName);
					document.body.appendChild(file);
					this.$store.commit('spinner/HIDE');
					file.click();
				}
			).catch(() => (this.$store.commit('spinner/HIDE')));
		}
	},
};
</script>
