<!--
Copyright 2017-2023 IQRF Tech s.r.o.
Copyright 2019-2023 MICRORISC s.r.o.

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
		<v-alert
			v-if='userEmail === null'
			type='warning'
			text
		>
			<v-row align='center'>
				<v-col class='grow'>
					{{ $t('account.email.messages.missing') }}
				</v-col>
				<v-col class='shrink'>
					<v-btn
						color='warning'
						small
						to='/profile/'
					>
						{{ $t('account.email.add') }}
					</v-btn>
				</v-col>
			</v-row>
		</v-alert>
		<v-alert
			v-if='userEmail !== null && isUserUnverified'
			color='warning'
			text
		>
			<v-row align='center'>
				<v-col class='grow'>
					{{ $t('account.email.messages.unverified', {email: userEmail}) }}
				</v-col>
				<v-col class='shrink'>
					<v-btn
						color='warning'
						small
						@click='resendVerification()'
					>
						{{ $t('core.user.resendVerification') }}
					</v-btn>
				</v-col>
			</v-row>
		</v-alert>
		<Disambiguation :links='links' />
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';

import {extendedErrorToast} from '@/helpers/errorToast';
import {mapGetters} from 'vuex';

import UserService from '@/services/UserService';

import {AxiosError} from 'axios';
import Disambiguation from '@/components/Disambiguation.vue';
import {Link, LinkTarget} from '@/helpers/DisambiguationHelper';
import {UserRoleIndex} from '@/services/AuthenticationService';

@Component({
	components: {
		Disambiguation,
	},
	metaInfo: {
		title: 'core.dashboard',
	},
	computed: {
		...mapGetters({
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
	 * @var {Link[]} links Links for disambiguation menu
	 */
	private links: Array<Link> = [
		{
			title: this.$t('gateway.title').toString(),
			description: this.$t('gateway.description').toString(),
			to: '/gateway/',
			role: UserRoleIndex.BASIC,
		},
		{
			title: this.$t('config.title').toString(),
			description: this.$t('config.description').toString(),
			to: '/config/',
			role: UserRoleIndex.NORMAL,
		},
		{
			title: this.$t('iqrfnet.title').toString(),
			description: this.$t('iqrfnet.description').toString(),
			to: '/iqrfnet/',
			role: UserRoleIndex.NORMAL,
		},
		{
			title: this.$t('network.title').toString(),
			description: this.$t('network.description').toString(),
			to: '/ip-network/',
			role: UserRoleIndex.ADMIN,
			feature: 'networkManager',
		},
		{
			title: this.$t('cloud.title').toString(),
			description: this.$t('cloud.description').toString(),
			to: '/cloud/',
			role: UserRoleIndex.NORMAL,
		},
		{
			title: this.$t('maintenance.title').toString(),
			description: this.$t('maintenance.description').toString(),
			to: '/maintenance/',
			role: UserRoleIndex.ADMIN,
		},
		{
			title: this.$t('core.grafana.title').toString(),
			description: this.$t('core.grafana.description').toString(),
			href: this.$store.getters['features/configuration']('grafana').url,
			role: UserRoleIndex.BASIC,
			feature: 'grafana',
		},
		{
			title: this.$t('core.nodeRed.workflow.title').toString(),
			description: this.$t('core.nodeRed.workflow.description').toString(),
			href: this.$store.getters['features/configuration']('nodeRed').url,
			role: UserRoleIndex.BASICADMIN,
			feature: 'nodeRed',
		},
		{
			title: this.$t('core.nodeRed.dashboard.title').toString(),
			description: this.$t('core.nodeRed.dashboard.description').toString(),
			href: this.$store.getters['features/configuration']('nodeRed').url + 'ui/',
			role: UserRoleIndex.BASIC,
			feature: 'nodeRed',
		},
		{
			title: this.$t('core.supervisor.title').toString(),
			description: this.$t('core.supervisor.description').toString(),
			href: this.$store.getters['features/configuration']('supervisord').url,
			role: UserRoleIndex.ADMIN,
			feature: 'supervisord',
		},
		{
			title: this.$t('core.user.title').toString(),
			description: this.$t('core.user.description').toString(),
			to: '/user/',
			role: [UserRoleIndex.ADMIN, UserRoleIndex.BASICADMIN],
		},
		{
			title: this.$t('core.security.title').toString(),
			description: this.$t('core.security.description').toString(),
			to: '/security/',
			role: UserRoleIndex.ADMIN,
		},
		{
			title: this.$t('core.documentation.title').toString(),
			description: this.$t('core.documentation.description').toString(),
			href: this.$store.getters['features/configuration']('docs').url,
			target: LinkTarget.blank,
		},
	];

	/**
	 * Resends verification e-mail
	 */
	private resendVerification(): void {
		this.$store.commit('spinner/SHOW');
		UserService.resendVerificationEmailLoggedIn()
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
