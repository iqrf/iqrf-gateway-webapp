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
		<v-btn-toggle class='flex-wrap'>
			<v-btn
				color='success'
				small
				@click='showFormModal()'
			>
				<v-icon color='white'>
					mdi-plus
				</v-icon>
			</v-btn>
			<v-menu
				v-for='(profile, i) of profiles'
				:key='i'
				:offset-y='true'
			>
				<template #activator='{on, attrs}'>
					<v-btn
						v-bind='attrs'
						color='primary'
						small
						v-on='on'
					>
						{{ profile.name }}
						<v-icon color='white'>
							mdi-menu-up
						</v-icon>
					</v-btn>
				</template>
				<v-list dense>
					<v-list-item @click='setPinConfigProfile(i)'>
						<v-icon dense>
							mdi-content-copy
						</v-icon>
						{{ $t('config.controller.pins.actions.set') }}
					</v-list-item>
					<v-list-item @click='showFormModal(profile)'>
						<v-icon dense>
							mdi-pencil
						</v-icon>
						{{ $t('config.controller.pins.actions.edit') }}
					</v-list-item>
					<v-list-item @click='showDeleteModal(i, profile.name)'>
						<v-icon dense>
							mdi-delete
						</v-icon>
						{{ $t('config.controller.pins.actions.delete') }}
					</v-list-item>
				</v-list>
			</v-menu>
		</v-btn-toggle>
		<ControllerPinConfigDeleteConfirmation ref='deleteModal' @delete-profile='deletePinConfigProfile' />
		<ControllerPinConfigForm ref='formModal' @save-profile='savePinConfigProfile' />
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import ControllerPinConfigDeleteConfirmation from './ControllerPinConfigDeleteConfirmation.vue';
import ControllerPinConfigForm from './ControllerPinConfigForm.vue';

import {extendedErrorToast} from '@/helpers/errorToast';

import ControllerPinConfigService from '@/services/ControllerPinConfigService';

import {AxiosError, AxiosResponse} from 'axios';
import {IControllerPinConfig} from '@/interfaces/controller';

@Component({
	components: {
		ControllerPinConfigDeleteConfirmation,
		ControllerPinConfigForm,
	},
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
		(this.$refs.deleteModal as ControllerPinConfigDeleteConfirmation).activateModal(idx, name);
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
