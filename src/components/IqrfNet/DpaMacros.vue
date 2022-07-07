<!--
Copyright 2017-2021 IQRF Tech s.r.o.
Copyright 2019-2021 MICRORISC s.r.o.

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
	<v-card v-if='macros'>
		<v-card-title>{{ $t('iqrfnet.sendPacket.macros') }}</v-card-title>
		<v-card-text>
			<v-btn-toggle class='flex-wrap'>
				<v-menu v-for='group of macros' :key='group.name'>
					<template #activator='{on, attrs}'>
						<v-btn
							v-bind='attrs'
							color='primary'
							v-on='on'
						>
							{{ group.name }}
							<v-icon color='white'>
								mdi-menu-down
							</v-icon>
						</v-btn>
					</template>
					<v-list dense>
						<v-list-item
							v-for='packet of group.macros'
							:key='packet.name'
							dense
							@click='$emit("set-packet", packet.request)'
						>
							{{ packet.name }}
						</v-list-item>
					</v-list>
				</v-menu>
			</v-btn-toggle>
		</v-card-text>
	</v-card>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {AxiosResponse} from 'axios';
import IqrfService from '@/services/IqrfService';

interface IDpaMacro {
	confirmation: boolean
	enabled: boolean
	name: string
	note: string
	request: string
}

interface IDpaMacros {
	enabled: boolean
	id: number
	macros: Array<IDpaMacro>
	name: string
}

@Component({})

/**
 * Raw DPA message macros for SendDpaPacket component
 */
export default class DpaMacros extends Vue {
	/**
	 * @var {Array<IDpaMacros>} macros Array of raw DPA message macros
	 */
	private macros: Array<IDpaMacros> = [];

	/**
	 * Vue lifecycle hook created
	 * Retrieves raw DPA message macros
	 */
	created(): void {
		IqrfService.getMacros()
			.then((response: AxiosResponse) => {
				this.macros = response.data.filter((group: IDpaMacros) => {
					if (!group.enabled) {
						return null;
					}
					group.macros = group.macros.filter((packet: IDpaMacro) => {
						if (packet.enabled) {
							return packet;
						}
					});
					return group;
				});
			});
	}
}
</script>
