<template>
	<div>
		<h1>{{ $t('iqrfnet.trUpload.title') }}</h1>
		<FileUpload />
		<DpaUpdater />
	</div>
</template>

<script lang='ts'>
import Vue from 'vue';
import DpaUpdater from '../../components/IqrfNet/DpaUpdater.vue';
import FileUpload from '../../components/IqrfNet/FileUpload.vue';

export default Vue.extend({
	name: 'TrUpload',
	components: {
		DpaUpdater,
		FileUpload
	},
	beforeRouteEnter(to, from, next) {
		next(vm => {
			if (!vm.$store.getters['features/isEnabled']('trUpload')) {
				vm.$toast.error(
					vm.$t('iqrfnet.trUpload.messages.disabled').toString()
				);
				vm.$router.push(from.path);
			}
		});
	},
	metaInfo: {
		title: 'iqrfnet.trUpload.title'
	}
});
</script>
