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
		<CTabs
			variant='tabs'
			:active-tab='activeTab'
		>
			<CTab :title='$t("config.daemon.interfaces.interfaceMapping.adapters")'>
				<InterfaceMappingGroup
					class='my-1'
					:mappings='adapterMappings'
					@set-mapping='setMapping'
					@delete-mapping='deleteMapping'
				/>
			</CTab>
			<CTab :title='$t("config.daemon.interfaces.interfaceMapping.boards")'>
				<InterfaceMappingGroup
					class='my-1'
					:mappings='boardMappings'
					@set-mapping='setMapping'
					@delete-mapping='deleteMapping'
				/>
			</CTab>
		</CTabs>
	</div>
</template>

<script lang='ts'>
import {Component, Prop, Vue} from 'vue-property-decorator';
import {CTab, CTabs} from '@coreui/vue/src';
import InterfaceMappingGroup from '@/components/Config/Interfaces/InterfaceMappingGroup.vue';

import {extendedErrorToast} from '@/helpers/errorToast';
import {ConfigDeviceType, MappingType} from '@/enums/Config/ConfigurationProfiles';

import MappingService from '@/services/MappingService';

import {AxiosError} from 'axios';
import {IMapping} from '@/interfaces/Config/Mapping';

@Component({
	components: {
		CTab,
		CTabs,
		InterfaceMappingGroup,
	},
})

/**
 * Interface configuration mapping
 */
export default class InterfaceMappings extends Vue {
	/**
	 * @var {Array<IMapping>} mappings Array of mappings
	 */
	private mappings: Array<IMapping> = [];

	/**
	 * @property {MappingType} interfaceType Communication interface type
	 */
	@Prop({required: true}) interfaceType!: MappingType;

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
		return MappingService.getMappings(this.interfaceType)
			.then((mappings: Array<IMapping>) => {
				this.mappings = mappings;
			})
			.catch((error: AxiosError) => {
				extendedErrorToast(error, 'config.daemon.interfaces.interfaceMapping.messages.listFailed');
			});
	}

	/**
	 * Removes a mapping from mappings database
	 * @param {number} id Mapping profile ID
	 */
	private deleteMapping(id: number): void {
		const mapping = this.mappings.find((mapping: IMapping) => mapping.id === id);
		if (mapping === undefined) {
			return;
		}
		const name = mapping.name;
		this.$store.commit('spinner/SHOW');
		MappingService.removeMapping(id)
			.then(() => {
				this.getMappings().then(() => {
					this.$store.commit('spinner/HIDE');
					this.$toast.success(
						this.$t('config.daemon.interfaces.interfaceMapping.messages.deleteSuccess', {mapping: name}).toString()
					);
				});
			})
			.catch((error: AxiosError) => {
				extendedErrorToast(error, 'config.daemon.interfaces.interfaceMapping.messages.deleteFailed', {mapping: name});
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
	}
}
</script>
