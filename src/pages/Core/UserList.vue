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
		<CCard>
			<CCardHeader class='border-0'>
				<CButton
					color='success'
					to='/user/add/'
					size='sm'
					class='float-right'
				>
					<CIcon :content='cilPlus' size='sm' />
					{{ $t('table.actions.add') }}
				</CButton>
			</CCardHeader>
			<CCardBody>
				<CDataTable
					:loading='loading'
					:items='users'
					:fields='fields'
					:column-filter='true'
					:items-per-page='20'
					:pagination='true'
					:striped='true'
					:sorter='{external: false, resetable: true}'
				>
					<template #no-items-view='{}'>
						{{ $t('table.messages.noRecords') }}
					</template>
					<template #email='{item}'>
						<td>
							<span v-if='item.email !== null'>
								{{ toUnicodeEmail(item.email) }}
							</span>
							<CIcon
								v-else
								class='text-danger'
								size='xl'
								:content='cilXCircle'
							/>
						</td>
					</template>
					<template #role='{item}'>
						<td>
							<CDropdown
								color='success'
								:toggler-text='$t(`core.user.roles.${item.role}`)'
								size='sm'
							>
								<CDropdownItem
									v-for='role of roles'
									:key='role'
									@click='changeRole(item, role)'
								>
									{{ $t(`core.user.roles.${role}`) }}
								</CDropdownItem>
							</CDropdown>
						</td>
					</template>
					<template #language='{item}'>
						<td>
							<CDropdown
								color='success'
								:toggler-text='$t(`core.user.languages.${item.language}`)'
								size='sm'
							>
								<CDropdownItem @click='changeLanguage(item, "en")'>
									{{ $t('core.user.languages.en') }}
								</CDropdownItem>
							</CDropdown>
						</td>
					</template>
					<template #actions='{item}'>
						<td class='col-actions'>
							<CButton
								v-if='item.email !== null && item.state === "unverified"'
								class='mr-1'
								color='warning'
								size='sm'
								@click='resendVerification(item.id)'
							>
								<CIcon :content='cilReload' size='sm' />
								{{ $t('core.user.resendVerification') }}
							</CButton>
							<CButton
								class='mr-1'
								color='info'
								:to='"/user/edit/" + item.id'
								size='sm'
							>
								<CIcon :content='cilPencil' size='sm' />
								{{ $t('table.actions.edit') }}
							</CButton>
							<CButton
								color='danger'
								size='sm'
								@click='removeUser(item)'
							>
								<CIcon :content='cilTrash' size='sm' />
								{{ $t('table.actions.delete') }}
							</CButton>
						</td>
					</template>
				</CDataTable>
			</CCardBody>
		</CCard>
		<UserDeleteModal ref='deleteModal' @deleted='getUsers' />
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {
	CButton,
	CCard,
	CCardBody,
	CCardHeader,
	CDataTable,
	CDropdown,
	CDropdownItem,
	CIcon
} from '@coreui/vue/src';
import UserDeleteModal from '@/components/Core/UserDeleteModal.vue';

import {cilPencil, cilPlus, cilReload, cilTrash, cilXCircle} from '@coreui/icons';
import {extendedErrorToast} from '@/helpers/errorToast';
import UserService from '@/services/UserService';

import {UserRole} from '@/services/AuthenticationService';

import {AxiosError} from 'axios';
import {IField} from '@/interfaces/Coreui';
import {IUser} from '@/interfaces/Core/User';

import punycode from 'punycode/';

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CDataTable,
		CDropdown,
		CDropdownItem,
		CIcon,
		UserDeleteModal,
	},
	data: () => ({
		cilPencil,
		cilPlus,
		cilReload,
		cilTrash,
		cilXCircle,
	}),
	metaInfo: {
		title: 'core.user.title',
	},
})

/**
 * List of existing users
 */
export default class UserList extends Vue {
	/**
	 * @var {Array<IField>} fields Array of CoreUI data table columns
	 */
	private readonly fields: Array<IField> = [
		{
			key: 'username',
			label: this.$t('forms.fields.username'),
		},
		{
			key: 'email',
			label: this.$t('forms.fields.email'),
		},
		{
			key: 'role',
			label: this.$t('core.user.role'),
		},
		{
			key: 'language',
			label: this.$t('core.user.language'),
		},
		{
			key: 'actions',
			label: this.$t('table.actions.title'),
			sorter: false,
			filter: false,
		},
	];

	/**
	 * @constant {Array<string>} roles Array of user roles
	 */
	private readonly roles = [
		UserRole.ADMIN,
		UserRole.NORMAL,
		UserRole.BASICADMIN,
		UserRole.BASIC,
	];

	/**
	 * @var {Array<User>} users Array of user objects
	 */
	private users: Array<IUser> = [];

	/**
	 * @var {boolean} loading Indicates that a request is in progress
	 */
	private loading = false;

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
		this.loading = true;
		const settings = {
			...user,
			...newSettings,
		};
		delete settings.id;
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
			.catch((error: AxiosError) => {
				this.loading = false;
				extendedErrorToast(error, 'core.user.messages.editFailed', {user: user.username});
			});
	}

	private removeUser(user: IUser): void {
		(this.$refs.deleteModal as UserDeleteModal).showModal(user, this.users.length === 1);
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
