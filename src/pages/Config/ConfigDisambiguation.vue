<template>
	<CCard>
		<CCardHeader>
			<h3>{{ $t('config.title') }}</h3>
		</CCardHeader>
		<CCardBody>
			<CListGroup>
				<CListGroupItem to='/config/daemon/'>
					<header class='list-group-item-heading'>
						{{ $t('config.daemon.title') }}
					</header>
					<p class='list-group-item-text'>
						{{ $t('config.daemon.description') }}
					</p>
				</CListGroupItem>
				<CListGroupItem
					v-if='$store.getters["features/isEnabled"]("networkManager")'
					to='/network/'
				>
					<header class='list-group-item-heading'>
						{{ $t('translatorConfig.title') }}
					</header>
					<p class='list-group-item-text'>
						{{ $t('translatorConfig.description') }}
					</p>
				</CListGroupItem>
				<CListGroupItem
					v-if='$store.getters["features/isEnabled"]("iqrfGatewayController")'
					to='/config/controller/'
				>
					<header class='list-group-item-heading'>
						{{ $t('controllerConfig.title') }}
					</header>
					<p class='list-group-item-text'>
						{{ $t('controllerConfig.description') }}
					</p>
				</CListGroupItem>
				<CListGroupItem
					v-if='$store.getters["features/isEnabled"]("mender")'
					to='/config/mender/'
				>
					<header class='list-group-item-heading'>
						{{ $t('config.mender.title') }}
					</header>
					<p class='list-group-item-text'>
						{{ $t('config.mender.description') }}
					</p>
				</CListGroupItem>
				<CListGroupItem to='/config/migration/'>
					<header class='list-group-item-heading'>
						{{ $t('config.migration.title') }}
					</header>
					<p class='list-group-item-text'>
						{{ $t('config.migration.description') }}
					</p>
				</CListGroupItem>
			</CListGroup>
		</CCardBody>
	</CCard>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CCard, CCardBody, CCardHeader, CListGroup, CListGroupItem} from '@coreui/vue/src';

@Component({
	components: {
		CCard,
		CCardBody,
		CCardHeader,
		CListGroup,
		CListGroupItem
	},
	metaInfo: {
		title: 'config.title',
	},
})

/**
 * Config disambiguation menu component
 */
export default class ConfigDisambiguation extends Vue {
}
</script>
