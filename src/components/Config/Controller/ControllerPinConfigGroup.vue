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
		<CButtonGroup class='flex-wrap'>
			<CButton
				color='success'
				size='sm'
				@click='showFormModal()'
			>
				<CIcon :content='cilPlus' />
			</CButton>
			<CDropdown
				v-for='(profile, i) of profiles'
				:key='i'
				:toggler-text='profile.name'
				color='primary'
				placement='top-start'
			>
				<CDropdownItem
					@click='setProfile(profile.id)'
				>
					<CIcon :content='cilCopy' />
					{{ $t('config.controller.pins.actions.set') }}
				</CDropdownItem>
				<CDropdownItem
					@click='showFormModal(profile)'
				>
					<CIcon :content='cilPencil' />
					{{ $t('config.controller.pins.actions.edit') }}
				</CDropdownItem>
				<CDropdownItem
					@click='showDeleteModal(profile.id, profile.name)'
				>
					<CIcon :content='cilTrash' />
					{{ $t('config.controller.pins.actions.delete') }}
				</CDropdownItem>
			</CDropdown>
		</CButtonGroup>
		<ControllerPinConfigDeleteModal ref='deleteModal' @delete-profile='deleteProfile' />
		<ControllerPinConfigFormModal ref='formModal' @update-profiles='refreshProfiles' />
	</div>
</template>

<script lang='ts'>
import {Component, PropSync, Vue} from 'vue-property-decorator';
import {CButton, CButtonGroup, CDropdown, CDropdownItem, CIcon} from '@coreui/vue/src';
import ControllerPinConfigDeleteModal from './ControllerPinConfigDeleteModal.vue';
import ControllerPinConfigFormModal from './ControllerPinConfigFormModal.vue';

import {cilCopy, cilPencil, cilPlus, cilTrash} from '@coreui/icons';
import {IControllerPinConfig} from '@/interfaces/Config/Controller';

@Component({
	components: {
		CButton,
		CButtonGroup,
		CDropdown,
		CDropdownItem,
		CIcon,
		ControllerPinConfigDeleteModal,
		ControllerPinConfigFormModal,
	},
	data: () => ({
		cilCopy,
		cilPencil,
		cilPlus,
		cilTrash,
	}),
})

/**
 * Controller pin configuration profile group
 */
export default class ControllerPinConfigGroup extends Vue {
	/**
	 * @property {Array<IControllerPinConfig>} _profiles Controller config profiles
	 */
	@PropSync('profiles', {type: Array, default: []}) _profiles!: Array<IControllerPinConfig>;

	/**
	 * Passes configuration profile to form modal and activates the modal
	 * @param {IControllerPinConfig|null} profile Configuration profile
	 */
	private showFormModal(profile: IControllerPinConfig|null = null): void {
		(this.$refs.formModal as ControllerPinConfigFormModal).activateModal(profile);
	}

	/**
	 * Passes configuration profile to delete modal and activates the modal
	 * @param {number} idx Profile ID
	 * @param {string} name Profile name
	 */
	private showDeleteModal(id: number, name: string): void {
		(this.$refs.deleteModal as ControllerPinConfigDeleteModal).activateModal(id, name);
	}

	/**
	 * Emits event to set config profile to form
	 * @param {number} id Config profile ID
	 */
	private setProfile(id: number): void {
		this.$emit('set-profile', id);
	}

	/**
	 * Emits event to delete config profile
	 * @param {number} id Config profile ID
	 */
	private deleteProfile(id: number): void {
		this.$emit('delete-profile', id);
	}

	/**
	 * Emits event to refresh config profiles
	 */
	private refreshProfiles(): void {
		this.$emit('refresh-profiles');
	}
}
</script>
