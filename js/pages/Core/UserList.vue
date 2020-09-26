<template>
	<div>
		<CCard>
			<CCardHeader>
				<CButton
					color='success'
					to='/user/add/'
					size='sm'
					class='float-right'
				>
					<CIcon :content='$options.icons.add' />
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
								color='primary'
								:to='"/user/edit/" + item.id'
								size='sm'
							>
								<CIcon :content='$options.icons.edit' />
								{{ $t('table.actions.edit') }}
							</CButton>
							<CButton color='danger' size='sm' @click='confirmDelete(item)'>
								<CIcon :content='$options.icons.delete' />
								{{ $t('table.actions.delete') }}
							</CButton>
						</td>
					</template>
				</CDataTable>
			</CCardBody>
		</CCard>
		<CModal
			color='danger'
			:show.sync='modals.delete.user !== null'
		>
			<template #header>
				<h5 class='modal-title'>
					{{ $t('core.user.messages.delete.confirmTitle') }}
				</h5>
				<CButtonClose class='text-white' @click='modals.delete.user = null' />
			</template>
			<span v-if='modals.delete.user !== null'>
				{{ $t('core.user.messages.delete.confirm', {username: modals.delete.user.username}) }}
			</span>
			<template #footer>
				<CButton color='danger' @click='modals.delete.user = null'>
					{{ $t('forms.no') }}
				</CButton>
				<CButton color='success' @click='performDelete'>
					{{ $t('forms.yes') }}
				</CButton>
			</template>
		</CModal>
	</div>
</template>

<script>
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
import UserService from '../../services/UserService';

export default {
	name: 'UserList',
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
	data() {
		let fields = [];
		if (this.$store.getters['user/getRole'] === 'normal') {
			fields = [
				{
					key: 'username',
					label: this.$t('core.user.username'),
				},
				{
					key: 'actions',
					label: this.$t('table.actions.title'),
					sorter: false,
					filter: false,
				},
			];
		} else {
			fields = [
				{
					key: 'id',
					label: this.$t('core.user.id'),
				},
				{
					key: 'username',
					label: this.$t('core.user.username'),
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
		return {
			fields,
			modals: {
				delete: {
					user: null,
				}
			},
			users: [],
		};
	},
	created() {
		this.$store.commit('spinner/SHOW');
		this.getUsers();
	},
	methods: {
		getUsers() {
			return UserService.list()
				.then((response) => {
					this.$store.commit('spinner/HIDE');
					this.users = response.data;
				})
				.catch(() => {
					this.$store.commit('spinner/HIDE');
				});
		},
		changeRole(user, newRole) {
			this.edit(user, {role: newRole});
		},
		changeLanguage(user, newLanguage) {
			this.edit(user, {language: newLanguage});
		},
		edit(user, newSettings) {
			this.$store.commit('spinner/SHOW');
			let settings = {
				...user,
				...newSettings,
			};
			delete settings.id;
			return UserService.edit(user.id, settings)
				.then(() => {
					this.getUsers().then(() => {
						this.$toast.success(this.$t('core.user.messages.edit.success', {username: user.username}).toString());
					});
				})
				.catch(() => {
					this.$store.commit('spinner/HIDE');
				});
		},
		confirmDelete(user) {
			this.modals.delete.user = user;
		},
		performDelete() {
			this.$store.commit('spinner/SHOW');
			const user = this.modals.delete.user;
			this.modals.delete.user = null;
			UserService.delete(user.id)
				.then(() => {
					this.getUsers().then(() => {
						this.$toast.success(this.$t('core.user.messages.delete.success', {username: user.username}).toString());
					});
				})
				.catch(() => {
					this.$store.commit('spinner/HIDE');
				});
		},
	},
	icons: {
		add: cilPlus,
		delete: cilTrash,
		edit: cilPencil,
	},
	metaInfo: {
		title: 'core.user.title',
	},
};
</script>
