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
		<h1>{{ $t('config.title') }}</h1>
		<CCard>
			<CCardBody>
				<CListGroup>
					<CListGroupItem
						v-if='roleIdx <= roles.normal'
						to='/config/daemon/'
					>
						<header class='list-group-item-heading'>
							{{ $t('config.daemon.title') }}
						</header>
						<p class='list-group-item-text'>
							{{ $t('config.daemon.description') }}
						</p>
					</CListGroupItem>
					<CListGroupItem
						v-if='$store.getters["features/isEnabled"]("iqrfGatewayController") && roleIdx <= roles.normal'
						to='/config/controller/'
					>
						<header class='list-group-item-heading'>
							{{ $t('config.controller.title') }}
						</header>
						<p class='list-group-item-text'>
							{{ $t('config.controller.description') }}
						</p>
					</CListGroupItem>
					<CListGroupItem
						v-if='$store.getters["features/isEnabled"]("iqrfGatewayTranslator") && roleIdx <= roles.normal'
						to='/config/translator/'
					>
						<header class='list-group-item-heading'>
							{{ $t('config.translator.title') }}
						</header>
						<p class='list-group-item-text'>
							{{ $t('config.translator.description') }}
						</p>
					</CListGroupItem>
					<CListGroupItem
						v-if='roleIdx <= roles.normal'
						to='/config/repository/'
					>
						<header class='list-group-item-heading'>
							{{ $t('config.repository.title') }}
						</header>
						<p class='list-group-item-text'>
							{{ $t('config.repository.description') }}
						</p>
					</CListGroupItem>
					<CListGroupItem
						v-if='roleIdx <= roles.admin'
						to='/config/smtp/'
					>
						<header class='list-group-item-heading'>
							{{ $t('config.smtp.title') }}
						</header>
						<p class='list-group-item-text'>
							{{ $t('config.smtp.description') }}
						</p>
					</CListGroupItem>
				</CListGroup>
			</CCardBody>
		</CCard>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CCard, CCardBody, CCardHeader, CListGroup, CListGroupItem} from '@coreui/vue/src';

import {getRoleIndex} from '@/helpers/user';

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
