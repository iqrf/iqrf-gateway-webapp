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
	<v-dialog
		v-model='show'
		width='50%'
		persistent
		no-click-animation
	>
		<template #activator='{on, attrs}'>
			<v-btn
				color='error'
				small
				v-bind='attrs'
				v-on='on'
				@click='openDialog'
			>
				<v-icon small>
					mdi-delete
				</v-icon>
				{{ $t('table.actions.delete') }}
			</v-btn>
		</template>
		<v-card>
			<v-card-title>
				{{ $t('core.user.modal.title') }}
			</v-card-title>
			<v-card-text>
				{{ $t('core.user.modal.prompt', {user: user.username}) }}
			</v-card-text>
			<v-card-actions>
				<v-spacer />
				<v-btn
					@click='closeDialog'
				>
					{{ $t('forms.cancel') }}
				</v-btn>
				<v-btn
					color='error'
					@click='deleteUser'
				>
					{{ $t('forms.delete') }}
				</v-btn>
			</v-card-actions>
		</v-card>
	</v-dialog>
</template>

<script lang='ts'>
import {Component, Prop} from 'vue-property-decorator';
import DialogBase from '@/components/DialogBase.vue';

import {extendedErrorToast} from '@/helpers/errorToast';

import UserService from '@/services/UserService';

import {AxiosError} from 'axios';
import {IUser} from '@/interfaces/user';


@Component({})

/**
 * User delete dialog component
 */
export default class UserDeleteDialog extends DialogBase {
	/**
	 * @property {IUser} user User to delete
	 */
	@Prop({required: true}) user!: IUser;

	/**
	 * Removes an existing user
	 */
	private deleteUser(): void {
		if (!this.user.id) {
			return;
		}
		this.$store.commit('spinner/SHOW');
		UserService.delete(this.user.id)
			.then(() => {
				this.$store.commit('spinner/HIDE');
				this.$toast.success(
					this.$t(
						'core.user.messages.deleteSuccess',
						{user: this.user.username}
					).toString()
				);
				this.$emit('deleted', this.user.id);
			})
			.catch((error: AxiosError) => extendedErrorToast(error, 'core.user.messages.deleteFailed', {user: this.user.username}));
	}
}
</script>
