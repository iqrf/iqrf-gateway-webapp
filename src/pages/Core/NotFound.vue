<template>
	<div>
		<h1>{{ $t('core.error.404.title') }}</h1>
		<CCard>
			<CCardHeader>{{ $t('core.error.404.header') }}</CCardHeader>
			<CCardBody>{{ $t('core.error.404.message') }}</CCardBody>
		</CCard>
	</div>
</template>

<script lang='ts'>
import Vue from 'vue';
import {CCard, CCardBody, CCardHeader} from '@coreui/vue/src';

export default Vue.extend({
	name: 'NotFound',
	components: {
		CCard,
		CCardBody,
		CCardHeader,
	},
	metaInfo: {
		title: 'core.error.404.title',
	},
});
</script>
