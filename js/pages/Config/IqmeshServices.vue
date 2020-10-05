<template>
	<div>
		<OtaUpload />
	</div>
</template>

<script lang='ts'>
import Vue from 'vue';
import OtaUpload from '../../components/Config/OtaUpload';

export default Vue.extend({
	name: 'IqmeshServices',
	components: {
		OtaUpload,
	}
});
</script>
