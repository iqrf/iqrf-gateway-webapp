<!--
Copyright 2017-2024 IQRF Tech s.r.o.
Copyright 2019-2024 MICRORISC s.r.o.

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
		<v-text-field
			v-model='connection.wifi.security.leap.username'
			:label='$t("network.wireless.form.username").toString()'
		/>
		<PasswordInput
			v-model='connection.wifi.security.leap.password'
			:label='$t("network.wireless.form.password").toString()'
		/>
	</div>
</template>

<script lang='ts'>
import {Component, VModel, Vue} from 'vue-property-decorator';

import PasswordInput from '@/components/Core/PasswordInput.vue';
import {
	NetworkConnectionConfiguration
} from '@iqrf/iqrf-gateway-webapp-client/types/Network/NetworkConnection';


/**
 * LEAP configuration options
 */
@Component({
	components: {
		PasswordInput,
	},
})
export default class LeapConfiguration extends Vue {

	/**
	 * Edited connection.
	 */
	@VModel({required: true}) connection!: NetworkConnectionConfiguration;

}
</script>
