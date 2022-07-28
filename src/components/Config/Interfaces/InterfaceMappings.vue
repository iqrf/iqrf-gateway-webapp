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
	<div>
		<h4>{{ $t('config.daemon.interfaces.interfaceMapping.boards') }}</h4>
		<v-btn-toggle class='flex-wrap' dense>
			<MappingFormDialog
				:type='interfaceType'
				@saved='getMappings'
			/>
			<v-menu
				v-for='(mapping, i) of mappings'
				:key='i'
				ref='menu'
				top
				:offset-y='true'
			>
				<template #activator='{attrs, on}'>
					<v-btn
						color='primary'
						small
						v-bind='attrs'
						v-on='on'
					>
						{{ mapping.name }}
						<v-icon color='white'>
							mdi-menu-up
						</v-icon>
					</v-btn>
				</template>
				<v-list dense>
					<v-list-item
						@click='setMapping(i)'
					>
						<v-icon dense>
							mdi-content-copy
						</v-icon>
						{{ $t('config.daemon.interfaces.interfaceMapping.set') }}
					</v-list-item>
					<MappingFormDialog
						:type='interfaceType'
						:mapping='mapping'
						@saved='getMappings'
						@close-menu='$refs.menu[i].isActive = false'
					/>
					<MappingDeleteDialog
						:mapping='mapping'
						@deleted='getMappings'
						@close-menu='$refs.menu[i].isActive = false'
					/>
				</v-list>
			</v-menu>
		</v-btn-toggle>
	</div>
</template>

<script lang='ts'>
import {Component, Prop, Vue} from 'vue-property-decorator';
import MappingDeleteDialog from '@/components/Config/Interfaces/MappingDeleteDialog.vue';
import MappingFormDialog from '@/components/Config/Interfaces/MappingFormDialog.vue';

import {extendedErrorToast} from '@/helpers/errorToast';

import MappingService from '@/services/MappingService';

import {AxiosError} from 'axios';
import {IMapping, MappingType} from '@/interfaces/mappings';

@Component({
	components: {
		MappingDeleteDialog,
		MappingFormDialog,
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
	 * Emits selected mapping to parent component to update form fields
	 * @param {number} index Mapping index
	 */
	private setMapping(index: number): void {
		const mapping = this.mappings[index];
		this.$emit('update-mapping', mapping);
	}
}
</script>
