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
	<span>
		<CButton
			color='danger'
			size='sm'
			@click='openModal'
		>
			<CIcon
				:content='cilTrash'
				size='sm'
			/>
			{{ $t('table.actions.delete') }}
		</CButton>
		<CModal
			:show.sync='show'
			color='danger'
			size='lg'
			:close-on-backdrop='false'
			:fade='false'
		>
			<template #header>
				<h5 class='modal-title'>
					{{ $t('config.daemon.misc.tracer.modal.title') }}
				</h5>
			</template>
			{{ $t('config.daemon.misc.tracer.modal.prompt', {instance: instance.instance}) }}
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
	</span>
</template>

<script lang='ts'>
import {Component, Prop} from 'vue-property-decorator';
import {CButton, CModal} from '@coreui/vue/src';
import ModalBase from '@/components/ModalBase.vue';

import {cilTrash} from '@coreui/icons';
import {extendedErrorToast} from '@/helpers/errorToast';

import DaemonConfigurationService from '@/services/DaemonConfigurationService';

import {AxiosError} from 'axios';
import {ITraceService} from '@/interfaces/Config/Misc';

/**
 * Tracer delete modal component
 */
@Component({
	components: {
		CButton,
		CModal,
	},
	data: () => ({
		cilTrash,
	}),
})
export default class TracerDeleteModal extends ModalBase {
	/**
	 * @property {ITraceService} instance Tracer instance to delete
	 */
	@Prop({required: true}) instance!: ITraceService;

	/**
	 * Removes instance of logging service component
	 */
	private remove(): void {
		this.closeModal();
		this.$store.commit('spinner/SHOW');
		DaemonConfigurationService.deleteInstance(this.instance.component, this.instance.instance)
			.then(() => {
				this.$store.commit('spinner/HIDE');
				this.$toast.success(
					this.$t('config.daemon.misc.tracer.messages.deleteSuccess', {instance: this.instance.instance})
						.toString()
				);
				this.$emit('deleted');
			})
			.catch((error: AxiosError) => {
				extendedErrorToast(error, 'config.daemon.misc.tracer.messages.deleteFailed', {instance: this.instance.instance});
			});
	}
}
</script>
