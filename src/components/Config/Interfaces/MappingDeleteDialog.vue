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
			<v-list-item
				v-bind='attrs'
				v-on='on'
				@click='showDialog'
			>
				<v-icon dense>
					mdi-delete
				</v-icon>
				{{ $t('config.daemon.interfaces.interfaceMapping.delete') }}
			</v-list-item>
		</template>
		<v-card>
			<v-card-title>
				{{ $t('config.daemon.interfaces.interfaceMapping.deleteModal.title') }}
			</v-card-title>
			<v-card-text>
				{{ $t('config.daemon.interfaces.interfaceMapping.deleteModal.prompt', {mapping: mapping.name}) }}
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
					@click='deleteMapping'
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

import MappingService from '@/services/MappingService';

import {AxiosError} from 'axios';
import {IMapping} from '@/interfaces/mappings';

/**
 * Mapping delete confirmation modal window component
 */
@Component
export default class MappingDeleteDialog extends DialogBase {
	/**
	 * @property {IMapping} mapping Mapping to delete
	 */
	@Prop({required: true}) mapping!: IMapping;

	/**
	 * Removes a mapping from mappings database
	 */
	private deleteMapping(): void {
		if (this.mapping.id === undefined) {
			return;
		}
		this.$store.commit('spinner/SHOW');
		MappingService.removeMapping(this.mapping.id)
			.then(() => {
				this.closeDialog();
				this.$store.commit('spinner/HIDE');
				this.$toast.success(
					this.$t(
						'config.daemon.interfaces.interfaceMapping.messages.deleteSuccess',
						{mapping: this.mapping.name}
					).toString()
				);
				this.$emit('deleted');
			})
			.catch((error: AxiosError) => {
				const params = {mapping: this.mapping.name};
				extendedErrorToast(error, 'config.daemon.interfaces.interfaceMapping.messages.deleteFailed', params);
			});
	}

	/**
	 * Emits event to close parent menu (temporary workaround) and opens dialog
	 */
	private showDialog(): void {
		this.$emit('close-menu');
		this.openDialog();
	}
}
</script>
