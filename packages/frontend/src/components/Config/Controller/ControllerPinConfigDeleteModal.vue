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
		<v-card v-if='profile !== null'>
			<v-card-title>{{ $t('config.controller.deleteModal.title') }}</v-card-title>
			<v-card-text>{{ $t('config.controller.deleteModal.prompt', {profile: profile.name}) }}</v-card-text>
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
import {IqrfGatewayControllerMapping} from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import {AxiosError} from 'axios';
import {Component, VModel, Vue} from 'vue-property-decorator';

import {extendedErrorToast} from '@/helpers/errorToast';
import {useApiClient} from '@/services/ApiClient';

/**
 * Controller pin configuration delete modal window component
 */
@Component
export default class ControllerPinConfigDeleteModal extends Vue {

	/**
	 * @property {IqrfGatewayControllerMapping|null} profile Profile to delete
	 */
	@VModel({required: true, default: null}) profile!: IqrfGatewayControllerMapping|null;

	/**
	 * Computes modal display condition
	 */
	get showModal(): boolean {
		return this.profile !== null;
	}

	/**
	 * Removes configuration profile
	 */
	private remove(): void {
		if (this.profile === null || this.profile.id === undefined) {
			return;
		}
		const name = this.profile.name;
		this.$store.commit('spinner/SHOW');
		useApiClient().getConfigServices().getIqrfGatewayControllerService().deleteMapping(this.profile.id)
			.then(() => {
				this.$store.commit('spinner/HIDE');
				this.$toast.success(
					this.$t('config.controller.pins.messages.deleteSuccess', {profile: name}).toString()
				);
				this.hideModal();
				this.$emit('deleted');
			})
			.catch((err: AxiosError) => {
				extendedErrorToast(err, 'config.controller.pins.messages.deleteFailed', {profile: name});
			});
	}

	/**
	 * Hides modal window
	 */
	private hideModal(): void {
		this.profile = null;
	}
}
</script>
