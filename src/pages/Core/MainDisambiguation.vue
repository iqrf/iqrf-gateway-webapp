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
		<h1>{{ $t('core.dashboard') }}</h1>
		<CAlert
			v-if='userEmail === null'
			color='warning'
			class='d-flex justify-content-between align-items-center'
		>
			{{ $t('account.email.messages.missing') }}
			<CButton
				color='warning'
				size='sm'
				:to='"/user/edit/" + userId'
			>
				{{ $t('account.email.add') }}
			</CButton>
		</CAlert>
		<CAlert
			v-if='userEmail !== null && isUserUnverified'
			color='warning'
			class='d-flex justify-content-between align-items-center'
		>
			{{ $t('account.email.messages.unverified', {email: userEmail}) }}
			<CButton
				color='warning'
				size='sm'
				@click='resendVerification(userId)'
			>
				{{ $t('core.user.resendVerification') }}
			</CButton>
		</CAlert>
		<CCard>
			<CCardBody>
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
					<CListGroupItem to='/maintenance/'>
						<header class='list-group-item-heading'>
							{{ $t('maintenance.title') }}
						</header>
						<p class='list-group-item-text'>
							{{ $t('maintenance.description') }}
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
					<CListGroupItem to='/security/'>
						<header class='list-group-item-heading'>
							{{ $t('core.security.title') }}
						</header>
						<p class='list-group-item-text'>
							{{ $t('core.security.description') }}
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
			</CCardBody>
		</CCard>
	</div>
</template>

<script lang='ts'>
import {CAlert, CButton, CCard, CListGroup, CListGroupItem} from '@coreui/vue/src';
import {AxiosError} from 'axios';
import {Component, Vue} from 'vue-property-decorator';
import {mapGetters} from 'vuex';

import {extendedErrorToast} from '../../helpers/errorToast';
import UserService from '../../services/UserService';

@Component({
	components: {
		CAlert,
		CButton,
		CCard,
		CListGroup,
		CListGroupItem
	},
	metaInfo: {
		title: 'core.dashboard',
	},
	computed: {
		...mapGetters({
			userId: 'user/getId',
			userEmail: 'user/getEmail',
			isUserUnverified: 'user/isUnverified',
		}),
	},
})

/**
 * Main disambiguation menu component
 */
export default class MainDisambiguation extends Vue {

	/**
	 * Resends verification e-mail
	 * @param userId User ID
	 * @private
	 */
	private resendVerification(userId: number): void {
		this.$store.commit('spinner/SHOW');
		UserService.resendVerificationEmail(userId)
			.then(() => {
				this.$store.commit('spinner/HIDE');
				this.$toast.success(
					this.$t('core.user.messages.resendSuccess').toString()
				);
			})
			.catch((error: AxiosError) => extendedErrorToast(error, 'core.user.messages.resendFailed'));
	}

}
</script>
