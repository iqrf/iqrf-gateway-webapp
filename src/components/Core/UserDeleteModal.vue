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
	<CModal
		v-show='show'
		:show='show'
		color='danger'
		size='lg'
		:close-on-backdrop='false'
		:fade='false'
	>
		<template #header>
			<h5 class='modal-title'>
				{{ $t('core.user.modal.title') }}
			</h5>
		</template>
		<span>
			{{ $t('core.user.modal.prompt', {user: user.username}) }}
		</span>
		<template #footer>
			<CButton
				class='mr-1'
				color='secondary'
				@click='hideModal'
			>
				{{ $t('forms.cancel') }}
			</CButton>
			<CButton
				color='danger'
				@click='remove'
			>
				{{ $t('forms.delete') }}
			</CButton>
		</template>
	</CModal>
</template>

<script lang='ts'>
import {Component} from 'vue-property-decorator';
import {CButton, CModal} from '@coreui/vue/src';
import ModalBase from '@/components/ModalBase.vue';

import {extendedErrorToast} from '@/helpers/errorToast';
import {UserRole} from '@/services/AuthenticationService';

import UserService from '@/services/UserService';

import {AxiosError} from 'axios';
import {IUser} from '@/interfaces/Core/User';

@Component({
	components: {
		CButton,
		CModal,
	},
})
export default class UserDeleteModal extends ModalBase {
	/**
	 * @constant {IUser} defaultUser Default user
	 */
	private readonly defaultUser: IUser = {
		username: '',
		email: '',
		language: 'en',
		role: UserRole.BASIC
	};

	/**
	 * @var {IUser} user User
	 */
	private user: IUser = this.defaultUser;

	/**
	 * @var {boolean} onlyUser Current user is the only user
	 */
	private onlyUser = false;

	/**
	 * Removes an existing user
	 */
	private remove(): void {
		if (this.user.id === undefined) {
			return;
		}
		const id = this.user.id;
		this.$store.commit('spinner/SHOW');
		UserService.delete(id)
			.then(async () => {
				this.$store.commit('spinner/HIDE');
				this.$toast.success(
					this.$t(
						'core.user.messages.deleteSuccess',
						{user: this.user.username}
					).toString()
				);
				if (id === this.$store.getters['user/getId']) {
					await this.$store.dispatch('user/signOut');
					if (this.onlyUser) {
						await this.$store.dispatch('features/fetch');
						await this.$router.push('/install/');
					} else {
						await this.$router.push({path: '/sign/in', query: {redirect: this.$route.path}});
					}
					this.hideModal();
				} else {
					this.hideModal();
					this.$emit('deleted');
				}
			})
			.catch((error: AxiosError) => extendedErrorToast(error, 'core.user.messages.deleteFailed', {user: this.user.username}));
	}

	/**
	 * Stores user and shows modal window
	 * @param {IUser} user User
	 * @param {boolean} onlyUser
	 */
	public showModal(user: IUser, onlyUser: boolean): void {
		this.user = user;
		this.onlyUser = onlyUser;
		this.openModal();
	}

	/**
	 * Resets user and hides modal window
	 */
	private hideModal(): void {
		this.user = this.defaultUser;
		this.closeModal();
	}
}
</script>
