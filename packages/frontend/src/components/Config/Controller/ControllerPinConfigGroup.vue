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
		<v-data-table
			:loading='loading'
			:items='_profiles'
			:headers='headers'
			:no-data-text='$t("config.controller.pins.noProfiles")'
		>
			<template #top>
				<v-toolbar dense flat>
					<v-spacer />
					<v-btn
						class='mr-1'
						color='success'
						small
						@click='profileFormModel = defaultProfile'
					>
						<v-icon small>
							mdi-plus
						</v-icon>
					</v-btn>
					<v-btn
						color='primary'
						small
						@click='refreshProfiles'
					>
						<v-icon small>
							mdi-refresh
						</v-icon>
					</v-btn>
				</v-toolbar>
			</template>
			<template #[`item.name`]='{item}'>
				{{ item.name }}
			</template>
			<template #[`item.actions`]='{item}'>
				<v-btn
					class='mr-1'
					color='primary'
					small
					@click='setProfile(item.id)'
				>
					<v-icon small>
						mdi-content-copy
					</v-icon>
					{{ $t('forms.set') }}
				</v-btn>
				<v-btn
					class='mr-1'
					color='info'
					small
					@click='profileFormModel = item'
				>
					<v-icon small>
						mdi-pencil
					</v-icon>
					{{ $t('table.actions.edit') }}
				</v-btn>
				<v-btn
					color='error'
					small
					@click='profileDeleteModel = item'
				>
					<v-icon small>
						mdi-delete
					</v-icon>
					{{ $t('table.actions.delete') }}
				</v-btn>
			</template>
		</v-data-table>
		<ControllerPinConfigDeleteModal
			v-model='profileDeleteModel'
			@deleted='refreshProfiles'
		/>
		<ControllerPinConfigFormModal
			v-model='profileFormModel'
			@update-profiles='refreshProfiles'
		/>
	</div>
</template>

<script lang='ts'>
import {
	IqrfGatewayControllerMapping, MappingDeviceType
} from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import {Component, Prop, PropSync, Vue} from 'vue-property-decorator';
import {DataTableHeader} from 'vuetify';

import ControllerPinConfigDeleteModal from './ControllerPinConfigDeleteModal.vue';
import ControllerPinConfigFormModal from './ControllerPinConfigFormModal.vue';

@Component({
	components: {
		ControllerPinConfigDeleteModal,
		ControllerPinConfigFormModal,
	},
})

/**
 * Controller pin configuration profile group
 */
export default class ControllerPinConfigGroup extends Vue {
	/**
	 * @property {Array<IqrfGatewayControllerMapping>} _profiles Controller config profiles
	 */
	@PropSync('profiles', {type: Array, default: []}) _profiles!: Array<IqrfGatewayControllerMapping>;

	/**
	 * @property {boolean} loading Data table loading status
	 */
	@Prop({required: false, default: false}) loading!: boolean;

	/**
	 * @var {IqrfGatewayControllerMapping|null} profileFormModel Form profile model
	 */
	private profileFormModel: IqrfGatewayControllerMapping|null = null;

	/**
	 * @var {IqrfGatewayControllerMapping|null} profileDeleteModel Profile to delete
	 */
	private profileDeleteModel: IqrfGatewayControllerMapping|null = null;

	/**
	 * @constant {IqrfGatewayControllerMapping} defaultProfile Default profile values
	 */
	private readonly defaultProfile: IqrfGatewayControllerMapping = {
		name: '',
		deviceType: MappingDeviceType.Adapter,
		greenLed: 0,
		redLed: 0,
		button: 0,
		sck: 0,
		sda: 0,
	};

	/**
	 * @constant {Array<DataTableHeader>} header Data table headers
	 */
	private readonly headers: Array<DataTableHeader> = [
		{
			value: 'name',
			text: this.$t('config.controller.pins.form.name').toString(),
		},
		{
			value: 'actions',
			text: this.$t('table.actions.title').toString(),
			filterable: false,
			sortable: false,
			align: 'end',
		},
	];

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
