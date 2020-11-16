<template>
	<div>
		<h1>{{ $t('config.daemon.title') }}</h1>
		<CCard>
			<CCardBody>
				<CListGroup>
					<CListGroupItem
						v-if='$store.getters["user/getRole"] === "power"'
						to='/config/daemon/main/'
					>
						<header class='list-group-item-heading'>
							{{ $t('config.daemon.main.title') }}
						</header>
						<p class='list-group-item-text'>
							{{ $t('config.daemon.main.description') }}
						</p>
					</CListGroupItem>
					<CListGroupItem
						v-if='$store.getters["user/getRole"] === "power"'
						to='/config/daemon/component/'
					>
						<header class='list-group-item-heading'>
							{{ $t('config.daemon.components.title') }}
						</header>
						<p class='list-group-item-text'>
							{{ $t('config.daemon.components.description') }}
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
							{{ $t('config.daemon.scheduler.title') }}
						</header>
						<p class='list-group-item-text'>
							{{ $t('config.daemon.scheduler.description') }}
						</p>
					</CListGroupItem>
					<CListGroupItem to='/config/daemon/misc/'>
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
		title: 'config.daemon.title'
	}
})

/**
 * Daemon configuration disambiguation menu
 */
export default class DaemonDisambiguation extends Vue {
}
</script>
