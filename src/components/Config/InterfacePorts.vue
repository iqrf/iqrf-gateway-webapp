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
import {Component, Prop, Vue} from 'vue-property-decorator';
import {CButton, CButtonGroup} from '@coreui/vue/src';
import IqrfService from '../../services/IqrfService';

@Component({
	components: {
		CButton,
		CButtonGroup,
	},
})

export default class InterfacePorts extends Vue {
	private ports: Array<string> = []
	
	@Prop({ required: true })
	interfaceType!: string;

	created(): void {
		IqrfService.getInterfacePorts(this.interfaceType)
			.then((ports: Array<string>) => (this.ports = ports))
			.catch(() => (this.ports = []));
	}

	private setPort(port: string): void {
		this.$emit('update-port', port);
	}
}
</script>
