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
	<v-card>
		<v-card-title>{{ $t('install.steps.smtp') }}</v-card-title>
		<v-card-text>
			<SmtpForm @done='nextStep' />
		</v-card-text>
	</v-card>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import SmtpForm from '@/components/Config/SmtpForm.vue';

@Component({
	components: {
		SmtpForm
	},
	metaInfo: {
		title: 'install.steps.smtp'
	}
})

/**
 * Installation wizard SMTP configuration component
 */
export default class InstallSmtpConfig extends Vue {

	/**
	 * Advances the install wizard
	 */
	private nextStep(): void {
		if (this.$store.getters['features/isEnabled']('gatewayPass')) {
			this.$emit('next-step');
			this.$router.push('/install/gateway-user/');
		} else if (this.$store.getters['features/isEnabled']('ssh')) {
			this.$emit('next-step');
			this.$router.push('/install/ssh-keys/');
		} else {
			this.$router.push('/');
			this.$toast.success(
				this.$t('install.messages.finished').toString()
			);
		}
	}
}
</script>
