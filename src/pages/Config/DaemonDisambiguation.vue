<template>
	<CCard>
		<CCardHeader>
			<h3>{{ $t('config.daemon.title') }}</h3>
		</CCardHeader>
		<CCardBody>
			<CListGroup>
				<CListGroupItem
					v-if='$store.getters["user/getRole"] === "power"'
					to='/config/daemon/main/'
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
					to='/config/daemon/component/'
				>
					<header class='list-group-item-heading'>
						{{ $t('config.components.title') }}
					</header>
					<p class='list-group-item-text'>
						{{ $t('config.components.description') }}
					</p>
				</CListGroupItem>
				<CListGroupItem to='/config/daemon/interfaces/'>
					<header class='list-group-item-heading'>
						{{ $t('config.daemon.interfaces.title') }}
					</header>
					<p class='list-group-item-text'>
						{{ $t('config.daemon.interfaces.description') }}
					</p>
				</CListGroupItem>
				<CListGroupItem to='/config/daemon/messagings/'>
					<header class='list-group-item-heading'>
						{{ $t('config.daemon.messagings.title') }}
					</header>
					<p class='list-group-item-text'>
						{{ $t('config.daemon.messagings.description') }}
					</p>
				</CListGroupItem>
				<CListGroupItem to='/config/daemon/scheduler/'>
					<header class='list-group-item-heading'>
						{{ $t('config.scheduler.title') }}
					</header>
					<p class='list-group-item-text'>
						{{ $t('config.scheduler.description') }}
					</p>
				</CListGroupItem>
				<CListGroupItem to='/config/daemon/other/'>
					<header class='list-group-item-heading'>
						{{ $t('config.daemon.misc.title') }}
					</header>
					<p class='list-group-item-text'>
						{{ $t('config.daemon.misc.description') }}
					</p>
				</CListGroupItem>
			</CListGroup>
		</CCardBody>
	</CCard>
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
		title: 'config.daemon.title'
	}
})

/**
 * Daemon configuration disambiguation menu
 */
export default class DaemonDisambiguation extends Vue {
}
</script>
