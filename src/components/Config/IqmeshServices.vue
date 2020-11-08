<template>
	<div>
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

/**
 * IQMESH services card for Daemon component configuration
 */
export default class IqmeshServices extends Vue {}
</script>
