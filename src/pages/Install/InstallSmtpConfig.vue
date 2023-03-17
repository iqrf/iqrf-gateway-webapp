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
