<template>
	<div>
		<h4>{{ $t('config.interfaceMapping.boards') }}</h4>
		<CButtonGroup>
			<CButton
				v-for='board in Object.keys(mappings[interfaceType])'
				:key='board'
				color='primary'
				@click='setMapping(board)'
			>
				{{ board }}
			</CButton>
		</CButtonGroup>
	</div>
</template>

<script lang='ts'>
import Vue from 'vue';
import {CButton, CButtonGroup} from '@coreui/vue/src';
import {IInterfaceMappings} from '../../interfaces/mappings';

export default Vue.extend({
	name: 'InterfaceMappings',
	components: {
		CButton,
		CButtonGroup,
	},
	props: {
		interfaceType: {
			type: String,
			required: true,
		},
	},
	data(): IInterfaceMappings {
		return {
			mappings: {
				'spi': require('../../../app/ConfigModule/json/SpiPins.json'),
				'uart': require('../../../app/ConfigModule/json/UartPins.json'),
			},
		};
	},
	methods: {
		setMapping(board: string): void {
			const mapping = this.mappings[this.interfaceType][board];
			this.$emit('update-mapping', mapping);
		},
	},
});
</script>
