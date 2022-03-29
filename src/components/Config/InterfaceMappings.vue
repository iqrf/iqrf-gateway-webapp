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
					{{ $t('config.daemon.interfaces.interfaceMapping.set') }}
				</CDropdownItem>
				<CDropdownItem
					@click='invokeMappingForm(mapping)'
				>
					<CIcon :content='icons.edit' />
					{{ $t('config.daemon.interfaces.interfaceMapping.edit') }}
				</CDropdownItem>
				<CDropdownItem
					@click='showRemoveModal(i)'
				>
					<CIcon :content='icons.remove' />
					{{ $t('config.daemon.interfaces.interfaceMapping.delete') }}
				</CDropdownItem>
			</CDropdown>
		</CButtonGroup>
		<CModal
			color='danger'
			:show.sync='showModal'
		>
			<template #header>
				<h5 class='modal-title'>
					{{ $t('config.daemon.interfaces.interfaceMapping.modal.title') }}
				</h5>
			</template>
			{{ $t('config.daemon.interfaces.interfaceMapping.modal.prompt', {mapping: modalMapping}) }}
			<template #footer>
				<CButton
					color='danger'
					@click='removeMapping'
				>
					{{ $t('forms.delete') }}
				</CButton> <CButton
					color='secondary'
					@click='hideRemoveModal'
				>
					{{ $t('forms.cancel') }}
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
import MappingForm from '../../components/Config/MappingForm.vue';

import {cilCopy, cilPencil, cilPlus, cilTrash} from '@coreui/icons';
import {extendedErrorToast} from '../../helpers/errorToast';

import MappingService from '../../services/MappingService';

import {AxiosError, AxiosResponse} from 'axios';
import {IMapping} from '../../interfaces/mappings';

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
	private deleteMapping: IMapping|null = null;

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
	 * @var {Array<IMapping>} mappings Array of mappings
	 */
	private mappings: Array<IMapping> = [];

	/**
	 * @var {string} modalMapping Name of mapping used in remove modal
	 */
	private modalMapping = '';

	/**
	 * @var {boolean} showModal
	 */
	private showModal = false;

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
		return MappingService.getMappings()
			.then((response: AxiosResponse) => {
				this.handleMappingResponse(response.data);
			})
			.catch((error: AxiosError) => {
				extendedErrorToast(error, 'config.daemon.interfaces.interfaceMapping.messages.listFailed');
			});
	}

	/**
	 * Handles mapping REST API response
	 * @param {Array<IMapping>} mappings Array of mappings from REST API response
	 */
	private handleMappingResponse(mappings: Array<IMapping>): void {
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
	 * Hides the remove modal
	 */
	private hideRemoveModal(): void {
		this.showModal = false;
		this.deleteMapping = null;
		this.modalMapping = '';
	}

	/**
	 * Removes a mapping from mappings database
	 */
	private removeMapping(): void {
		if (this.deleteMapping === null || this.deleteMapping.id === undefined) {
			return;
		}
		this.showModal = false;
		this.$store.commit('spinner/SHOW');
		MappingService.removeMapping(this.deleteMapping.id)
			.then(() => {
				this.deleteMapping = null;
				this.getMappings().then(() => {
					this.$store.commit('spinner/HIDE');
					this.$toast.success(
						this.$t('config.daemon.interfaces.interfaceMapping.messages.deleteSuccess', {mapping: this.modalMapping}).toString()
					);
				});
				this.modalMapping = '';
			})
			.catch((error: AxiosError) => {
				this.deleteMapping = null;
				extendedErrorToast(error, 'config.daemon.interfaces.interfaceMapping.messages.deleteFailed', {mapping: this.modalMapping});
				this.modalMapping = '';
			});
	}

	/**
	 * Invokes mapping add or edit form
	 * @param {IMapping|null} mapping Mapping
	 */
	private invokeMappingForm(mapping: IMapping|null = null): void {
		(this.$refs.mappingModal as MappingForm).activateModal(mapping);
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
