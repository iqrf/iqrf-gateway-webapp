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
							{{ toUnicodeEmail(item.email) }}
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
									dense
									@click='changeLanguage(item, "en")'
								>
									{{ $t('core.user.languages.en') }}
								</v-list-item>
							</v-list>
						</v-menu>
					</template>
					<template #[`item.actions`]='{item}'>
						<v-btn
							v-if='item.email !== null && item.state === "unverified"'
							color='warning'
							small
							@click='resendVerification(item.id)'
						>
							<v-icon small>
								mdi-reload
							</v-icon>
							{{ $t('core.user.resendVerification') }}
						</v-btn> <v-btn
							color='info'
							:to='"/user/edit/" + item.id'
							small
						>
							<v-icon small>
								mdi-pencil
							</v-icon>
							{{ $t('table.actions.edit') }}
						</v-btn> <UserDeleteDialog
							:user='item'
							@deleted='handleDeleteSuccess'
						/>
					</template>
				</v-data-table>
			</v-card-text>
		</v-card>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import UserDeleteDialog from '@/components/Core/UserDeleteDialog.vue';

import {extendedErrorToast} from '@/helpers/errorToast';
import UserService from '@/services/UserService';

import {UserRole} from '@/services/AuthenticationService';

import {AxiosError} from 'axios';
import {DataTableHeader} from 'vuetify';
import {IUser} from '@/interfaces/user';

import punycode from 'punycode/';


@Component({
	components: {
		UserDeleteDialog,
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
	 * @var {boolean} loading Loading data
	 */
	private loading = false;

	/**
	 * @var {Array<DataTableHeader>} headers Vuetify data table headers
	 */
	private headers: Array<DataTableHeader> = [
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
	 * @var {Array<User>} users Array of user objects
	 */
	private users: Array<IUser> = [];

	/**
	 * @constant {Array<string>} roles Array of user roles
	 */
	private roles = [
		UserRole.ADMIN,
		UserRole.NORMAL,
		UserRole.BASICADMIN,
		UserRole.BASIC,
	];

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
		this.loading = true;
		return UserService.list()
			.then((response: Array<IUser>) => {
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
	 * Converts email string to unicode
	 * @param {string} email Email to convert
	 * @returns {string} Unicode email string
	 */
	private toUnicodeEmail(email: string): string {
		return punycode.toUnicode(email);
	}

	/**
	 * Changes user's role from table
	 * @param {IUser} user User object
	 * @param {string} newRole New user role
	 */
	private changeRole(user: IUser, newRole: string): void {
		if (user.role === newRole) {
			return;
		}
		this.edit(user, {role: newRole});
	}

	/**
	 * Changes user's language from table
	 * @param {IUser} user User object
	 * @param {string} newLanguage New user language
	 */
	private changeLanguage(user: IUser, newLanguage: string): void {
		if (user.language === newLanguage) {
			return;
		}
		this.edit(user, {language: newLanguage});
	}

	/**
	 * Updates settings of a user object and then stores new values
	 * @param {IUser} user User object
	 * @param {Record<string, string>} newSettings Settings to apply to the user object
	 */
	private edit(user: IUser, newSettings: Record<string, string>) {
		if (user.id === undefined) {
			return;
		}
		const settings = {
			...user,
			...newSettings,
		};
		delete settings.id;
		this.$store.commit('spinner/SHOW');
		return UserService.edit(user.id, settings)
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
			.catch((error: AxiosError) => extendedErrorToast(error, 'core.user.messages.editFailed', {user: user.username}));
	}

	/**
	 * Handles user delete success REST API response
	 */
	private async handleDeleteSuccess(userId: number): Promise<void> {
		if (userId === this.$store.getters['user/getId']) {
			await this.$store.dispatch('user/signOut');
			if (this.users.length === 1) {
				await this.$store.dispatch('features/fetch');
				await this.$router.push('/install/');
			} else {
				await this.$router.push({path: '/sign/in', query: {redirect: this.$route.path}});
			}
			return;
		}
		this.getUsers();
	}

	/**
	 * Requests a new verification email
	 * @param {number} userId User ID
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

<style scoped>
.card-header {
	padding-bottom: 0;
}
</style>
