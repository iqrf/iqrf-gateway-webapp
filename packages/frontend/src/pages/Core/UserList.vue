<!--
Copyright 2017-2025 IQRF Tech s.r.o.
Copyright 2019-2025 MICRORISC s.r.o.

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
		<h1>{{ $t('core.user.title') }}</h1>
		<v-card>
			<v-card-text>
				<v-data-table
					:loading='loading'
					:headers='headers'
					:items='users'
					:no-data-text='$t("table.messages.noRecords")'
				>
					<template #top>
						<v-toolbar dense flat>
							<v-spacer />
							<v-btn
								color='success'
								to='/user/add/'
								small
							>
								<v-icon small>
									mdi-plus
								</v-icon>
								{{ $t('table.actions.add') }}
							</v-btn>
						</v-toolbar>
					</template>
					<template #[`item.email`]='{item}'>
						<span v-if='item.email !== null'>
							{{ item.email }}
						</span>
						<v-icon
							v-else
							color='error'
						>
							mdi-close-circle-outline
						</v-icon>
					</template>
					<template #[`item.role`]='{item}'>
						<v-menu offset-y>
							<template #activator='{attrs, on}'>
								<v-btn
									color='success'
									small
									v-bind='attrs'
									v-on='on'
								>
									{{ $t(`core.user.roles.${item.role}`) }}
									<v-icon>mdi-menu-down</v-icon>
								</v-btn>
							</template>
							<v-list dense>
								<v-list-item
									v-for='role of roles'
									:key='role'
									dense
									@click='changeRole(item, role)'
								>
									{{ $t(`core.user.roles.${role}`) }}
								</v-list-item>
							</v-list>
						</v-menu>
					</template>
					<template #[`item.language`]='{item}'>
						<v-menu offset-y>
							<template #activator='{attrs, on}'>
								<v-btn
									color='success'
									small
									v-bind='attrs'
									v-on='on'
								>
									{{ $t(`core.user.languages.${item.language}`) }}
									<v-icon>mdi-menu-down</v-icon>
								</v-btn>
							</template>
							<v-list dense>
								<v-list-item
									v-for='language in ["cs", "en"]'
									:key='language'
									dense
									@click='changeLanguage(item, language)'
								>
									{{ $t(`core.user.languages.${language}`) }}
								</v-list-item>
							</v-list>
						</v-menu>
					</template>
					<template #[`item.actions`]='{item}'>
						<v-btn
							v-if='item.email !== null && item.state === "unverified"'
							class='mr-1'
							color='warning'
							small
							@click='resendVerification(item.id)'
						>
							<v-icon small>
								mdi-reload
							</v-icon>
							{{ $t('core.user.resendVerification') }}
						</v-btn>
						<v-btn
							class='mr-1'
							color='info'
							:to='"/user/edit/" + item.id'
							small
						>
							<v-icon small>
								mdi-pencil
							</v-icon>
							{{ $t('table.actions.edit') }}
						</v-btn>
						<v-btn
							class='mr-1'
							color='error'
							small
							@click='deleteUser(item)'
						>
							<v-icon small>
								mdi-delete
							</v-icon>
							{{ $t('table.actions.delete') }}
						</v-btn>
					</template>
				</v-data-table>
			</v-card-text>
		</v-card>
		<UserDeleteModal
			v-model='userDeleteModel'
			:only-user='users.length === 1'
			@deleted='getUsers'
		/>
	</div>
</template>

<script lang='ts'>
import {UserService} from '@iqrf/iqrf-gateway-webapp-client/services/Security';
import {
	UserEdit,
	UserInfo, UserLanguage,
	UserRole,
} from '@iqrf/iqrf-gateway-webapp-client/types';
import {AxiosError} from 'axios';
import {Component, Vue} from 'vue-property-decorator';
import {DataTableHeader} from 'vuetify';

import UserDeleteModal from '@/components/Core/UserDeleteModal.vue';
import {extendedErrorToast} from '@/helpers/errorToast';
import {useApiClient} from '@/services/ApiClient';

@Component({
	components: {
		UserDeleteModal,
	},
	metaInfo: {
		title: 'core.user.title',
	},
})

/**
 * List of existing users
 */
