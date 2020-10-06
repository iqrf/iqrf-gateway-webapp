<template>
	<router-link
		v-if='device.hasLink()'
		:to='"/iqrfnet/enumeration/" + device.address'
	>
		<CIcon :class='device.getIconColor()' :content='device.getIcon()' />
	</router-link>
	<CIcon v-else :class='device.getIconColor()' :content='device.getIcon()' />
</template>

<script lang='ts'>
import Vue from 'vue';
import Device from '../../helpers/Device';
import {CIcon} from '@coreui/vue/src';

export default Vue.extend({
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
});
</script>
