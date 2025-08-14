<template>
	<div>
		<CButtonGroup class='flex-wrap'>
			<CButton
				color='success'
				size='sm'
				@click='showFormModal()'
			>
				<CIcon :content='cilPlus' />
			</CButton>
			<CDropdown
				v-for='(mapping, i) of mappings'
				:key='i'
				:toggler-text='mapping.name'
				color='primary'
				placement='top-start'
			>
				<CDropdownItem
					@click='setMapping(mapping.id)'
				>
					<CIcon :content='cilCopy' />
					{{ $t('config.daemon.interfaces.interfaceMapping.set') }}
				</CDropdownItem>
				<CDropdownItem
					@click='showFormModal(mapping)'
				>
					<CIcon :content='cilPencil' />
					{{ $t('config.daemon.interfaces.interfaceMapping.edit') }}
				</CDropdownItem>
				<CDropdownItem
					@click='showDeleteModal(mapping.id, mapping.name)'
				>
					<CIcon :content='cilTrash' />
					{{ $t('config.daemon.interfaces.interfaceMapping.delete') }}
				</CDropdownItem>
			</CDropdown>
		</CButtonGroup>
		<MappingFormModal ref='formModal' @update-mappings='refreshMappings' />
		<MappingDeleteModal ref='deleteModal' @delete-mapping='deleteMapping' />
	</div>
</template>

<script lang='ts'>
import {Component, PropSync, Vue} from 'vue-property-decorator';
import {CButton, CButtonGroup, CDropdown, CDropdownItem, CIcon} from '@coreui/vue/src';
import MappingDeleteModal from '@/components/Config/Interfaces/MappingDeleteModal.vue';
import MappingFormModal from '@/components/Config/Interfaces/MappingFormModal.vue';

import {cilCopy, cilPencil, cilPlus, cilTrash} from '@coreui/icons';

import {IMapping} from '@/interfaces/Config/Mapping';

@Component({
	components: {
		CButton,
		CButtonGroup,
		CDropdown,
		CDropdownItem,
		CIcon,
		MappingDeleteModal,
		MappingFormModal,
	},
	data: () => ({
		cilCopy,
		cilPencil,
		cilPlus,
		cilTrash,
	}),
})

/**
 * Interface mapping group component
 */
export default class InterfaceMappingGroup extends Vue {
	/**
	 * @property {Array<IMapping>} _mappings Mapping profiles
	 */
	@PropSync('mappings', {type: Array, default: []}) _mappings!: Array<IMapping>;

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
	 * Emits event to set mapping profile to form
	 * @param {number} id Mapping profile ID
	 */
	private setMapping(id: number): void {
		this.$emit('set-mapping', id);
	}

	/**
	 * Emits event to delete mapping profile
	 * @param {number} id Mapping profile ID
	 */
	private deleteMapping(id: number): void {
		this.$emit('delete-mapping', id);
	}

	/**
	 * Emits event to refresh mapping profiles
	 */
	private refreshMappings(): void {
		this.$emit('refresh-mappings');
	}
}
</script>
