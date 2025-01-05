<!--
Copyright 2017-2025 IQRF Tech s.r.o.
Copyright 2019-2025 MICRORISC s.r.o.

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
		<CTabs
			variant='tabs'
			:active-tab='activeTab'
		>
			<CTab :title='$t("config.controller.pins.adapters")'>
				<ControllerPinConfigGroup
					class='my-1'
					:profiles='adapterProfiles'
					@set-profile='setProfile'
					@delete-profile='deleteProfile'
					@refresh-profiles='listConfigs'
				/>
			</CTab>
			<CTab :title='$t("config.controller.pins.boards")'>
				<ControllerPinConfigGroup
					class='my-1'
					:profiles='boardProfiles'
					@set-profile='setProfile'
					@delete-profile='deleteProfile'
					@refresh-profiles='listConfigs'
				/>
			</CTab>
		</CTabs>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CTab, CTabs} from '@coreui/vue/src';
import ControllerPinConfigGroup from '@/components/Config/Controller/ControllerPinConfigGroup.vue';

import {cilCopy, cilPencil, cilPlus, cilTrash} from '@coreui/icons';
import {extendedErrorToast} from '@/helpers/errorToast';
import {ConfigDeviceType} from '@/enums/Config/ConfigurationProfiles';

import ControllerPinConfigService from '@/services/ControllerPinConfigService';

import {AxiosError, AxiosResponse} from 'axios';
import {IControllerPinConfig} from '@/interfaces/Config/Controller';

@Component({
	components: {
		CTab,
		CTabs,
		ControllerPinConfigGroup,
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
	 * @var {number} activeTab Currently selected tab
	 */
	private activeTab = 0;

	/**
	 * @var {Array<IControllerPinConfig>} profiles Controller pin configuration profiles
	 */
	private profiles: Array<IControllerPinConfig> = [];

	/**
	 * Computes adapter controller profile options
	 * @return {Array<IControllerPinConfig>} Adapter controller profile options
	 */
	get adapterProfiles(): Array<IControllerPinConfig> {
		return this.profiles.filter((profile: IControllerPinConfig): boolean => profile.deviceType === ConfigDeviceType.ADAPTER);
	}

	/**
	 * Computes board controller profile options
	 * @return {Array<IControllerPinConfig>} Board controller profile options
	 */
	get boardProfiles(): Array<IControllerPinConfig> {
		return this.profiles.filter((profile: IControllerPinConfig): boolean => profile.deviceType === ConfigDeviceType.BOARD);
	}

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
	 * Delete configuration profile
	 * @param {number} id Config profile ID
	 */
	private deleteProfile(id: number): void {
		const profile = this.profiles.find((profile: IControllerPinConfig) => profile.id === id);
		if (profile === undefined) {
			return;
		}
		const name = profile.name;
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

	/**
	 * Emites pin config update event
	 * @param {number} id Config profile ID
	 */
	private setProfile(id: number): void {
		const profile = this.profiles.find((profile: IControllerPinConfig) => profile.id === id);
		if (profile !== undefined) {
			this.$emit('update-pin-config', profile);
		}
	}
}
</script>
