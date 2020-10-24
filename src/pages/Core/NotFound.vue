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
import {Component, Vue} from 'vue-property-decorator';
import {CCard, CCardBody, CCardHeader} from '@coreui/vue/src';

@Component({
	components: {
		CCard,
		CCardBody,
		CCardHeader,
	},
	metaInfo: {
		title: 'core.error.404.title',
	},
})

/**
 * Error 404 page component
 */
export default class NotFound extends Vue {
}
</script>
