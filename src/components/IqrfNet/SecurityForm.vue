<template>
	<CCard>
		<CCardHeader>{{ $t('iqrfnet.trConfiguration.security.title') }}</CCardHeader>
		<CCardBody>
			<CForm>
				<CSelect
					:value.sync='format'
					:options='selectOptions'
					:label='$t("iqrfnet.trConfiguration.security.form.format")'
				/>
				<CInput
					v-model='password'
					:label='$t("iqrfnet.trConfiguration.security.form.password")'
				/>
				<CButton
					color='primary'
					@click='save(true)'
				>
					{{ $t("iqrfnet.trConfiguration.security.form.setPassword") }}
				</CButton>
				<CButton
					color='primary'
					@click='save(false)'
				>
					{{ $t("iqrfnet.trConfiguration.security.form.setKey") }}
				</CButton>
			</CForm>
		</CCardBody>
	</CCard>
</template>

<script lang='ts'>
import Vue from 'vue';
import {MutationPayload} from 'vuex';
import {CButton, CCard, CCardBody, CCardHeader, CForm, CInput, CSelect} from '@coreui/vue/src';
import SecurityService from '../../services/SecurityService';
import {SecurityFormat} from '../../iqrfNet/securityFormat';

export default Vue.extend({
	name: 'SecurityForm',
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CForm,
		CInput,
		CSelect,
	},
	props: {
		address: {
			type: Number,
			required: true,
		},
	},
	data(): any {
		return {
			selectOptions: [
				{
					value: SecurityFormat.ASCII.valueOf(),
					label: this.$t('iqrfnet.trConfiguration.security.form.ascii').toString(),
				},
				{
					value: SecurityFormat.HEX.valueOf(),
					label: this.$t('iqrfnet.trConfiguration.security.form.hex').toString(),
				}
			],
			format: SecurityFormat.ASCII.valueOf(),
			password: '',
		};
	},
	created(): void {
		this.unsubscribe = this.$store.subscribe((mutation: MutationPayload) => {
			if (mutation.type !== 'SOCKET_ONMESSAGE') {
				return;
			}
			if (mutation.payload.mType === 'iqrfEmbedOs_SetSecurity') {
				if (mutation.payload.data.status === 0) {
					this.$store.commit('spinner/HIDE');
					this.$toast.success(this.$t('iqrfnet.trConfiguration.security.messages.success').toString());
				} else {
					this.$toast.error(this.$t('iqrfnet.trConfiguration.security.messages.failure').toString());
				}
			}
		});
	},
	beforeDestroy(): void {
		this.unsubscribe();
	},
	methods: {
		save(password: boolean): void {
			let pattern = '';
			if (this.format === SecurityFormat.ASCII) {
				pattern = '^[ -~]{0,16}$';
			} else {
				pattern = '^[a-fA-F0-9]{0,32}$';
			}
			const regex = RegExp(pattern);
			if (!regex.test(this.password)) {
				this.$toast.error('Password string is not valid for the selected format.');
				return;
			}
			this.$store.commit('spinner/SHOW');
			if (password) {
				SecurityService.setSecurity(this.address, this.password, this.format, 0);
			} else {
				SecurityService.setSecurity(this.address, this.password, this.format, 1);
			}
		}
	},
});
</script>
