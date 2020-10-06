<template>
	<div>
		<h1>{{ $t('config.iqmesh.title') }}</h1>
		<OtaUpload />
	</div>
</template>

<script lang='ts'>
import Vue from 'vue';
import OtaUpload from '../../components/Config/OtaUpload.vue';

export default Vue.extend({
	name: 'IqmeshServices',
	components: {
		OtaUpload,
	}
});
</script>
