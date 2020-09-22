<template>
	<router-link v-if='device.hasLink()' :to='"/iqrfnet/enumeration/" + device.address'>
		<CIcon :class='device.getIconColor()' :content='device.getIcon()' />
	</router-link>
	<CIcon v-else :class='device.getIconColor()' :content='device.getIcon()' />
</template>

<script>
import Device from '../../helpers/Device';
import {CIcon} from '@coreui/vue/src';

export default {
	name: 'DeviceIcon',
	components: {
		CIcon
	},
	props: {
		device: {
			type: Device,
			required: true,
		},
	},
};
</script>
