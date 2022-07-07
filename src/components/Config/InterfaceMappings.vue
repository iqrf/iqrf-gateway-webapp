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
		<v-btn-toggle dense class='flex-wrap'>
			<v-btn
				color='success'
				size='sm'
				@click='showFormModal()'
			>
				<v-icon color='white'>
					mdi-plus
				</v-icon>
			</v-btn>
			<v-menu
				v-for='(mapping, i) of mappings'
				:key='i'
				:offset-y='true'
			>
				<template #activator='{on, attrs}'>
					<v-btn
						v-bind='attrs'
						color='primary'
						v-on='on'
					>
						{{ mapping.name }}
						<v-icon color='white'>
							mdi-menu-up
						</v-icon>
					</v-btn>
				</template>
				<v-list dense>
					<v-list-item @click='setMapping(i)'>
						<v-icon dense>
							mdi-content-copy
						</v-icon>
						{{ $t('config.daemon.interfaces.interfaceMapping.set') }}
					</v-list-item>
				</v-list>
				<MappingForm ref='formModal' @update-mappings='getMappings' />
				<v-list-item @click='showDeleteModal(i, mapping.name)'>
					<v-icon dense>
						mdi-delete
					</v-icon>
					{{ $t('config.daemon.interfaces.interfaceMapping.delete') }}
				</v-list-item>
			</v-menu>
		</v-btn-toggle>
		<MappingDeleteConfirmation ref='deleteModal' @delete-mapping='deleteMapping' />
	</div>
</template>

<script lang='ts'>
import {Component, Prop, Vue} from 'vue-property-decorator';
import MappingDeleteConfirmation from '@/components/Config/MappingDeleteConfirmation.vue';
import MappingForm from '@/components/Config/MappingForm.vue';

import {extendedErrorToast} from '@/helpers/errorToast';

import MappingService from '@/services/MappingService';

import {AxiosError} from 'axios';
import {IMapping, MappingType} from '@/interfaces/mappings';

@Component({
	components: {
		MappingDeleteConfirmation,
		MappingForm,
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
	 * Removes a mapping from mappings database
	 * @param {number} index Mapping index
	 */
	private deleteMapping(index: number): void {
		const id = (this.mappings[index].id as number);
		const name = this.mappings[index].name;
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
	 * Invokes mapping add or edit form
	 * @param {IMapping|null} mapping Mapping
	 */
	private showFormModal(mapping: IMapping|null = null): void {
		(this.$refs.formModal as MappingForm).activateModal(mapping);
	}

	/**
	 * Shows mapping delete modal
	 * @param {number} idx Mapping index
	 * @param {string} name Mapping name
	 */
	private showDeleteModal(idx: number, name: string): void {
		(this.$refs.deleteModal as MappingDeleteConfirmation).activateModal(idx, name);
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
