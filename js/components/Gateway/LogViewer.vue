<template>
	<div v-if='log' class='panel panel-default'>
		<div class='panel-body'>
			<pre class='log'>{{ log }}</pre>
			<button class='btn btn-primary' @click='downloadArchive()'>
				{{ $t('gateway.log.download') }}
			</button>
		</div>
	</div>
</template>

<script>
import GatewayService from '../../services/GatewayService';
import spinner from '../../spinner';

export default {
	name: 'LogViewer',
	data() {
		return {
			log: null
		};
	},
	created() {
		spinner.showSpinner();
		GatewayService.getLatestLog()
			.then(
				(response) => {
					this.log = response.data;
					spinner.hideSpinner();
				}
			)
			.catch(() => spinner.hideSpinner());
	},
	methods: {
		downloadArchive() {
			spinner.showSpinner();
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
					spinner.hideSpinner();
					file.click();
				}
			).catch(() => (spinner.hideSpinner()));
		}
	},
};
</script>

<style scoped>

</style>
