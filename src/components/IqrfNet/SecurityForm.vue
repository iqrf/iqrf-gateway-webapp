<template>
	<CCard>
		<CCardHeader>{{ $t('iqrfnet.trConfiguration.security.title') }}</CCardHeader>
		<CCardBody>
			<ValidationObserver v-slot='{ invalid }'>
				<CForm>
					<CSelect
						:value.sync='format'
						:options='selectOptions'
						:label='$t("iqrfnet.trConfiguration.security.form.format")'
					/>
					<ValidationProvider
						v-slot='{ valid, touched, errors }'
						:rules='{regex: validationPattern}'
						:custom-messages='{regex: "iqrfnet.trConfiguration.security.messages.invalid"}'
					>
						<CInput
							v-model='password'
							:label='$t("forms.fields.password")'
							:type='visibility'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
						>
							<template #append-content>
								<span @click='changeVisibility'>
									<FontAwesomeIcon
										:icon='(visibility === "password" ? ["far", "eye"] : ["far", "eye-slash"])'
									/>
								</span>
							</template>
						</CInput>
					</ValidationProvider>
					<CButton
						color='primary'
						:disabled='invalid'
						@click='save(true)'
					>
						{{ $t("iqrfnet.trConfiguration.security.form.setPassword") }}
					</CButton> <CButton
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

<script lang='ts'>
import {Component, Prop, Vue} from 'vue-property-decorator';
import {MutationPayload} from 'vuex';
import {CButton, CCard, CCardBody, CCardHeader, CForm, CInput, CSelect} from '@coreui/vue/src';
import {FontAwesomeIcon} from '@fortawesome/vue-fontawesome';
import SecurityService from '../../services/SecurityService';
import OsService from '../../services/DaemonApi/OsService';
import {SecurityFormat} from '../../iqrfNet/securityFormat';
import {IOption} from '../../interfaces/coreui';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {regex} from 'vee-validate/dist/rules';

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CForm,
		CInput,
		CSelect,
		FontAwesomeIcon,
		ValidationObserver,
		ValidationProvider,
	}
})

/**
 * Security card for TrConfiguration component
 */
export default class SecurityForm extends Vue {
	/**
	 * @var {SecurityFormat} format Format of password or user key
	 */
	private format: SecurityFormat = SecurityFormat.ASCII.valueOf()

	/**
	 * @var {string|null} msgId Daemon api message id
	 */
	private msgId: string|null = null

	/**
	 * @var {string} password Password to set
	 */
	private password = ''

	/**
	 * @constant {Array<IOption>} selectOptions CoreUI form select option
	 */
	private selectOptions: Array<IOption> = [
		{
			value: SecurityFormat.ASCII.valueOf(),
			label: this.$t('iqrfnet.trConfiguration.security.form.ascii').toString(),
		},
		{
			value: SecurityFormat.HEX.valueOf(),
			label: this.$t('iqrfnet.trConfiguration.security.form.hex').toString(),
		}
	]

	/**
	 * @var {string} visibility Form password field visibility type
	 */
	private visibility = 'password'

	/**
	 * Component unsubscribe function
	 */
	private unsubscribe: CallableFunction = () => {return;}

	/**
	 * @property {number} address Address of device
	 */
	@Prop({required: true}) address!: number

	/**
	 * Returns regex pattern for validation
	 */
	get validationPattern(): string {
		if (this.format === SecurityFormat.ASCII) {
			return '^[ -~]{0,16}$';
		} else {
			return '^[a-fA-F0-9]{0,32}$';
		}
	}

	/**
	 * Vue lifecycle hook created
	 */
	created(): void {
		extend('regex', regex);
		this.unsubscribe = this.$store.subscribe((mutation: MutationPayload) => {
			if (mutation.type !== 'SOCKET_ONMESSAGE') {
				return;
			}
			if (mutation.payload.data.msgId !== this.msgId) {
				return;
			}
			this.$store.dispatch('spinner/hide');
			this.$store.dispatch('removeMessage', this.msgId);
			if (mutation.payload.mType === 'iqrfEmbedOs_SetSecurity') {
				this.handleSecurityResponse(mutation.payload);
			} else if (mutation.payload.mType === 'iqrfEmbedOs_Reset') {
				this.handleResetResponse(mutation.payload);
			}
		});
	}

	/**
	 * Vue lifecycle hook beforeDestroy
	 */
	beforeDestroy(): void {
		this.$store.dispatch('removeMessage', this.msgId);
		this.unsubscribe();
	}

	/**
	 * Saves new security setting
	 * @param {boolean} password Is the new security setting a password?
	 */
	private save(password: boolean): void {
		const regex = RegExp(this.validationPattern);
		if (!regex.test(this.password)) {
			this.$toast.error(this.$t('iqrfnet.trConfiguration.security.messages.invalid'));
			return;
		}
		this.$store.dispatch('spinner/show', {timeout: 15000});
		const type = password ? 0 : 1;
		SecurityService.setSecurity(this.address, this.password, this.format, type, 15000, 'iqrfnet.trConfiguration.security.messages.failure', () => this.msgId = null)
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Handles EmbedOs_SetSecurity request response
	 */
	private handleSecurityResponse(response): void {
		if (response.data.status === 0) {
			this.resetDevice();
		} else {
			this.$toast.error(
				this.$t('iqrfnet.trConfiguration.security.messages.failure').toString()
			);
		}
	}

	/**
	 * Performs device reset
	 */
	private resetDevice(): void {
		this.$store.dispatch('spinner/show', {timeout: 30000});
		OsService.reset(this.address, 30000, 'iqrfnet.trConfiguration.security.messages.resetFailure', () => this.msgId = null)
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Handles EmbedOs_Reset request response
	 */
	private handleResetResponse(response): void {
		if (response.data.status === 0) {
			this.$toast.success(
				this.$t('iqrfnet.trConfiguration.security.messages.success').toString()
			);
		} else {
			this.$toast.error(
				this.$t('iqrfnet.trConfiguration.security.messages.resetFailure').toString()
			);
		}
	}

	/**
	 * Changes password input field visibility
	 */
	private changeVisibility(): void {
		this.visibility = this.visibility === 'password' ? 'text': 'password';
	}
}
</script>
