<template>
	<div v-if='ports !== []'>
		<h4>{{ $t('config.interfaceMapping.interfaces') }}</h4>
		<CButtonGroup>
			<CButton
				v-for='port of ports'
				:key='port'
				color='primary'
				@click='setPort(port)'
			>
				{{ port }}
			</CButton>
		</CButtonGroup>
	</div>
</template>

<script>
import {CButton, CButtonGroup} from '@coreui/vue/src';
import IqrfService from '../../services/IqrfService';

export default {
	name: 'InterfacePorts',
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
			ports: [],
		};
	},
	created() {
		IqrfService.getInterfacePorts(this.interfaceType)
			.then((ports) => (this.ports = ports))
			.catch(() => (this.ports = []));
	},
	methods: {
		setPort(port) {
			this.$emit('updatePort', port);
			switch (this.interfaceType) {
				case 'spi':
					document.getElementById('frm-configIqrfSpiForm-IqrfInterface').value = port;
					break;
				case 'uart':
					document.getElementById('frm-configIqrfUartForm-IqrfInterface').value = port;
					break;
			}
		},
	},
};
</script>
