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
					<CIcon :content='icons.add' size='sm' />
					{{ $t('table.actions.add') }}
				</CButton>
			</CCardHeader>
			<CCardBody>
				<CDataTable
					:items='users'
					:fields='fields'
					:column-filter='true'
					:items-per-page='20'
					:pagination='true'
					:striped='true'
					:sorter='{ external: false, resetable: true }'
				>
					<template #no-items-view='{}'>
						{{ $t('table.messages.noRecords') }}
					</template>
					<template #role='{item}'>
						<td>
							<CDropdown
								color='success'
								:toggler-text='$t("core.user.roles." + item.role)'
								size='sm'
							>
								<CDropdownItem @click='changeRole(item, "normal")'>
									{{ $t('core.user.roles.normal') }}
								</CDropdownItem>
								<CDropdownItem @click='changeRole(item, "power")'>
									{{ $t('core.user.roles.power') }}
								</CDropdownItem>
							</CDropdown>
						</td>
					</template>
					<template #language='{item}'>
						<td>
							<CDropdown
								color='success'
								:toggler-text='$t("core.user.languages." + item.language)'
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
								v-if='$store.getters["user/getRole"] === "power" || $store.getters["user/getName"] === item.username'
								color='info'
								:to='"/user/edit/" + item.id'
								size='sm'
							>
								<CIcon :content='icons.edit' size='sm' />
								{{ $t('table.actions.edit') }}
							</CButton> <CButton
								color='danger'
								size='sm'
								@click='confirmDelete(item)'
							>
								<CIcon :content='icons.delete' size='sm' />
								{{ $t('table.actions.delete') }}
							</CButton>
						</td>
					</template>
				</CDataTable>
			</CCardBody>
		</CCard>
		<CModal
			color='danger'
			:show='deleteUser !== null'
		>
			<template #header>
				<h5 class='modal-title'>
					{{ $t('core.user.modal.title') }}
				</h5>
			</template>
			<span v-if='deleteUser !== null'>
				{{ $t('core.user.modal.prompt', {user: deleteUser.username}) }}
			</span>
			<template #footer>
				<CButton
					color='danger'
					@click='performDelete'
				>
					{{ $t('forms.delete') }}
				</CButton> <CButton
					color='secondary'
					@click='deleteUser = null'
				>
					{{ $t('forms.cancel') }}
				</CButton> 
			</template>
		</CModal>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {
	CButton,
	CButtonClose,
	CCard,
	CCardBody,
	CCardHeader,
	CDataTable,
	CDropdown,
	CDropdownItem,
	CIcon,
	CModal
} from '@coreui/vue/src';

import {cilPencil, cilPlus, cilTrash} from '@coreui/icons';
import {extendedErrorToast} from '../../helpers/errorToast';
import UserService from '../../services/UserService';

import {AxiosError, AxiosResponse} from 'axios';
import {Dictionary} from 'vue-router/types/router';
import {IField} from '../../interfaces/coreui';
import {IUser} from '../../interfaces/user';
import { extend } from 'vee-validate';

@Component({
	components: {
		CButton,
		CButtonClose,
		CCard,
		CCardBody,
		CCardHeader,
		CDataTable,
		CDropdown,
		CDropdownItem,
		CIcon,
		CModal,
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
	 * @var {IUser|null} deleteUser User object used in remove modal
	 */
	private deleteUser: IUser|null = null

	/**
	 * @var {Dictionary<Array<string>>} icons Dictionary of CoreUI icons
	 */
	private icons: Dictionary<Array<string>> = {
		add: cilPlus,
		delete: cilTrash,
		edit: cilPencil,
	}

	/**
	 * @var {Array<IField>} fields Array of CoreUI data table columns
	 */
	private fields: Array<IField> = []

	/**
	 * @var {Array<User>} users Array of user objects
	 */
	private users: Array<IUser> = []
	
	/**
	 * Updates table fields by user rolr
	 */
	created(): void {
		if (this.$store.getters['user/getRole'] === 'normal') {
			this.fields = [
				{
					key: 'username',
					label: this.$t('forms.fields.username'),
				},
				{
					key: 'actions',
					label: this.$t('table.actions.title'),
					sorter: false,
					filter: false,
				},
			];
		} else {
			this.fields = [
				{
					key: 'id',
					label: this.$t('core.user.id'),
				},
				{
					key: 'username',
					label: this.$t('forms.fields.username'),
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
		}
	}

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
		if (!this.$store.getters['spinner/isEnabled']) {
			this.$store.commit('spinner/SHOW');
		}
		return UserService.list()
			.then((response: AxiosResponse) => {
				this.$store.commit('spinner/HIDE');
				this.users = response.data;
			})
			.catch((error: AxiosError) => extendedErrorToast(error, 'core.user.messages.listFetchFailed'));
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
	 * @param {Dictionary<string>} newSettings Settings to apply to the user obhect
	 */
	private edit(user: IUser, newSettings: Dictionary<string>) {
		if (user.id === undefined) {
			return;
		}
		let settings = {
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
				});
			})
			.catch((error: AxiosError) => extendedErrorToast(error, 'core.user.messages.editFailed', {user: user.username}));
	}

	/**
	 * Assigns user object to remove modal variable
	 */
	private confirmDelete(user: IUser): void {
		this.deleteUser = user;
	}

	/**
	 * Removes an existing user
	 */
	private performDelete(): void {
		if (this.deleteUser === null) {
			return;
		}
		const user = this.deleteUser;
		this.deleteUser = null;
		if (user.id === undefined) {
			return;
		}
		this.$store.commit('spinner/SHOW');
		UserService.delete(user.id)
			.then(() => this.handleDeleteSuccess(user))
			.catch((error: AxiosError) => extendedErrorToast(error, 'core.user.messages.deleteFailed', {user: user.username}));
	}

	/**
	 * Handles user delete success REST API response
	 * @param {IUser} user Removed user object
	 */
	private handleDeleteSuccess(user: IUser): void {
		if (user.id === this.$store.getters['user/getId']) {
			this.$store.dispatch('user/signOut');
			this.$store.commit('spinner/HIDE');
			this.$toast.success(
				this.$t(
					'core.user.messages.deleteSuccess',
					{user: user.username}
				).toString()
			);
			if (this.users.length === 1) {
				this.$router.push('/install/');
			} else {
				this.$router.push({path: '/sign/in', query: {redirect: this.$route.path}});
			}
			return;
		}
		this.getUsers().then(() => {
			this.$toast.success(
				this.$t(
					'core.user.messages.deleteSuccess', 
					{user: user.username}
				).toString()
			);
		});
	}
}
</script>

<style scoped>
.card-header {
	padding-bottom: 0;
}
</style>
