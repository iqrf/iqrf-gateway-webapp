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
import {Component, Prop, PropSync, Vue} from 'vue-property-decorator';
import MappingDeleteModal from '@/components/Config/Interfaces/MappingDeleteModal.vue';
import MappingFormModal from '@/components/Config/Interfaces/MappingFormModal.vue';

import {ConfigDeviceType, MappingType} from '@/enums/Config/ConfigurationProfiles';

import {DataTableHeader} from 'vuetify';
import {IMapping} from '@/interfaces/Config/Mapping';

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
	 * @property {Array<IMapping>} _mappings Mapping profiles
	 */
	@PropSync('mappings', {type: Array, default: []}) _mappings!: Array<IMapping>;

	/**
	 * @property {boolean} loading Data table loading status
	 */
	@Prop({required: false, default: false}) loading!: boolean;

	/**
	 * @var {IMapping|null} mappingFormModel Form mapping model
	 */
	private mappingFormModel: IMapping|null = null;

	/**
	 * @var {IMapping|null} mappingDeleteModel Mapping to delete
	 */
	private mappingDeleteModel: IMapping|null = null;

	/**
	 * @constant {IMapping} defaultMapping Default mapping values
	 */
	private readonly defaultMapping: IMapping = {
		name: '',
		type: MappingType.SPI,
		deviceType: ConfigDeviceType.ADAPTER,
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
