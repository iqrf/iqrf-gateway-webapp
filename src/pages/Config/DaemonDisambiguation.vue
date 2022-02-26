<!--
Copyright 2017-2021 IQRF Tech s.r.o.
Copyright 2019-2021 MICRORISC s.r.o.

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software,
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied
See the License for the specific language governing permissions and
limitations under the License.
-->
<template>
	<div>
		<h1>{{ $t('config.daemon.title') }}</h1>
		<CCard>
			<CCardBody>
				<CListGroup>
					<CListGroupItem
						v-if='roleIdx <= roles.admin'
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
						v-if='roleIdx <= roles.admin'
						to='/config/daemon/component/'
					>
						<header class='list-group-item-heading'>
							{{ $t('config.daemon.components.title') }}
						</header>
						<p class='list-group-item-text'>
							{{ $t('config.daemon.components.description') }}
						</p>
					</CListGroupItem>
					<CListGroupItem
						v-if='roleIdx <= roles.normal'
						to='/config/daemon/interfaces/'
					>
						<header class='list-group-item-heading'>
							{{ $t('config.daemon.interfaces.title') }}
						</header>
						<p class='list-group-item-text'>
							{{ $t('config.daemon.interfaces.description') }}
						</p>
					</CListGroupItem>
					<CListGroupItem
						v-if='roleIdx <= roles.normal'
						to='/config/daemon/messagings/'
					>
						<header class='list-group-item-heading'>
							{{ $t('config.daemon.messagings.title') }}
						</header>
						<p class='list-group-item-text'>
							{{ $t('config.daemon.messagings.description') }}
						</p>
					</CListGroupItem>
					<CListGroupItem
						v-if='roleIdx <= roles.normal'
						to='/config/daemon/scheduler/'
					>
						<header class='list-group-item-heading'>
							{{ $t('config.daemon.scheduler.title') }}
						</header>
						<p class='list-group-item-text'>
							{{ $t('config.daemon.scheduler.description') }}
						</p>
					</CListGroupItem>
					<CListGroupItem
						v-if='roleIdx <= roles.normal'
						to='/config/daemon/misc/'
					>
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

import {getRoleIndex} from '../../helpers/user';

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
	/**
	 * @var {number} roleIdx Index of role in user role enum
	 */
	private roleIdx = 0;

	/**
	 * @constant {Record<string, number>} roles Dictionary of role indices
	 */
	private roles: Record<string, number> = {
		admin: 0,
		normal: 1,
		basicadmin: 2,
		basic: 3,
	};

	/**
	 * Retrieves user role and calculates the role index
	 */
	private created(): void {
		const roleVal = this.$store.getters['user/getRole'];
		this.roleIdx = getRoleIndex(roleVal);
	}
}
</script>
