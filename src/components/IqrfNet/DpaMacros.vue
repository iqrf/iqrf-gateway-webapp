<!--
Copyright 2017-2022 IQRF Tech s.r.o.
Copyright 2019-2022 MICRORISC s.r.o.

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
	<CCard v-if='macros'>
		<CCardHeader>
			{{ $t('iqrfnet.sendPacket.macros') }}
		</CCardHeader>
		<CCardBody>
			<CButtonGroup class='flex-wrap'>
				<CDropdown
					v-for='group of macros'
					:key='group.id'
					:toggler-text='group.name'
					color='primary'
					placement='top-start'
				>
					<CDropdownItem
						v-for='packet of group.macros'
						:key='packet.name'
						@click='$emit("set-packet", packet.request)'
					>
						{{ packet.name }}
					</CDropdownItem>
				</CDropdown>
			</CButtonGroup>
		</CCardBody>
	</CCard>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {AxiosResponse} from 'axios';
import {CButtonGroup, CCard, CCardBody, CCardHeader, CDropdown, CDropdownItem} from '@coreui/vue/src';
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

@Component({
	components: {
		CButtonGroup,
		CCard,
		CCardBody,
		CCardHeader,
		CDropdown,
		CDropdownItem,
	}
})

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
