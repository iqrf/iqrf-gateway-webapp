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
		<template #activator='{attrs, on}'>
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
			<v-card-title>{{ $t('core.security.ssh.modal.title') }}</v-card-title>
			<v-card-text>{{ $t('core.security.ssh.modal.prompt', {id: sshKey.id}) }}</v-card-text>
			<v-card-actions>
				<v-spacer />
				<v-btn
					@click='closeDialog'
				>
					{{ $t('forms.cancel') }}
				</v-btn>
				<v-btn
					color='error'
					@click='deleteKey'
				>
					{{ $t('forms.delete') }}
				</v-btn>
			</v-card-actions>
		</v-card>
	</v-dialog>
</template>

<script lang='ts'>
import {Component, Prop} from 'vue-property-decorator';
import DialogBase from '../DialogBase.vue';

import {extendedErrorToast} from '@/helpers/errorToast';

import SshService from '@/services/SshService';

import {AxiosError} from 'axios';
import {ISshKey} from '@/interfaces/ssh';

/**
 * API key delete dialog component
 */
@Component
export default class SshKeyDeleteDialog extends DialogBase {
	/**
	 * @property {ISshKey} sshKey SSH key to delete
	 */
	@Prop({required: true}) sshKey!: ISshKey;

	/**
	 * Removes authorized SSH public key
	 */
	private deleteKey(): void {
		if (this.sshKey.id === undefined) {
			return;
		}
		const id: number = this.sshKey.id;
		this.$store.commit('spinner/SHOW');
		SshService.deleteKey(id)
			.then(() => {
				this.$store.commit('spinner/HIDE');
				this.$toast.success(
					this.$t(
						'core.security.ssh.messages.deleteSuccess',
						{id: id}
					).toString()
				);
				this.$emit('deleted');
			})
			.catch((error: AxiosError) => extendedErrorToast(error, 'core.security.ssh.messages.deleteFailed', {id: id}));
	}
}
</script>
