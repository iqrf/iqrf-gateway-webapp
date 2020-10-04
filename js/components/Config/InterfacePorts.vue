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

<script lang='ts'>
import Vue from 'vue';
import {CButton, CButtonGroup} from '@coreui/vue/src';
import IqrfService from '../../services/IqrfService';

export default Vue.extend({
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
	data(): any {
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
		setPort(port: string) {
			this.$emit('updatePort', port);
		},
	},
});
</script>
