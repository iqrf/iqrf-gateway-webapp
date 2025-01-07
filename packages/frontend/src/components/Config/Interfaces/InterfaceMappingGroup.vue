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
		<v-data-table
			:loading='loading'
			:items='_mappings'
			:headers='headers'
			:no-data-text='$t("config.daemon.interfaces.interfaceMapping.noMappings")'
		>
			<template #top>
				<v-toolbar dense flat>
					<v-spacer />
					<v-btn
						class='mr-1'
						color='success'
						small
						@click='mappingFormModel = defaultMapping'
					>
						<v-icon small>
							mdi-plus
						</v-icon>
					</v-btn>
					<v-btn
						color='primary'
						small
						@click='refreshMappings'
					>
						<v-icon small>
							mdi-refresh
						</v-icon>
					</v-btn>
				</v-toolbar>
			</template>
			<template #[`item.actions`]='{item}'>
				<v-btn
					class='mr-1'
					color='primary'
					small
					@click='setMapping(item.id)'
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
					@click='mappingFormModel = item'
				>
					<v-icon small>
						mdi-pencil
					</v-icon>
					{{ $t('table.actions.edit') }}
				</v-btn>
				<v-btn
					color='error'
					small
					@click='mappingDeleteModel = item'
				>
					<v-icon small>
						mdi-delete
					</v-icon>
					{{ $t('table.actions.delete') }}
				</v-btn>
			</template>
		</v-data-table>
		<MappingDeleteModal
			v-model='mappingDeleteModel'
			@deleted='refreshMappings'
		/>
		<MappingFormModal
			v-model='mappingFormModel'
			@update-mappings='refreshMappings'
		/>
	</div>
</template>

<script lang='ts'>
import {
	IqrfGatewayDaemonMapping, MappingDeviceType, MappingType
} from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import {Component, Prop, PropSync, Vue} from 'vue-property-decorator';
import {DataTableHeader} from 'vuetify';

import MappingDeleteModal from '@/components/Config/Interfaces/MappingDeleteModal.vue';
import MappingFormModal from '@/components/Config/Interfaces/MappingFormModal.vue';

@Component({
	components: {
		MappingDeleteModal,
		MappingFormModal,
	},
})

/**
 * Interface mapping group component
 */
export default class InterfaceMappingGroup extends Vue {
	/**
	 * @property {Array<IqrfGatewayDaemonMapping>} _mappings Mapping profiles
	 */
	@PropSync('mappings', {type: Array, default: []}) _mappings!: Array<IqrfGatewayDaemonMapping>;

	/**
	 * @property {boolean} loading Data table loading status
	 */
	@Prop({required: false, default: false}) loading!: boolean;

	/**
	 * @var {IqrfGatewayDaemonMapping|null} mappingFormModel Form mapping model
	 */
	private mappingFormModel: IqrfGatewayDaemonMapping|null = null;

	/**
	 * @var {IqrfGatewayDaemonMapping|null} mappingDeleteModel Mapping to delete
	 */
	private mappingDeleteModel: IqrfGatewayDaemonMapping|null = null;

	/**
	 * @constant {IqrfGatewayDaemonMapping} defaultMapping Default mapping values
	 */
	private readonly defaultMapping: IqrfGatewayDaemonMapping = {
		name: '',
		type: MappingType.SPI,
		deviceType: MappingDeviceType.Adapter,
		IqrfInterface: '',
		powerEnableGpioPin: 0,
		busEnableGpioPin: 0,
		pgmSwitchGpioPin: 0,
		i2cEnableGpioPin: 0,
		spiEnableGpioPin: 0,
		uartEnableGpioPin: 0,
		baudRate: 57600
	};

	/**
	 * @constant {Array<DataTableHeader>} header Data table headers
	 */
	private readonly headers: Array<DataTableHeader> = [
		{
			value: 'name',
			text: this.$t('config.daemon.interfaces.interfaceMapping.form.name').toString()
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
