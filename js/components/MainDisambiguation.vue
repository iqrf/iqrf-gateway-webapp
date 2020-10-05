<template>
	<CCard body-wrapper>
		<CListGroup>
			<CListGroupItem to='/gateway/'>
				<header class='list-group-item-heading'>
					{{ $t('gateway.title') }}
				</header>
				<p class='list-group-item-text'>
					{{ $t('gateway.description') }}
				</p>
			</CListGroupItem>
			<CListGroupItem to='/config/'>
				<header class='list-group-item-heading'>
					{{ $t('config.title') }}
				</header>
				<p class='list-group-item-text'>
					{{ $t('config.description') }}
				</p>
			</CListGroupItem>
			<CListGroupItem to='/iqrfnet/'>
				<header class='list-group-item-heading'>
					{{ $t('iqrfnet.title') }}
				</header>
				<p class='list-group-item-text'>
					{{ $t('iqrfnet.description') }}
				</p>
			</CListGroupItem>
			<CListGroupItem v-if='$store.getters["features/isEnabled"]("networkManager")' to='/network/'>
				<header class='list-group-item-heading'>
					{{ $t('network.title') }}
				</header>
				<p class='list-group-item-text'>
					{{ $t('network.description') }}
				</p>
			</CListGroupItem>
			<CListGroupItem to='/cloud/'>
				<header class='list-group-item-heading'>
					{{ $t('cloud.title') }}
				</header>
				<p class='list-group-item-text'>
					{{ $t('cloud.description') }}
				</p>
			</CListGroupItem>
			<CListGroupItem
				v-if='$store.getters["features/isEnabled"]("grafana")'
				:href='$store.getters["features/configuration"]("grafana").url'
				target='_blank'
			>
				<header class='list-group-item-heading'>
					{{ $t('core.grafana.title') }}
				</header>
				<p class='list-group-item-text'>
					{{ $t('core.grafana.description') }}
				</p>
			</CListGroupItem>
			<CListGroupItem
				v-if='$store.getters["features/isEnabled"]("nodeRed")'
				:href='$store.getters["features/configuration"]("nodeRed").url'
				target='_blank'
			>
				<header class='list-group-item-heading'>
					{{ $t('core.nodeRed.workflow.title') }}
				</header>
				<p class='list-group-item-text'>
					{{ $t('core.nodeRed.workflow.description') }}
				</p>
			</CListGroupItem>
			<CListGroupItem
				v-if='$store.getters["features/isEnabled"]("nodeRed")'
				:href='$store.getters["features/configuration"]("nodeRed").url + "ui/"'
				target='_blank'
			>
				<header class='list-group-item-heading'>
					{{ $t('core.nodeRed.dashboard.title') }}
				</header>
				<p class='list-group-item-text'>
					{{ $t('core.nodeRed.dashboard.description') }}
				</p>
			</CListGroupItem>
			<CListGroupItem
				v-if='$store.getters["features/isEnabled"]("supervisord")'
				:href='$store.getters["features/configuration"]("supervisord").url'
				target='_blank'
			>
				<header class='list-group-item-heading'>
					{{ $t('core.supervisor.title') }}
				</header>
				<p class='list-group-item-text'>
					{{ $t('core.supervisor.description') }}
				</p>
			</CListGroupItem>
			<CListGroupItem to='/user/'>
				<header class='list-group-item-heading'>
					{{ $t('core.user.title') }}
				</header>
				<p class='list-group-item-text'>
					{{ $t('core.user.description') }}
				</p>
			</CListGroupItem>
			<CListGroupItem
				v-if='$store.getters["features/isEnabled"]("docs")'
				:href='$store.getters["features/configuration"]("docs").url'
				target='_blank'
			>
				<header class='list-group-item-heading'>
					{{ $t('core.documentation.title') }}
				</header>
				<p class='list-group-item-text'>
					{{ $t('core.documentation.description') }}
				</p>
			</CListGroupItem>
		</CListGroup>
	</CCard>
</template>

<script lang='ts'>
import Vue from 'vue';
import {CCard, CListGroup, CListGroupItem} from '@coreui/vue/src';

export default Vue.extend({
	name: 'MainDisambiguation',
	components: {
		CCard,
		CListGroup,
		CListGroupItem
	},
	//metaInfo: {
	//	title: 'core.dashboard',
	//},
});
</script>
