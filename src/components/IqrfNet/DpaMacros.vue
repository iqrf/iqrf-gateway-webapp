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
import {CButtonGroup, CCard, CCardBody, CCardHeader, CDropdown, CDropdownItem} from '@coreui/vue/src';

import IqrfService, {DpaMacro, DpaMacroGroup} from '@/services/IqrfService';

/**
 * Raw DPA message macros for SendDpaPacket component
 */
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
