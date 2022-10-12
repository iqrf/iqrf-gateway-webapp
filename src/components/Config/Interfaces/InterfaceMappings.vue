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
		<CButtonGroup class='flex-wrap'>
			<CButton
				color='success'
				size='sm'
				@click='showFormModal()'
			>
				<CIcon :content='icons.add' />
			</CButton>
			<CDropdown
				v-for='(mapping, i) of mappings'
				:key='i'
				:toggler-text='mapping.name'
				color='primary'
				placement='top-start'
			>
				<CDropdownItem
					@click='setMapping(i)'
				>
					<CIcon :content='icons.set' />
					{{ $t('config.daemon.interfaces.interfaceMapping.set') }}
				</CDropdownItem>
				<CDropdownItem
					@click='showFormModal(mapping)'
				>
					<CIcon :content='icons.edit' />
					{{ $t('config.daemon.interfaces.interfaceMapping.edit') }}
				</CDropdownItem>
				<CDropdownItem
					@click='showDeleteModal(i, mapping.name)'
				>
					<CIcon :content='icons.remove' />
					{{ $t('config.daemon.interfaces.interfaceMapping.delete') }}
				</CDropdownItem>
			</CDropdown>
		</CButtonGroup>
		<MappingFormModal ref='formModal' @update-mappings='getMappings' />
		<MappingDeleteConfirmation ref='deleteModal' @delete-mapping='deleteMapping' />
	</div>
</template>

<script lang='ts'>
import {Component, Prop, Vue} from 'vue-property-decorator';
import {CButton, CButtonGroup, CDropdown, CDropdownItem, CIcon, CModal} from '@coreui/vue/src';
import MappingDeleteModal from '@/components/Config/Interfaces/MappingDeleteModal.vue';
import MappingFormModal from '@/components/Config/Interfaces/MappingFormModal.vue';

import {cilCopy, cilPencil, cilPlus, cilTrash} from '@coreui/icons';
import {extendedErrorToast} from '@/helpers/errorToast';

import MappingService from '@/services/MappingService';

import {AxiosError} from 'axios';
import {IMapping, MappingType} from '@/interfaces/mappings';

@Component({
	components: {
		CButton,
		CButtonGroup,
		CDropdown,
		CDropdownItem,
		CIcon,
		CModal,
		MappingDeleteModal,
		MappingFormModal,
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
	 * @constant {Record<string, Array<string>>} icons Dictionary of CoreUI Icons
	 */
	private icons: Record<string, Array<string>> = {
		add: cilPlus,
		edit: cilPencil,
		remove: cilTrash,
		set: cilCopy,
	};

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
		(this.$refs.formModal as MappingFormModal).activateModal(mapping);
	}

	/**
	 * Shows mapping delete modal
	 * @param {number} idx Mapping index
	 * @param {string} name Mapping name
	 */
	private showDeleteModal(idx: number, name: string): void {
		(this.$refs.deleteModal as MappingDeleteModal).activateModal(idx, name);
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
