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
		<h4>{{ $t('config.controller.pins.profiles') }}</h4>
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
					@click='setPinConfigProfile(i)'
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
					@click='showDeleteModal(i, profile.name)'
				>
					<CIcon :content='cilTrash' />
					{{ $t('config.controller.pins.actions.delete') }}
				</CDropdownItem>
			</CDropdown>
		</CButtonGroup>
		<ControllerPinConfigDeleteModal ref='deleteModal' @delete-profile='deletePinConfigProfile' />
		<ControllerPinConfigForm ref='formModal' @save-profile='savePinConfigProfile' />
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CButton, CButtonGroup, CDropdown, CDropdownItem, CIcon} from '@coreui/vue/src';
import ControllerPinConfigDeleteModal from './ControllerPinConfigDeleteModal.vue';
import ControllerPinConfigForm from './ControllerPinConfigForm.vue';

import {cilCopy, cilPencil, cilPlus, cilTrash} from '@coreui/icons';
import {extendedErrorToast} from '@/helpers/errorToast';

import ControllerPinConfigService from '@/services/ControllerPinConfigService';

import {AxiosError, AxiosResponse} from 'axios';
import {IControllerPinConfig} from '@/interfaces/controller';

@Component({
	components: {
		CButton,
		CButtonGroup,
		CDropdown,
		CDropdownItem,
		CIcon,
		ControllerPinConfigDeleteModal,
		ControllerPinConfigForm,
	},
	data: () => ({
		cilCopy,
		cilPencil,
		cilPlus,
		cilTrash,
	}),
})

/**
 * Controller pin configurations component
 */
export default class ControllerPinConfigs extends Vue {
	/**
	 * @var {Array<IControllerPinConfig>} profiles Controller pin configuration profiles
	 */
	private profiles: Array<IControllerPinConfig> = [];

	/**
	 * Initializes pin configurations component
	 */
	mounted(): void {
		this.listConfigs();
	}

	/**
	 * Retrieves controller pin configurations
	 */
	private listConfigs(): Promise<void> {
		return ControllerPinConfigService.list()
			.then((rsp: AxiosResponse) => this.profiles = rsp.data)
			.catch((err: AxiosError) => extendedErrorToast(err, 'config.controller.pins.messages.listFailed'));
	}

	/**
	 * Emites pin config update event
	 * @param {number} idx Index of pin config profile
	 */
	private setPinConfigProfile(idx: number): void {
		this.$emit('update-pin-config', this.profiles[idx]);
	}

	/**
	 * Passes configuration profile to form modal and activates the modal
	 * @param {IControllerPinConfig|null} profile Configuration profile
	 */
	private showFormModal(profile: IControllerPinConfig|null = null): void {
		const config = JSON.parse(JSON.stringify(profile));
		(this.$refs.formModal as ControllerPinConfigForm).activateModal(config);
	}

	/**
	 * Saves configuration profile
	 * @param {IControllerPinConfig} profile Configuration profile
	 */
	private savePinConfigProfile(profile: IControllerPinConfig): void {
		const id = profile.id;
		delete profile.id;
		if (id === -1 || id === undefined) {
			this.addPinConfigProfile(profile);
		} else {
			this.editPinConfigProfile(id, profile);
		}
	}

	/**
	 * Adds a new configuration profile
	 * @param {IControllerPinConfig} profile Configuration profile
	 */
	private addPinConfigProfile(profile: IControllerPinConfig): void {
		const name = profile.name;
		this.$store.commit('spinner/SHOW');
		ControllerPinConfigService.add(profile)
			.then(() => {
				this.listConfigs().then(() => {
					this.$store.commit('spinner/HIDE');
					this.$toast.success(
						this.$t('config.controller.pins.messages.addSuccess', {profile: name}).toString()
					);
				});
			})
			.catch((err: AxiosError) => {
				extendedErrorToast(err, 'config.controller.pins.messages.addFailed', {profile: name});
			});
	}

	/**
	 * Edits an existing configuration profile
	 * @param {number} id Profile ID
	 * @param {IControllerPinConfig} profile Configuration profile
	 */
	private editPinConfigProfile(id: number, profile: IControllerPinConfig): void {
		const name = profile.name;
		this.$store.commit('spinner/SHOW');
		ControllerPinConfigService.edit(id, profile)
			.then(() => {
				this.listConfigs().then(() => {
					this.$store.commit('spinner/HIDE');
					this.$toast.success(
						this.$t('config.controller.pins.messages.editSuccess', {profile: name}).toString()
					);
				});
			})
			.catch((err: AxiosError) => {
				extendedErrorToast(err, 'config.controller.pins.messages.editFailed', {profile: name});
			});
	}

	/**
	 * Passes configuration profile to delete modal and activates the modal
	 * @param {number} idx Profile index
	 * @param {string} name Profile name
	 */
	private showDeleteModal(idx: number, name: string): void {
		(this.$refs.deleteModal as ControllerPinConfigDeleteModal).activateModal(idx, name);
	}

	/**
	 * Delete configuration profile
	 * @param {number} idx Profile index
	 */
	private deletePinConfigProfile(idx: number): void {
		const id = (this.profiles[idx].id as number);
		const name = this.profiles[idx].name;
		this.$store.commit('spinner/SHOW');
		ControllerPinConfigService.delete(id)
			.then(() => {
				this.listConfigs().then(() => {
					this.$store.commit('spinner/HIDE');
					this.$toast.success(
						this.$t('config.controller.pins.messages.deleteSuccess', {profile: name}).toString()
					);
				});
			})
			.catch((err: AxiosError) => {
				extendedErrorToast(err, 'config.controller.pins.messages.deleteFailed', {profile: name});
			});
	}
}
</script>
