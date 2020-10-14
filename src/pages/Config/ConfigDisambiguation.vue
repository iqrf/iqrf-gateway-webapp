<template>
	<div>
		<h1>{{ $t('config.title') }}</h1>
		<CCard body-wrapper>
			<CListGroup>
				<CListGroupItem
					v-if='$store.getters["user/getRole"] === "power"'
					to='/config/main/'
				>
					<header class='list-group-item-heading'>
						{{ $t('config.main.title') }}
					</header>
					<p class='list-group-item-text'>
						{{ $t('config.main.description') }}
					</p>
				</CListGroupItem>
				<CListGroupItem
					v-if='$store.getters["user/getRole"] === "power"'
					to='/config/component/'
				>
					<header class='list-group-item-heading'>
						{{ $t('config.components.title') }}
					</header>
					<p class='list-group-item-text'>
						{{ $t('config.components.description') }}
					</p>
				</CListGroupItem>
				<CListGroupItem
					v-if='$store.getters["user/getRole"] === "normal"'
					to='/config/component/'
				>
					<header class='list-group-item-heading'>
						{{ $t('config.selectedComponents.title') }}
					</header>
					<p class='list-group-item-text'>
						{{ $t('config.selectedComponents.description') }}
					</p>
				</CListGroupItem>
				<CListGroupItem to='/config/iqrf-spi/'>
					<header class='list-group-item-heading'>
						{{ $t('config.iqrfSpi.title') }}
					</header>
					<p class='list-group-item-text'>
						{{ $t('config.iqrfSpi.description') }}
					</p>
				</CListGroupItem>
				<CListGroupItem to='/config/iqrf-cdc/'>
					<header class='list-group-item-heading'>
						{{ $t('config.iqrfCdc.title') }}
					</header>
					<p class='list-group-item-text'>
						{{ $t('config.iqrfCdc.description') }}
					</p>
				</CListGroupItem>
				<CListGroupItem to='/config/iqrf-uart/'>
					<header class='list-group-item-heading'>
						{{ $t('config.iqrfUart.title') }}
					</header>
					<p class='list-group-item-text'>
						{{ $t('config.iqrfUart.description') }}
					</p>
				</CListGroupItem>
				<CListGroupItem to='/config/iqrf-dpa/'>
					<header class='list-group-item-heading'>
						{{ $t('config.iqrfDpa.title') }}
					</header>
					<p class='list-group-item-text'>
						{{ $t('config.iqrfDpa.description') }}
					</p>
				</CListGroupItem>
				<CListGroupItem to='/config/iqrf-repository/'>
					<header class='list-group-item-heading'>
						{{ $t('config.iqrfRepository.title') }}
					</header>
					<p class='list-group-item-text'>
						{{ $t('config.iqrfRepository.description') }}
					</p>
				</CListGroupItem>
				<CListGroupItem to='/config/iqrf-info/'>
					<header class='list-group-item-heading'>
						{{ $t('config.iqrfInfo.title') }}
					</header>
					<p class='list-group-item-text'>
						{{ $t('config.iqrfInfo.description') }}
					</p>
				</CListGroupItem>
				<CListGroupItem
					v-if='$store.getters["user/getRole"] === "power"'
					to='/config/iqmesh/'
				>
					<header class='list-group-item-heading'>
						{{ $t('config.iqmesh.title') }}
					</header>
					<p class='list-group-item-text'>
						{{ $t('config.iqmesh.description') }}
					</p>
				</CListGroupItem>
				<CListGroupItem to='/config/mqtt/'>
					<header class='list-group-item-heading'>
						{{ $t('config.mqtt.title') }}
					</header>
					<p class='list-group-item-text'>
						{{ $t('config.mqtt.description') }}
					</p>
				</CListGroupItem>
				<CListGroupItem to='/config/websocket/'>
					<header class='list-group-item-heading'>
						{{ $t('config.websocket.title') }}
					</header>
					<p class='list-group-item-text'>
						{{ $t('config.websocket.description') }}
					</p>
				</CListGroupItem>
				<CListGroupItem to='/config/mq/'>
					<header class='list-group-item-heading'>
						{{ $t('config.mq.title') }}
					</header>
					<p class='list-group-item-text'>
						{{ $t('config.mq.description') }}
					</p>
				</CListGroupItem>
				<CListGroupItem to='/config/udp/'>
					<header class='list-group-item-heading'>
						{{ $t('config.udp.title') }}
					</header>
					<p class='list-group-item-text'>
						{{ $t('config.udp.description') }}
					</p>
				</CListGroupItem>
				<CListGroupItem
					v-if='$store.getters["user/getRole"] === "power"'
					to='/config/json-raw-api/'
				>
					<header class='list-group-item-heading'>
						{{ $t('config.jsonRawApi.title') }}
					</header>
					<p class='list-group-item-text'>
						{{ $t('config.jsonRawApi.description') }}
					</p>
				</CListGroupItem>
				<CListGroupItem to='/config/json-mng-meta-data-api/'>
					<header class='list-group-item-heading'>
						{{ $t('config.jsonMngMetaDataApi.title') }}
					</header>
					<p class='list-group-item-text'>
						{{ $t('config.jsonMngMetaDataApi.description') }}
					</p>
				</CListGroupItem>
				<CListGroupItem
					v-if='$store.getters["user/getRole"] === "power"'
					to='/config/json-splitter/'
				>
					<header class='list-group-item-heading'>
						{{ $t('config.jsonSplitter.title') }}
					</header>
					<p class='list-group-item-text'>
						{{ $t('config.jsonSplitter.description') }}
					</p>
				</CListGroupItem>
				<CListGroupItem to='/config/scheduler/'>
					<header class='list-group-item-heading'>
						{{ $t('config.scheduler.title') }}
					</header>
					<p class='list-group-item-text'>
						{{ $t('config.scheduler.description') }}
					</p>
				</CListGroupItem>
				<CListGroupItem to='/config/tracer/'>
					<header class='list-group-item-heading'>
						{{ $t('config.tracer.title') }}
					</header>
					<p class='list-group-item-text'>
						{{ $t('config.tracer.description') }}
					</p>
				</CListGroupItem>
				<CListGroupItem to='/config/monitor/'>
					<header class='list-group-item-heading'>
						{{ $t('config.monitor.title') }}
					</header>
					<p class='list-group-item-text'>
						{{ $t('config.monitor.description') }}
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
			</CListGroup>
		</CCard>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CCard, CListGroup, CListGroupItem} from '@coreui/vue/src';

@Component({
	components: {
		CCard,
		CListGroup,
		CListGroupItem
	},
	metaInfo: {
		title: 'config.title',
	},
})

export default class ConfigDisambiguation extends Vue {
}
</script>
