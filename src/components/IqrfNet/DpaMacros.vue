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
	<v-card v-if='macros'>
		<v-card-title>{{ $t('iqrfnet.sendPacket.macros') }}</v-card-title>
		<v-card-text>
			<v-item-group class='flex-wrap' dense>
				<v-menu
					v-for='group of macros'
					:key='group.name'
					offset-y
					top
				>
					<template #activator='{on, attrs}'>
						<v-btn
							v-bind='attrs'
							color='primary'
							v-on='on'
						>
							{{ group.name }}
							<v-icon>
								mdi-menu-up
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
			</v-item-group>
		</v-card-text>
	</v-card>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import IqrfService, {DpaMacro, DpaMacroGroup} from '@/services/IqrfService';

/**
 * Raw DPA message macros for SendDpaPacket component
 */
@Component
export default class DpaMacros extends Vue {
	/**
	 * @var {Array<IDpaMacros>} macros Array of raw DPA message macros
	 */
	private macros: Array<DpaMacroGroup> = [];

	/**
	 * Vue lifecycle hook created
	 * Retrieves raw DPA message macros
	 */
	created(): void {
		IqrfService.getMacros()
			.then((response: Array<DpaMacroGroup>) => {
				this.macros = response.filter((group: DpaMacroGroup): boolean => {
					if (!group.enabled) {
						return false;
					}
					group.macros = group.macros.filter((packet: DpaMacro): boolean => packet.enabled);
					return true;
				});
			});
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
