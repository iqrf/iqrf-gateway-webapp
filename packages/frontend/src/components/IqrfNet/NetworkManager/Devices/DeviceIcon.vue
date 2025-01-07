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
	<router-link
		v-if='device.hasLink()'
		:to='"/iqrfnet/enumeration/" + device.address'
	>
		<v-icon :color='device.getIconColor()'>
			{{ device.getIcon() }}
		</v-icon>
	</router-link>
	<v-icon v-else :color='device.getIconColor()'>
		{{ device.getIcon() }}
	</v-icon>
</template>

<script lang='ts'>
import {Component, Prop, Vue} from 'vue-property-decorator';

import Device from '@/helpers/Device';



/**
 * DeviceIcon component used in table of devices
 */
@Component
export default class DeviceIcon extends Vue {
	/**
	 * @property {Device|StandardDevice} device Network device
	 */
	@Prop({required: true}) device!: Device;
}
</script>
