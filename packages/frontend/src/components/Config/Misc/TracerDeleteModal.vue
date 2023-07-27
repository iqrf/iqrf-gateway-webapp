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
		<v-card>
			<v-card-title>
				<h5>{{ $t('config.daemon.misc.tracer.modal.title') }}</h5>
			</v-card-title>
			<v-card-text>
				{{ $t('config.daemon.misc.tracer.modal.prompt', {instance: instance}) }}
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
import {Component, VModel, Vue} from 'vue-property-decorator';

import {extendedErrorToast} from '@/helpers/errorToast';

import DaemonConfigurationService from '@/services/DaemonConfigurationService';

import {AxiosError} from 'axios';

/**
 * Tracer delete modal component
 */
@Component
export default class TracerDeleteModal extends Vue {

	/**
	 * @property {string|null} instance Instance to delete
	 */
	@VModel({required: true}) instance!: string|null;

	/**
	 * @constant {string} component Tracer component
	 */
	private readonly component = 'shape::TraceFileService';

	/**
	 * Computes modal display condition
	 */
	get showModal(): boolean {
		return this.instance !== null;
	}

	/**
	 * Removes instance of logging service component
	 */
	private remove(): void {
		if (this.instance === null) {
			return;
		}
		const instance = this.instance;
		this.$store.commit('spinner/SHOW');
		DaemonConfigurationService.deleteInstance(this.component, instance)
			.then(() => {
				this.$store.commit('spinner/HIDE');
				this.$toast.success(
					this.$t('config.daemon.misc.tracer.messages.deleteSuccess', {instance: instance})
						.toString()
				);
				this.hideModal();
				this.$emit('deleted');
			})
			.catch((error: AxiosError) => {
				extendedErrorToast(error, 'config.daemon.misc.tracer.messages.deleteFailed', {instance: instance});
			});
	}

	/**
	 * Closes modal window
	 */
	private hideModal(): void {
		this.instance = null;
	}
}
</script>
