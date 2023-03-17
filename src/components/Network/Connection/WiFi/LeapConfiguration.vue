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

import {IConnection} from '@/interfaces/Network/Connection';

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
	@VModel({required: true}) connection!: IConnection;

}
</script>
