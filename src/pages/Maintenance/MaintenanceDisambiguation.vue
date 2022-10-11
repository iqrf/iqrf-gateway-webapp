<!--
Copyright 2017-2022 IQRF Tech s.r.o.
Copyright 2019-2022 MICRORISC s.r.o.

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
		<h1>{{ $t('maintenance.title') }}</h1>
		<CCard body-wrapper>
			<CListGroup>
				<CListGroupItem
					v-if='roleIdx <= roles.admin'
					to='/maintenance/backup-restore/'
				>
					<header class='list-group-item-heading'>
						{{ $t('maintenance.backup.title') }}
					</header>
					<p class='list-group-item-text'>
						{{ $t('maintenance.backup.description') }}
					</p>
				</CListGroupItem>
				<CListGroupItem
					v-if='$store.getters["features/isEnabled"]("mender") && roleIdx <= roles.admin'
					to='/maintenance/mender/'
				>
					<header class='list-group-item-heading'>
						{{ $t('maintenance.mender.title') }}
					</header>
					<p class='list-group-item-text'>
						{{ $t('maintenance.mender.description') }}
					</p>
				</CListGroupItem>
				<CListGroupItem
					v-if='$store.getters["features/isEnabled"]("monit") && roleIdx <= roles.admin'
					to='/maintenance/monit/'
				>
					<header class='list-group-item-heading'>
						{{ $t('maintenance.monit.title') }}
					</header>
					<p class='list-group-item-text'>
						{{ $t('maintenance.monit.description') }}
					</p>
				</CListGroupItem>
			</CListGroup>
		</CCard>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CCard, CListGroup, CListGroupItem} from '@coreui/vue/src';

import {getRoleIndex} from '@/helpers/user';

@Component({
	components: {
		CCard,
		CListGroup,
		CListGroupItem,
	},
	metaInfo: {
		title: 'maintenance.title',
	},
})

/**
 * Maintenance disambiguation component
 */
export default class MaintenanceDisambiguation extends Vue {
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
