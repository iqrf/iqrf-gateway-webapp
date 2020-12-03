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
					:label='$t("forms.fields.password")'
					:type='visibility'
				>
					<template #append-content>
						<span @click='changeVisibility'>
							<CIcon
								:content='(visibility === "password" ? icons.hidden : icons.shown)'
							/>
						</span>
					</template>
				</CInput>
				<CButton
					color='primary'
					@click='save(true)'
				>
					{{ $t("iqrfnet.trConfiguration.security.form.setPassword") }}
				</CButton> <CButton
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
import {Component, Prop, Vue} from 'vue-property-decorator';
import {MutationPayload} from 'vuex';
import {CButton, CCard, CCardBody, CCardHeader, CForm, CInput, CSelect} from '@coreui/vue/src';
import {cilLockLocked, cilLockUnlocked} from '@coreui/icons';
import SecurityService from '../../services/SecurityService';
import ServiceService from '../../services/ServiceService';
import {SecurityFormat} from '../../iqrfNet/securityFormat';
import {IOption} from '../../interfaces/coreui';
import {Dictionary} from 'vue-router/types/router';
import {AxiosError} from 'axios';

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CForm,
		CInput,
		CSelect,
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
	 * @constant {Dictionary<Array<string>>} icons Dictionary of CoreUI icons
	 */
	private icons: Dictionary<Array<string>> = {
		hidden: cilLockLocked,
		shown: cilLockUnlocked
	}


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
	 * Vue lifecycle hook created
	 */
	created(): void {
		this.unsubscribe = this.$store.subscribe((mutation: MutationPayload) => {
			if (mutation.type !== 'SOCKET_ONMESSAGE') {
				return;
			}
			if (mutation.payload.data.msgId === this.msgId) {
				this.$store.dispatch('spinner/hide');
				this.$store.dispatch('removeMessage', this.msgId);
				if (mutation.payload.data.status === 0) {
					this.restartDaemon();
				} else {
					this.$toast.error(this.$t('iqrfnet.trConfiguration.security.messages.failure').toString());
				}
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
		this.$store.dispatch('spinner/show', {timeout: 15000});
		const type = password ? 0 : 1;
		SecurityService.setSecurity(this.address, this.password, this.format, type, 15000, 'iqrfnet.trConfiguration.security.messages.failure', () => this.msgId = null)
			.then((msgId: string) => this.msgId = msgId);
	}

	private restartDaemon(): void {
		this.$store.commit('spinner/SHOW');
		ServiceService.restart('iqrf-gateway-daemon')
			.then(() => {
				this.$store.commit('spinner/HIDE');
				this.$toast.success(this.$t('iqrfnet.trConfiguration.security.messages.success').toString());
			})
			.catch(this.handleError);
	}

	/**
	 * Handles REST API error responses
	 */
	private handleError(error: AxiosError): void {
		this.$store.commit('spinner/HIDE');
		const response = error.response;
		if (response === undefined) {
			this.$toast.error(this.$t('service.errors.processTimeout').toString());
			return;
		}
		if (response.status === 404) {
			this.$toast.error(this.$t('service.errors.missingService').toString());
		}
		if (response.status === 500 && response.data.message === 'Unsupported init system') {
			this.$toast.error(this.$t('service.errors.unsupportedInit').toString());
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
