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
		:show.sync='show'
		color='danger'
		size='lg'
		:close-on-backdrop='false'
		:fade='false'
	>
		<template #header>
			<h5 class='modal-title'>
				{{ $t('core.security.ssh.modal.title') }}
			</h5>
		</template>
		{{ $t('core.security.ssh.modal.prompt', {id: key.id}) }}
		<template #footer>
			<CButton
				class='mr-1'
				colors='secondary'
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

import SshService from '@/services/SshService';

import {AxiosError} from 'axios';
import {ISshKey} from '@/interfaces/Core/SshKey';

@Component({
	components: {
		CButton,
		CModal,
	},
})

/**
 * SSH key delete modal window component
 */
export default class SshKeyDeleteModal extends ModalBase {
	/**
	 * @constant {ISshKey} defaultKey Default SSH key
	 */
	private readonly defaultKey: ISshKey = {
		id: 0,
		description: '',
		type: '',
		hash: '',
		key: '',
		createdAt: '',
	};

	/**
	 * @var {ISshKey} key SSH key to delete
	 */
	private key: ISshKey = this.defaultKey;

	/**
	 * Removes an existing SSH key
	 */
	private remove(): void {
		if (this.key === null) {
			return;
		}
		const key = this.key;
		this.$store.commit('spinner/SHOW');
		SshService.deleteKey(this.key.id)
			.then(() => {
				this.$store.commit('spinner/HIDE');
				this.$toast.success(
					this.$t('core.security.ssh.messages.deleteSuccess', {id: key.id}).toString()
				);
				this.hideModal();
				this.$emit('deleted');
			})
			.catch((error: AxiosError) => {
				extendedErrorToast(error, 'core.security.ssh.messages.deleteFailed', {id: key.id});
			});
	}

	/**
	 * Stores key to delete and shows modal window
	 * @param {ISshKey} key SSH key to delete
	 */
	public showModal(key: ISshKey): void {
		this.key = key;
		this.openModal();
	}

	/**
	 * Resets key to delete and hides modal window
	 */
	private hideModal(): void {
		this.key = this.defaultKey;
		this.closeModal();
	}
}
</script>
