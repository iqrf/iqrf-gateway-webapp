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
	<div>
		<h5>{{ $t('config.daemon.interfaces.interfaceMapping.title') }}</h5>
		<v-dialog
			v-model='show'
			width='50%'
			persistent
			no-click-animation
		>
			<template #activator='{attrs, on}'>
				<v-btn
					color='primary'
					v-bind='attrs'
					v-on='on'
					@click='openModal'
				>
					{{ $t('config.daemon.interfaces.interfaceMapping.browse') }}
				</v-btn>
			</template>
			<v-card>
				<v-card-title>
					{{ $t('config.daemon.interfaces.interfaceMapping.profiles') }}
				</v-card-title>
				<v-card-text>
					<v-tabs v-model='activeTab' :show-arrows='true'>
						<v-tab>{{ $t('config.daemon.interfaces.interfaceMapping.adapters') }}</v-tab>
						<v-tab>{{ $t('config.daemon.interfaces.interfaceMapping.boards') }}</v-tab>
					</v-tabs>
					<v-divider />
					<v-tabs-items v-model='activeTab'>
						<v-tab-item :transition='false'>
							<InterfaceMappingGroup
								:mappings='adapterMappings'
								:loading='loading'
								@set-mapping='setMapping'
								@refresh-mappings='getMappings'
							/>
						</v-tab-item>
						<v-tab-item :transition='false'>
							<InterfaceMappingGroup
								:mappings='boardMappings'
								:loading='loading'
								@set-mapping='setMapping'
								@refresh-mappings='getMappings'
							/>
						</v-tab-item>
					</v-tabs-items>
				</v-card-text>
				<v-card-actions>
					<v-spacer />
					<v-btn
						@click='closeModal'
					>
						{{ $t('forms.close') }}
					</v-btn>
				</v-card-actions>
			</v-card>
		</v-dialog>
	</div>
</template>

<script lang='ts'>
import {Component, Prop} from 'vue-property-decorator';
import InterfaceMappingGroup from '@/components/Config/Interfaces/InterfaceMappingGroup.vue';
import ModalBase from '@/components/ModalBase.vue';

import {extendedErrorToast} from '@/helpers/errorToast';
import {ConfigDeviceType, MappingType} from '@/enums/Config/ConfigurationProfiles';

import MappingService from '@/services/MappingService';

import {AxiosError} from 'axios';
import {IMapping} from '@/interfaces/Config/Mapping';

@Component({
	components: {
		InterfaceMappingGroup,
	},
})

/**
 * Interface configuration mapping
 */
export default class InterfaceMappings extends ModalBase {

	/**
	 * @property {MappingType} interfaceType Communication interface type
	 */
	@Prop({required: true}) interfaceType!: MappingType;

	/**
	 * @var {boolean} loading Indicates that data is loading
	 */
	private loading = false;

	/**
	 * @var {Array<IMapping>} mappings Array of mappings
	 */
	private mappings: Array<IMapping> = [];

	/**
	 * @var {number} activeTab Currently selected tab
	 */
	private activeTab = 0;

	/**
	 * Computes adapter mapping options
	 * @return {Array<IMapping>} Adapter mappings
	 */
	get adapterMappings(): Array<IMapping> {
		return this.mappings.filter((mapping: IMapping): boolean => mapping.deviceType === ConfigDeviceType.ADAPTER);
	}

	/**
	 * Computes board mapping options
	 * @return {Array<IMapping>} Board mappings
	 */
	get boardMappings(): Array<IMapping> {
		return this.mappings.filter((mapping: IMapping): boolean => mapping.deviceType === ConfigDeviceType.BOARD);
	}

	/**
	 * Vue lifecycle hook mounted
	 */
	mounted(): void {
		this.getMappings();
	}

	/**
	 * Retrieves list of mappings
	 */
	private getMappings(): Promise<void> {
		this.loading = true;
		return MappingService.getMappings(this.interfaceType)
			.then((mappings: Array<IMapping>) => {
				this.mappings = mappings;
				this.loading = false;
			})
			.catch((error: AxiosError) => {
				this.loading = false;
				extendedErrorToast(error, 'config.daemon.interfaces.interfaceMapping.messages.listFailed');
			});
	}

	/**
	 * Emits selected mapping to parent component to update form fields
	 * @param {number} id Mapping profile ID
	 */
	private setMapping(id: number): void {
		const mapping = this.mappings.find((mapping: IMapping) => mapping.id === id);
		if (mapping !== undefined) {
			this.$emit('update-mapping', mapping);
		}
		this.closeModal();
	}
}
</script>
