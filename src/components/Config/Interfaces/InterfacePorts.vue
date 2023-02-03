<!--
Copyright 2017-2023 IQRF Tech s.r.o.
Copyright 2019-2023 MICRORISC s.r.o.

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
	<div v-if='ports.length > 0'>
		<CTabs :active-tab='0'>
			<CTab :title='$t("config.daemon.interfaces.interfaceMapping.interfaces")'>
				<CButtonGroup class='my-1 flex-wrap'>
					<CButton
						v-for='port of ports'
						:key='port'
						color='primary'
						@click='setPort(port)'
					>
						{{ port }}
					</CButton>
				</CButtonGroup>
			</CTab>
		</CTabs>
	</div>
</template>

<script lang='ts'>
import {Component, Prop, Vue} from 'vue-property-decorator';
import {CButton, CButtonGroup} from '@coreui/vue/src';
import IqrfService from '@/services/IqrfService';

@Component({
	components: {
		CButton,
		CButtonGroup,
	},
})

/**
 * Interface port mapping
 */
export default class InterfacePorts extends Vue {
	/**
	 * @var {Array<string>} ports device ports
	 */
	private ports: Array<string> = [];

	/**
	 * @property {string} interfaceType communication interface type
	 */
	@Prop({ required: true }) interfaceType!: string;

	/**
	 * Vue lifecycle hook created
	 */
	created(): void {
		IqrfService.getInterfacePorts(this.interfaceType)
			.then((ports: Array<string>) => (this.ports = ports))
			.catch(() => (this.ports = []));
	}

	/**
	 * Emits port to parent component to update port field
	 * @param {string} port port name
	 */
	private setPort(port: string): void {
		this.$emit('update-port', port);
	}
}
</script>
