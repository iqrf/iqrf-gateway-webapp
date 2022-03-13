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
		<h1>{{ $t('iqrfnet.title') }}</h1>
		<CCard body-wrapper>
			<CListGroup>
				<CListGroupItem
					v-if='roleIdx <= roles.normal'
					to='/iqrfnet/send-raw/'
				>
					<header class='list-group-item-heading'>
						{{ $t('iqrfnet.sendPacket.title') }}
					</header>
					<p class='list-group-item-text'>
						{{ $t('iqrfnet.sendPacket.description') }}
					</p>
				</CListGroupItem>
				<CListGroupItem
					v-if='roleIdx <= roles.normal'
					to='/iqrfnet/send-json/'
				>
					<header class='list-group-item-heading'>
						{{ $t('iqrfnet.sendJson.title') }}
					</header>
					<p class='list-group-item-text'>
						{{ $t('iqrfnet.sendJson.description') }}
					</p>
				</CListGroupItem>
				<CListGroupItem
					v-if='$store.getters["features/isEnabled"]("trUpload") && roleIdx <= roles.admin'
					to='/iqrfnet/tr-upload/'
				>
					<header class='list-group-item-heading'>
						{{ $t('iqrfnet.trUpload.title') }}
					</header>
					<p class='list-group-item-text'>
						{{ $t('iqrfnet.trUpload.description') }}
					</p>
				</CListGroupItem>
				<CListGroupItem
					v-if='roleIdx <= roles.normal'
					to='/iqrfnet/tr-config/'
				>
					<header class='list-group-item-heading'>
						{{ $t('iqrfnet.trConfiguration.title') }}
					</header>
					<p class='list-group-item-text'>
						{{ $t('iqrfnet.trConfiguration.description') }}
					</p>
				</CListGroupItem>
				<CListGroupItem
					v-if='roleIdx <= roles.normal'
					to='/iqrfnet/network/'
				>
					<header class='list-group-item-heading'>
						{{ $t('iqrfnet.networkManager.title') }}
					</header>
					<p class='list-group-item-text'>
						{{ $t('iqrfnet.networkManager.description') }}
					</p>
				</CListGroupItem>
				<CListGroupItem
					v-if='roleIdx <= roles.normal'
					to='/iqrfnet/standard/'
				>
					<header class='list-group-item-heading'>
						{{ $t('iqrfnet.standard.title') }}
					</header>
					<p class='list-group-item-text'>
						{{ $t('iqrfnet.standard.description') }}
					</p>
				</CListGroupItem>
			</CListGroup>
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
		title: 'iqrfnet.title',
	},
})

/**
 * IqrfNet disambiguation menu component
 */
export default class IqrfNetDisambiguation extends Vue {
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
