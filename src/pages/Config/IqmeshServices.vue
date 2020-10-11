<template>
	<div>
		<h1>{{ $t('config.iqmesh.title') }}</h1>
		<OtaUpload />
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import OtaUpload from '../../components/Config/OtaUpload.vue';

@Component({
	components: {
		OtaUpload,
	}
})

export default class IqmeshServices extends Vue {}
</script>
