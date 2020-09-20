<template>
	<CCard body-wrapper>
		<CListGroup>
			<CListGroupItem to='/gateway/info/'>
				<header class='list-group-item-heading'>
					{{ $t('gateway.info.title') }}
				</header>
				<p class='list-group-item-text'>
					{{ $t('gateway.info.description') }}
				</p>
			</CListGroupItem>
			<CListGroupItem to='/gateway/log/'>
				<header class='list-group-item-heading'>
					{{ $t('gateway.log.title') }}
				</header>
				<p class='list-group-item-text'>
					{{ $t('gateway.log.description') }}
				</p>
			</CListGroupItem>
			<CListGroupItem to='/gateway/change-mode/'>
				<header class='list-group-item-heading'>
					{{ $t('gateway.mode.title') }}
				</header>
				<p class='list-group-item-text'>
					{{ $t('gateway.mode.description') }}
				</p>
			</CListGroupItem>
			<CListGroupItem to='/service/iqrf-gateway-daemon/'>
				<header class='list-group-item-heading'>
					{{ $t('service.iqrf-gateway-daemon.title') }}
				</header>
				<p class='list-group-item-text'>
					{{ $t('service.iqrf-gateway-daemon.description') }}
				</p>
			</CListGroupItem>
			<CListGroupItem
				v-if='$store.getters["features/isEnabled"]("ssh")'
				to='/service/ssh/'
			>
				<header class='list-group-item-heading'>
					{{ $t('service.ssh.title') }}
				</header>
				<p class='list-group-item-text'>
					{{ $t('service.ssh.description') }}
				</p>
			</CListGroupItem>
			<CListGroupItem
				v-if='$store.getters["features/isEnabled"]("unattendedUpgrades")'
				to='/service/unattended-upgrades/'
			>
				<header class='list-group-item-heading'>
					{{ $t('service.unattended-upgrades.title') }}
				</header>
				<p class='list-group-item-text'>
					{{ $t('service.unattended-upgrades.description') }}
				</p>
			</CListGroupItem>
			<CListGroupItem
				v-if='$store.getters["features/isEnabled"]("updater")'
				to='/gateway/updater/'
			>
				<header class='list-group-item-heading'>
					{{ $t('gateway.updater.title') }}
				</header>
				<p class='list-group-item-text'>
					{{ $t('gateway.updater.description') }}
				</p>
			</CListGroupItem>
			<CListGroupItem to='/gateway/power/'>
				<header class='list-group-item-heading'>
					{{ $t('gateway.power.title') }}
				</header>
				<p class='list-group-item-text'>
					{{ $t('gateway.power.description') }}
				</p>
			</CListGroupItem>
		</CListGroup>
	</CCard>
</template>

<script>
import {CCard, CListGroup, CListGroupItem} from '@coreui/vue';

export default {
	name: 'GatewayDisambiguation',
	components: {
		CCard,
		CListGroup,
		CListGroupItem
	},
	metaInfo: {
		title: 'gateway.title',
	},
};
</script>
