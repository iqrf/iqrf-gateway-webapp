<template>
	<div>
		<h4>{{ $t('config.interfaceMapping.boards') }}</h4>
		<CButtonGroup class='flex-wrap'>
			<CButton
				color='success'
				size='sm'
				@click='invokeMappingForm()'
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
					{{ $t('config.interfaceMapping.setMapping') }}
				</CDropdownItem>
				<CDropdownItem
					@click='invokeMappingForm(mapping.id)'
				>
					<CIcon :content='icons.edit' />
					{{ $t('config.interfaceMapping.editMapping') }}
				</CDropdownItem>
				<CDropdownItem
					@click='showRemoveModal(i)'
				>
					<CIcon :content='icons.remove' />
					{{ $t('config.interfaceMapping.removeMapping') }}
				</CDropdownItem>
			</CDropdown>
		</CButtonGroup>
		<CModal
			color='danger'
			:show.sync='showModal'
		>
			<template #header>
				<h5 class='modal-title'>
					{{ $t('config.interfaceMapping.removeTitle') }}
				</h5>
			</template>
			{{ $t('config.interfaceMapping.messages.removePrompt', {mapping: modalMapping}) }}
			<template #footer>
				<CButton color='danger' @click='showModal = false; deleteMapping = null'>
					{{ $t('forms.no') }}
				</CButton>
				<CButton color='success' @click='removeMapping'>
					{{ $t('forms.yes') }}
				</CButton>
			</template>
		</CModal>
		<MappingForm 
			ref='mappingModal'
			@update-mappings='getMappings'
		/>
	</div>
</template>

<script lang='ts'>
import {Component, Prop, Vue} from 'vue-property-decorator';
import {CButton, CButtonGroup, CDropdown, CDropdownItem, CModal} from '@coreui/vue/src';
import {cilCopy, cilPencil, cilPlus, cilTrash} from '@coreui/icons';
import {IMapping} from '../../interfaces/mappings';
import MappingService from '../../services/MappingService';
import { AxiosError, AxiosResponse } from 'axios';
import { Dictionary } from 'vue-router/types/router';
import FormErrorHandler from '../../helpers/FormErrorHandler';
import MappingForm from '../../components/Config/MappingForm.vue';

@Component({
	components: {
		CButton,
		CButtonGroup,
		CDropdown,
		CDropdownItem,
		CModal,
		MappingForm,
	},
})

/**
 * Interface configuration mapping
 */
export default class InterfaceMappings extends Vue {
	/**
	 * @var {IMapping|null} deleteMapping Mapping object to be remove
	 */
	private deleteMapping: IMapping|null = null

	/**
	 * @constant {Dictionary<Array<string>>} icons Array of CoreUI icons
	 */
	private icons: Dictionary<Array<string>> = {
		add: cilPlus,
		edit: cilPencil,
		remove: cilTrash,
		set: cilCopy,
	}

	/**
	 * @var {Array<IMapping>} mappings Array of mappings
	 */
	private mappings: Array<IMapping> = []

	/**
	 * @var {string} modalMapping Name of mapping used in remove modal
	 */
	private modalMapping = ''

	/**
	 * @var {boolean} showModal
	 */
	private showModal = false

	/**
	 * @property {string} interfaceType Communication interface type
	 */
	@Prop({required: true}) interfaceType!: string;

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
		this.$store.commit('spinner/SHOW');
		return MappingService.getMappings()
			.then((response: AxiosResponse) => this.handleMappingResponse(response.data))
			.catch((error: AxiosError) => FormErrorHandler.mappingError(error));
	}

	/**
	 * Handles mapping REST API response
	 * @param {Array<IMapping>} mappings Array of mappings from REST API response
	 */
	private handleMappingResponse(mappings: Array<IMapping>): void {
		this.$store.commit('spinner/HIDE');
		let filteredMappings: Array<IMapping> = [];
		mappings.forEach((mapping: IMapping) => {
			if (mapping.type !== this.interfaceType) {
				return;
			}
			filteredMappings.push(mapping);
		});
		this.mappings = filteredMappings;
	}

	/**
	 * Renders remove mapping modal
	 * @param {number} index Mapping index
	 */
	private showRemoveModal(index: number): void {
		this.deleteMapping = this.mappings[index];
		this.modalMapping = this.deleteMapping.name;
		this.showModal = true;
	}

	/**
	 * Removes a mapping from mappings database
	 */
	private removeMapping(): void {
		if (this.deleteMapping === null || this.deleteMapping.id === undefined) {
			return;
		} 
		this.showModal = false;
		MappingService.removeMapping(this.deleteMapping.id)
			.then(() => {
				this.getMappings().then(() => {
					this.$toast.success(
						this.$t('config.interfaceMapping.messages.removeSuccess', {mapping: this.modalMapping}).toString()
					);
				});
			})
			.catch((error: AxiosError) => FormErrorHandler.mappingError(error));
		this.deleteMapping = null;
	}

	/**
	 * Invokes mapping add or edit form
	 * @param {number|null} mappingId Mapping ID
	 */
	private invokeMappingForm(mappingId: number|null = null): void {
		(this.$refs.mappingModal as MappingForm).showModal(mappingId);
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
