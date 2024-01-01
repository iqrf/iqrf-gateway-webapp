<!--
Copyright 2017-2024 IQRF Tech s.r.o.
Copyright 2019-2024 MICRORISC s.r.o.

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
		<h5>{{ $t('config.controller.pins.profiles') }}</h5>
		<v-dialog
			v-model='show'
			width='50%'
			persistent
			no-click-animation
		>
			<template #activator='{attrs, on}'>
				<v-btn
					color='primary'
					v-bind='attrs'
					v-on='on'
					@click='openModal'
				>
					{{ $t('config.controller.pins.browse') }}
				</v-btn>
			</template>
			<v-card>
				<v-card-title>
					{{ $t('config.controller.pins.profiles') }}
				</v-card-title>
				<v-card-text>
					<v-tabs v-model='activeTab' :show-arrows='true'>
						<v-tab>{{ $t('config.controller.pins.adapters') }}</v-tab>
						<v-tab>{{ $t('config.controller.pins.boards') }}</v-tab>
					</v-tabs>
					<v-divider />
					<v-tabs-items v-model='activeTab'>
						<v-tab-item :transition='false'>
							<ControllerPinConfigGroup
								:profiles='adapterProfiles'
								:loading='loading'
								@refresh-profiles='listConfigs'
								@set-profile='setProfile'
							/>
						</v-tab-item>
						<v-tab-item :transition='false'>
							<ControllerPinConfigGroup
								:profiles='boardProfiles'
								:loading='loading'
								@refresh-profiles='listConfigs'
								@set-profile='setProfile'
							/>
						</v-tab-item>
					</v-tabs-items>
				</v-card-text>
				<v-card-actions>
					<v-spacer />
					<v-btn
						@click='closeModal'
					>
						{{ $t('forms.close') }}
					</v-btn>
				</v-card-actions>
			</v-card>
		</v-dialog>
	</div>
</template>

<script lang='ts'>
import {
	IqrfGatewayControllerMapping,
	IqrfGatewayControllerMappingDevice
} from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import {AxiosError} from 'axios';
import {Component} from 'vue-property-decorator';

import ControllerPinConfigGroup from '@/components/Config/Controller/ControllerPinConfigGroup.vue';
import ModalBase from '@/components/ModalBase.vue';
import {extendedErrorToast} from '@/helpers/errorToast';
import {useApiClient} from '@/services/ApiClient';

@Component({
	components: {
		ControllerPinConfigGroup,
	},
})

/**
 * Controller pin configurations component
 */
export default class ControllerPinConfigs extends ModalBase {
	/**
	 * @var {boolean} loading Indicates data is loading
	 */
	private loading = false;

	/**
	 * @var {number} activeTab Currently selected tab
	 */
	private activeTab = 0;

	/**
	 * @var {IqrfGatewayControllerMapping[]} profiles Controller pin configuration profiles
	 */
	private profiles: IqrfGatewayControllerMapping[] = [];

	/**
	 * Computes adapter controller profile options
	 * @return {Array<IControllerPinConfig>} Adapter controller profile options
	 */
	get adapterProfiles(): Array<IqrfGatewayControllerMapping> {
		return this.profiles.filter((profile: IqrfGatewayControllerMapping): boolean => profile.deviceType === IqrfGatewayControllerMappingDevice.Adapter);
	}

	/**
	 * Computes board controller profile options
	 * @return {Array<IqrfGatewayControllerMapping>} Board controller profile options
	 */
	get boardProfiles(): Array<IqrfGatewayControllerMapping> {
		return this.profiles.filter((profile: IqrfGatewayControllerMapping): boolean => profile.deviceType === IqrfGatewayControllerMappingDevice.Board);
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
		this.loading = true;
		return useApiClient().getConfigServices().getIqrfGatewayControllerService().listMappings()
			.then((rsp: IqrfGatewayControllerMapping[]) => {
				this.profiles = rsp;
				this.loading = false;
			})
			.catch((err: AxiosError) => {
				this.loading = false;
				extendedErrorToast(err, 'config.controller.pins.messages.listFailed');
			});
	}

	/**
	 * Emites pin config update event
	 * @param {number} id Config profile ID
	 */
	private setProfile(id: number): void {
		const profile = this.profiles.find((profile: IqrfGatewayControllerMapping) => profile.id === id);
		if (profile !== undefined) {
			this.$emit('update-pin-config', profile);
		}
		this.closeModal();
	}
}
</script>
