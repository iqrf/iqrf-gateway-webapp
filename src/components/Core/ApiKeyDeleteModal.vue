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
		:show.sync='show'
		color='danger'
		size='lg'
		:close-on-backdrop='false'
		:fade='false'
	>
		<template #header>
			<h5 class='modal-title'>
				{{ $t('core.security.apiKey.modal.title') }}
			</h5>
		</template>
		{{ $t('core.security.apiKey.modal.prompt', {key: key.id}) }}
		<template #footer>
			<CButton
				class='mr-1'
				color='secondary'
				@click='closeModal'
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

import ApiKeyService from '@/services/ApiKeyService';

import {AxiosError} from 'axios';
import {IApiKey} from '@/interfaces/ApiKey';

@Component({
	components: {
		CButton,
		CModal,
	},
})
export default class ApiKeyDeleteModal extends ModalBase {
	/**
	 * @const {IApiKey} defaultKey Default API key
	 */
	private defaultKey: IApiKey = {
		id: 0,
		description: '',
		expiration: ''
	};

	/**
	 * @var {IApiKey} key API key to delete
	 */
	private key: IApiKey = this.defaultKey;

	/**
	 * Removes an existing API key
	 */
	private remove(): void  {
		this.$store.commit('spinner/SHOW');
		ApiKeyService.deleteApiKey(this.key.id)
			.then(() => {
				this.$store.commit('spinner/HIDE');
				this.$toast.success(this.$t('core.security.apiKey.messages.deleteSuccess', {key: this.key.id}).toString());
				this.hideModal();
				this.$emit('deleted');
			})
			.catch((error: AxiosError) => {
				extendedErrorToast(error, 'core.security.apiKey.messages.deleteFailed', {key: this.key.id});
			});
	}

	/**
	 * Stores key to delete and shows modal window
	 * @param {IApiKey} key API key to delete
	 */
	public showModal(key: IApiKey): void {
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
