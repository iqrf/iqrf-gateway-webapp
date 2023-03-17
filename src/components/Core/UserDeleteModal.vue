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
	<v-dialog
		v-model='showModal'
		width='50%'
		persistent
		no-click-animation
	>
		<v-card v-if='user !== null'>
			<v-card-title>
				{{ $t('core.user.modal.title') }}
			</v-card-title>
			<v-card-text>
				{{ $t('core.user.modal.prompt', {user: user.username}) }}
			</v-card-text>
			<v-card-actions>
				<v-spacer />
				<v-btn
					@click='hideModal'
				>
					{{ $t('forms.cancel') }}
				</v-btn>
				<v-btn
					color='error'
					@click='remove'
				>
					{{ $t('forms.delete') }}
				</v-btn>
			</v-card-actions>
		</v-card>
	</v-dialog>
</template>

<script lang='ts'>
import {Component, Prop, VModel, Vue} from 'vue-property-decorator';

import {extendedErrorToast} from '@/helpers/errorToast';

import UserService from '@/services/UserService';

import {AxiosError} from 'axios';
import {IUser} from '@/interfaces/Core/User';

@Component({})
export default class UserDeleteModal extends Vue {
	/**
	 * User to delete
	 */
	@VModel({required: true}) user!: IUser|null;

	/**
	 * @property {boolean} onlyUser Deleted user is the only user
	 */
	@Prop({required: true}) onlyUser!: boolean;

	/**
	 * Computes modal display condition
	 */
	get showModal(): boolean {
		return this.user !== null;
	}

	/**
	 * Removes an existing user
	 */
	private remove(): void {
		if (this.user === null || this.user.id === undefined) {
			return;
		}
		const id = this.user.id;
		const username = this.user.username;
		this.$store.commit('spinner/SHOW');
		UserService.delete(id)
			.then(async () => {
				this.$store.commit('spinner/HIDE');
				this.$toast.success(
					this.$t(
						'core.user.messages.deleteSuccess',
						{user: username}
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
			.catch((error: AxiosError) => extendedErrorToast(error, 'core.user.messages.deleteFailed', {user: username}));
	}

	/**
	 * Resets user and hides modal window
	 */
	private hideModal(): void {
		this.user = null;
	}
}
</script>