export default class UserList extends Vue {
	/**
	 * @var {boolean} loading Indicates that a request is in progress
	 */
	private loading = false;

	/**
	 * @var {UserInfo[]} users Array of user objects
	 */
	private users: UserInfo[] = [];

	/**
	 * @var {UserInfo|null} userDeleteModel User to delete
	 */
	private userDeleteModel: UserInfo|null = null;

	/**
	 * @var {Array<DataTableHeader>} headers Data table headers
	 */
	private readonly headers: Array<DataTableHeader> = [
		{
			value: 'username',
			text: this.$t('forms.fields.username').toString(),
		},
		{
			value: 'email',
			text: this.$t('forms.fields.email').toString(),
		},
		{
			value: 'role',
			text: this.$t('core.user.role').toString(),
		},
		{
			value: 'language',
			text: this.$t('core.user.language').toString(),
		},
		{
			value: 'actions',
			text: this.$t('table.actions.title').toString(),
			sortable: false,
			filterable: false,
			align: 'end',
		},
	];

	/**
	 * @constant {Array<string>} roles Array of user roles
	 */
	private readonly roles = [
		UserRole.Admin,
		UserRole.Normal,
		UserRole.BasicAdmin,
		UserRole.Basic,
	];

	/**
   * @property {UserService} service User service
   * @private
   */
	private service: UserService = useApiClient().getSecurityServices().getUserService();

	/**
	 * Retrieves list of existing user
	 */
	mounted(): void {
		this.getUsers();
	}

	/**
	 * Retrieves list of existing users
	 */
	private getUsers(): Promise<void> {
		if (!this.loading) {
			this.loading = true;
		}
		return this.service.list()
			.then((response: UserInfo[]) => {
				this.users = response;
				this.loading = false;
			})
			.catch((error: AxiosError) => {
				this.loading = false;
				extendedErrorToast(error, 'core.user.messages.listFetchFailed');
				if (error.response?.status === 403) {
					this.$router.push('/');
				}
			});
	}

	/**
	 * Changes user's role from table
	 * @param {UserInfo} user User object
	 * @param {UserRole} newRole New user role
	 */
	private changeRole(user: UserInfo, newRole: UserRole): void {
		if (user.role === newRole) {
			return;
		}
		user.role = newRole;
		this.edit(user);
	}

	/**
	 * Changes user's language from table
	 * @param {UserInfo} user User object
	 * @param {string} newLanguage New user language
	 */
	private changeLanguage(user: UserInfo, newLanguage: string): void {
		if (user.language === newLanguage) {
			return;
		}
		user.language = newLanguage as UserLanguage;
		this.edit(user);
	}

	/**
	 * Updates settings of a user object and then stores new values
	 * @param {UserInfo} user User object
	 */
	private edit(user: UserInfo) {
		if (user.id === undefined) {
			return;
		}
		this.loading = true;
		const config: UserEdit = {
			username: user.username,
			email: user.email,
			role: user.role,
			language: user.language,
		};
		return this.service.update(user.id, config)
			.then(() => {
				this.getUsers().then(() => {
					this.$toast.success(
						this.$t(
							'core.user.messages.editSuccess',
							{user: user.username},
						).toString()
					);
					if (user.id === this.$store.getters['user/getId']) {
						this.$store.dispatch('user/updateInfo');
					}
				});
			})
			.catch((error: AxiosError) => {
				this.loading = false;
				extendedErrorToast(error, 'core.user.messages.editFailed', {user: user.username});
			});
	}

	/**
	 * Requests a new verification email
	 * @param {number} userId User ID
	 */
	private resendVerification(userId: number): void {
		this.$store.commit('spinner/SHOW');
		this.service.resendVerificationEmail(userId)
			.then(() => {
				this.$store.commit('spinner/HIDE');
				this.$toast.success(
					this.$t('core.user.messages.resendSuccess').toString()
				);
			})
			.catch((error: AxiosError) => extendedErrorToast(error, 'core.user.messages.resendFailed'));
	}

	/**
	 * Opens user delete modal
	 * @param {UserInfo} user User
	 */
	private deleteUser(user: UserInfo): void {
		this.userDeleteModel = user;
	}
}
</script>

<style scoped>
.card-header {
	padding-bottom: 0;
}
</style>
