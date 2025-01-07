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
		<v-card v-if='mapping !== null'>
			<v-card-title>
				{{ $t('config.daemon.interfaces.interfaceMapping.deleteModal.title') }}
			</v-card-title>
			<v-card-text>
				{{ $t('config.daemon.interfaces.interfaceMapping.deleteModal.prompt', {mapping: mapping.name}) }}
			</v-card-text>
			<v-card-actions>
				<v-spacer />
				<v-btn
					@click='hideModal'
				>
					{{ $t('forms.close') }}
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
import {
	IqrfGatewayDaemonMapping
} from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import {AxiosError} from 'axios';
import {Component, VModel, Vue} from 'vue-property-decorator';

import {extendedErrorToast} from '@/helpers/errorToast';
import {useApiClient} from '@/services/ApiClient';

/**
 * Mapping delete modal window component
 */
@Component
export default class MappingDeleteModal extends Vue {

	/**
	 * @property {IqrfGatewayDaemonMapping|null} mapping Mapping to delete
	 */
	@VModel({required: true, default: null}) mapping!: IqrfGatewayDaemonMapping|null;

	/**
	 * Computes modal display condition
	 */
	get showModal(): boolean {
		return this.mapping !== null;
	}

	/**
	 * Removes mapping profile
	 */
	private remove(): void {
		if (this.mapping === null || this.mapping.id === undefined) {
			return;
		}
		const id = this.mapping.id;
		const name = this.mapping.name;
		this.$store.commit('spinner/SHOW');
		useApiClient().getConfigServices().getIqrfGatewayDaemonService().deleteMapping(id)
			.then(() => {
				this.$store.commit('spinner/HIDE');
				this.$toast.success(
					this.$t('config.daemon.interfaces.interfaceMapping.messages.deleteSuccess', {mapping: name}).toString()
				);
				this.hideModal();
				this.$emit('deleted');
			})
			.catch((error: AxiosError) => {
				extendedErrorToast(error, 'config.daemon.interfaces.interfaceMapping.messages.deleteFailed', {mapping: name});
			});
	}

	/**
	 * Hides modal window
	 */
	private hideModal(): void {
		this.mapping = null;
	}
}
</script>
