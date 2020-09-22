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

<script>
import {CButtonGroup, CCard, CCardBody, CCardHeader, CDropdown, CDropdownItem} from '@coreui/vue/src';
import IqrfService from '../../services/IqrfService';

export default {
	name: 'DpaMacros',
	components: {
		CButtonGroup,
		CCard,
		CCardBody,
		CCardHeader,
		CDropdown,
		CDropdownItem,
	},
	data() {
		return {
			macros: null,
		};
	},
	created() {
		IqrfService.getMacros()
			.then((response) => {
				this.macros = response.data.filter((group) => {
					if (!group.enabled) {
						return null;
					}
					group.macros = group.macros.filter((packet) => {
						if (packet.enabled) {
							return packet;
						}
					});
					return group;
				});
			});
	},
};
</script>
