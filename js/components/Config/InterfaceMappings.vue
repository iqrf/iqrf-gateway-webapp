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

<script>
import {CButton, CButtonGroup} from '@coreui/vue';

export default {
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
	data() {
		return {
			mappings: {
				'spi': require('../../../app/ConfigModule/json/SpiPins.json'),
				'uart': require('../../../app/ConfigModule/json/UartPins.json'),
			},
		};
	},
	methods: {
		setMapping(board) {
			const mapping = this.mappings[this.interfaceType][board];
			this.$emit('updateMapping', mapping);
			switch (this.interfaceType) {
				case 'uart':
					document.getElementById('frm-configIqrfUartForm-IqrfInterface').value = mapping.IqrfInterface;
					document.getElementById('frm-configIqrfUartForm-baudRate').value = mapping.baudRate;
					document.getElementById('frm-configIqrfUartForm-powerEnableGpioPin').value = mapping.powerEnableGpioPin;
					document.getElementById('frm-configIqrfUartForm-busEnableGpioPin').value = mapping.busEnableGpioPin;
					break;
			}
		},
	},
};
</script>
