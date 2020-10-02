<template>
	<CCard>
		<CCardHeader>{{ $t('iqrfnet.trConfiguration.security.title') }}</CCardHeader>
		<CCardBody>
			<ValidationObserver v-slot='{ invalid }'>
				<CForm>
					<CSelect
						v-model='format'
						:options='selectOptions'
						:label='$t("iqrfnet.trConfiguration.security.form.format")'
					/>
					<ValidationProvider
						v-slot='{ errors, touched, valid }'
						:rules='rules.rule'
					>
						<CInput
							v-model='password'
							:label='$t("iqrfnet.trConfiguration.security.form.password")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='errors[0]'
						/>
					</ValidationProvider>
					<CButton
						color='primary'
						:disabled='invalid'
						@click='save(true)'
					>
						{{ $t("iqrfnet.trConfiguration.security.form.setPassword") }}
					</CButton>
					<CButton
						color='primary'
						:disabled='invalid'
						@click='save(false)'
					>
						{{ $t("iqrfnet.trConfiguration.security.form.setKey") }}
					</CButton>
				</CForm>
			</ValidationObserver>
		</CCardBody>
	</CCard>
</template>

<script>
import {CButton, CCard, CCardBody, CCardHeader, CForm, CInput, CSelect} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {regex} from 'vee-validate/dist/rules';
import SecurityService from '../../services/SecurityService';
import {SecurityFormat} from '../../iqrfNet/securityFormat';

export default {
	name: 'SecurityForm',
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CForm,
		CInput,
		CSelect,
		ValidationObserver,
		ValidationProvider
	},
	props: {
		address: {
			type: Number,
			required: true,
		},
	},
	data() {
		return {
			selectOptions: [
				{value: SecurityFormat.ASCII, label: this.$t('iqrfnet.trConfiguration.security.form.ascii')},
				{value: SecurityFormat.HEX, label: this.$t('iqrfnet.trConfiguration.security.form.hex')}
			],
			format: SecurityFormat.ASCII,
			password: '',

		};
	},
	computed: {
		rules() {
			if (this.format === SecurityFormat.ASCII) {
				return {rule: 'regex:/^[\x00-\x7F]{0,16}$/'};
			} else {
				return {rule: 'regex:/^[0-9a-fA-F]{0,32}$/'};
			}
		}
	},
	created() {
		extend('regex', regex);
		this.unsubscribe = this.$store.subscribe(mutation => {
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
	beforeDestroy() {
		this.unsubscribe();
	},
	methods: {
		save(password) {
			this.$store.commit('spinner/SHOW');
			if (password) {
				SecurityService.setSecurity(this.address, this.password, this.format, 0);
			} else {
				SecurityService.setSecurity(this.address, this.password, this.format, 1);
			}
		}
	},
};
</script>
