<!--
Copyright 2017-2025 IQRF Tech s.r.o.
Copyright 2019-2025 MICRORISC s.r.o.

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
		<v-card v-if='key !== null'>
			<v-card-title>{{ $t('core.security.apiKey.modal.title') }}</v-card-title>
			<v-card-text>{{ $t('core.security.apiKey.modal.prompt', {key: key.id}) }}</v-card-text>
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
import {ApiKeyInfo} from '@iqrf/iqrf-gateway-webapp-client/types/Security';
import {AxiosError} from 'axios';
import {Component, VModel, Vue} from 'vue-property-decorator';

import {extendedErrorToast} from '@/helpers/errorToast';
import {useApiClient} from '@/services/ApiClient';

@Component({})

/**
 * API key delete modal window component
 */
export default class ApiKeyDeleteModal extends Vue {
	/**
	 * API key to delete
	 */
	@VModel({required: true}) key!: ApiKeyInfo|null;

	/**
	 * Computes modal display condition
	 */
	get showModal(): boolean {
		return this.key !== null;
	}

	/**
	 * Removes an existing API key
	 */
	private remove(): void  {
		if (this.key === null) {
			return;
		}
		const id = this.key.id;
		this.$store.commit('spinner/SHOW');
		useApiClient().getSecurityServices().getApiKeyService().delete(id)
			.then(() => {
				this.$store.commit('spinner/HIDE');
				this.$toast.success(this.$t('core.security.apiKey.messages.deleteSuccess', {key: id}).toString());
				this.hideModal();
				this.$emit('deleted');
			})
			.catch((error: AxiosError) => {
				extendedErrorToast(error, 'core.security.apiKey.messages.deleteFailed', {key: id});
			});
	}

	/**
	 * Closes modal window
	 */
	private hideModal(): void {
		this.key = null;
	}
}
</script>
