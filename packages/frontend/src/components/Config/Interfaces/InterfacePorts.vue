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
		<h5>{{ $t('config.daemon.interfaces.interfaceMapping.interfaces') }}</h5>
		<v-item-group
			v-if='ports.length > 0'
			dense
			class='flex-wrap'
		>
			<v-btn
				v-for='port of ports'
				:key='port'
				small
				color='primary'
				@click='setPort(port)'
			>
				{{ port }}
			</v-btn>
		</v-item-group>
		<v-alert
			v-else
			color='warning'
			text
		>
			{{ $t('config.daemon.interfaces.interfaceMapping.noInterfaces') }}
		</v-alert>
	</div>
</template>

<script lang='ts'>
import {Component, Prop, Vue} from 'vue-property-decorator';
import IqrfService from '@/services/IqrfService';

/**
 * Interface port mapping
 */
@Component
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

<style lang="scss" scoped>
.v-item-group {
	.v-btn {
		border-radius: 0%;
	}

	.v-btn:first-child {
		border-top-left-radius: 4px;
		border-bottom-left-radius: 4px;
	}

	.v-btn:last-child {
		border-top-right-radius: 4px;
		border-bottom-right-radius: 4px;
	}
}
</style>
