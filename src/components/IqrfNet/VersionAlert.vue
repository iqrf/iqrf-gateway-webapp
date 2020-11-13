<template>
	<CCard class='border-0'>
		<CCardBody>
			<CAlert v-if='daemonVersion == null' color='danger'>
				{{ $t('iqrfnet.networkManager.messages.versionMissing') }}
			</CAlert>
			<CAlert v-else color='danger'>
				{{ $t('iqrfnet.networkManager.messages.versionInvalid') }}<br>
				{{ $t('iqrfnet.networkManager.messages.versionCurrent') }} {{ daemonVersion }}
			</CAlert>
		</CCardBody>
	</CCard>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CAlert, CCard, CCardBody} from '@coreui/vue/src';
import {mapGetters} from 'vuex';

@Component({
	components: {
		CAlert,
		CCard,
		CCardBody,
	},
	computed: {
		...mapGetters({
			daemonVersion: 'daemonVersion',
		}),
	},
})

/**
 * Version alert card for network manager page component
 */
export default class VersionAlert extends Vue {}
</script>
