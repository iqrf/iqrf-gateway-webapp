<template>
	<div>
		<FileUpload />
		<DpaUpdater />
	</div>
</template>

<script>
import DpaUpdater from '../../components/IqrfNet/DpaUpdater';
import FileUpload from '../../components/IqrfNet/FileUpload';

export default {
	name: 'TrUpload',
	components: {
		DpaUpdater,
		FileUpload
	},
	metaInfo: {
		title: 'iqrfnet.trUpload.title'
	}
};
</script>
