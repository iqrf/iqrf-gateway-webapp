<template>
	<div>
		<h1>{{ $t('network.ethernet.' + ($route.path === '/network/add' ? 'add' : 'edit')) }}</h1>
		<CCard>
			<CCardBody>
				<ValidationObserver v-slot='{invalid}'>
					<CForm @submit.prevent='saveConnection'>
						<ValidationProvider
							v-slot='{ errors, touched, valid }'
							rules='required'
							:custom-messages='{
								required: "network.connection.form.messages.instance"
							}'
						>
							<CInput
								v-model='connection.name'
								:label='$t("network.connection.name")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<CRow>
							<CCol>
								<legend>{{ $t('network.connection.ipv4.title') }}</legend>
								<ValidationProvider
									v-slot='{errors, touched, valid}'
									rules='required'
									:custom-messages='{
										required: "network.connection.ipv4.messages.method"
									}'
								>
									<CSelect
										:value.sync='connection.ipv4.method'
										:label='$t("network.connection.ipv4.method")'
										:options='ipv4Methods'
										:placeholder='$t("network.connection.ipv4.methods.null")'
										:is-valid='touched ? valid : null'
										:invalid-feedback='$t(errors[0])'
									/>
								</ValidationProvider>
								<fieldset v-if='connection.ipv4.method === "manual"'>
									<ValidationProvider
										v-slot='{errors, touched, valid}'
										:rules='connection.ipv4.method === "manual" ? "required" : ""'
										:custom-messages='{
											required: "network.connection.ipv4.messages.address"
										}'
									>
										<CInput
											v-model='connection.ipv4.addresses[0].address'
											:label='$t("network.connection.ipv4.address")'
											:is-valid='touched ? valid : null'
											:invalid-feedback='$t(errors[0])'
										/>
									</ValidationProvider>
									<ValidationProvider
										v-slot='{errors, touched, valid}'
										:rules='connection.ipv4.method === "manual" ? "required" : ""'
										:custom-messages='{
											required: "network.connection.ipv4.messages.mask"
										}'
									>
										<CInput
											v-model='connection.ipv4.addresses[0].mask'
											:label='$t("network.connection.ipv4.mask")'
											:is-valid='touched ? valid : null'
											:invalid-feedback='$t(errors[0])'
										/>
									</ValidationProvider>
									<ValidationProvider
										v-slot='{errors, touched, valid}'
										:rules='connection.ipv4.method === "manual" ? "required" : ""'
										:custom-messages='{
											required: "network.connection.ipv4.messages.gateway"
										}'
									>
										<CInput
											v-model='connection.ipv4.gateway'
											:label='$t("network.connection.ipv4.gateway")'
											:is-valid='touched ? valid : null'
											:invalid-feedback='$t(errors[0])'
										/>
									</ValidationProvider>
									<ValidationProvider
										v-if='connection.ipv4.method !== "disabled"'
										v-slot='{errors, touched, valid}'
										rules='required'
										:custom-messages='{
											required: "network.connection.ipv4.messages.dns"
										}'
									>
										<CInput
											v-model='connection.ipv4.dns[0].address'
											:label='$t("network.connection.ipv4.dns.address")'
											:is-valid='touched ? valid : null'
											:invalid-feedback='$t(errors[0])'
										/>
									</ValidationProvider>
								</fieldset>
							</CCol>
							<CCol>
								<legend>{{ $t('network.connection.ipv6.title') }}</legend>
								<ValidationProvider
									v-slot='{errors, touched, valid}'
									rules='required'
									:custom-messages='{
										required: "network.connection.ipv6.messages.method"
									}'
								>
									<CSelect
										:value.sync='connection.ipv6.method'
										:label='$t("network.connection.ipv6.method")'
										:options='ipv6Methods'
										:placeholder='$t("network.connection.ipv6.methods.null")'
										:is-valid='touched ? valid : null'
										:invalid-feedback='$t(errors[0])'
									/>
								</ValidationProvider>
								<fieldset v-if='connection.ipv6.method === "manual"'>
									<ValidationProvider
										v-slot='{errors, touched, valid}'
										:rules='connection.ipv6.method === "manual" ? "required":""'
										:custom-messages='{
											required: "network.connection.ipv6.messages.address"
										}'
									>
										<CInput
											v-model='connection.ipv6.addresses[0].address'
											:label='$t("network.connection.ipv6.address")'
											:is-valid='touched ? valid : null'
											:invalid-feedback='$t(errors[0])'
										/>
									</ValidationProvider>
									<ValidationProvider
										v-slot='{errors, touched, valid}'
										:rules='connection.ipv6.method === "manual" ? "required":""'
										:custom-messages='{
											required: "network.connection.ipv6.messages.prefix"
										}'
									>
										<CInput
											v-model.number='connection.ipv6.addresses[0].prefix'
											:label='$t("network.connection.ipv6.prefix")'
											:is-valid='touched ? valid : null'
											:invalid-feedback='$t(errors[0])'
										/>
									</ValidationProvider>
									<ValidationProvider
										v-slot='{errors, touched, valid}'
										:rules='connection.ipv6.method === "manual" ? "required":""'
										:custom-messages='{
											required: "network.connection.ipv6.messages.gateway"
										}'
									>
										<CInput
											v-model='connection.ipv6.addresses[0].gateway'
											:label='$t("network.connection.ipv6.gateway")'
											:is-valid='touched ? valid : null'
											:invalid-feedback='$t(errors[0])'
										/>
									</ValidationProvider>
									<ValidationProvider
										v-slot='{errors, touched, valid}'
										:rules='connection.ipv6.method === "manual" ? "required":""'
										:custom-messages='{
											required: "network.connection.ipv6.messages.dns"
										}'
									>
										<CInput
											v-model='connection.ipv6.addresses[0].address'
											:label='$t("network.connection.ipv6.dns.address")'
											:is-valid='touched ? valid : null'
											:invalid-feedback='$t(errors[0])'
										/>
									</ValidationProvider>
								</fieldset>
							</CCol>
						</CRow>
						<CButton
							type='submit'
							color='primary'
							:disabled='invalid'
						>
							{{ $t('forms.save') }}
						</CButton>
					</CForm>
				</ValidationObserver>
			</CCardBody>
		</CCard>
	</div>
</template>

<script lang='ts'>
import {Component, Prop, Vue} from 'vue-property-decorator';
import {CButton, CCard, CCardBody, CForm, CInput, CSelect} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {required} from 'vee-validate/dist/rules';
import NetworkConnectionService from '../../services/NetworkConnectionService';
import {IConnection} from '../../interfaces/network';
import {AxiosResponse} from 'axios';
import {IOption} from '../../interfaces/coreui';

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CForm,
		CInput,
		CSelect,
		ValidationObserver,
		ValidationProvider,
	},
	metaInfo: {
		title: 'network.ethernet.edit',
	}
})

export default class ConnectionFormBasic extends Vue {

	/**
	 * @var {IConnection} connection Configuration of IPv4 and IPv6 connectivity
	 */
	private connection: IConnection = {
		ipv4: {
			addresses: [],
			dns: [],
			gateway: '',
			method: '',
		},
		ipv6: {
			addresses: [],
			dns: [],
			method: '',
		}
	}

	/**
	 * @var {boolean} powerUser Indicates that user is a power user
	 */
	private powerUser = false

	/**
	 * @property {string} uuid Network connection configuration id
	 */
	@Prop({required: false, default: null}) uuid!: string

	/**
	 * Computes array of CoreUI select options for IPv4 configuration method
	 * @returns {Array<IOption>} Configuration method options
	 */
	get ipv4Methods(): Array<IOption> {
		const methods = this.powerUser ?
			['auto', 'link-local', 'manual', 'shared']:
			['auto', 'manual'];
		let methodOptions: Array<IOption> = methods.map(
			(method: string) => ({
				value: method,
				label: this.$t('network.connection.ipv4.methods.' + method).toString(),
			})
		);
		methodOptions.push({
			value: 'disabled',
			label: this.$t('states.disabled').toString()
		});
		return methodOptions;
	}

	/**
	 * Computes array of CoreUI select options for IPv6 configuration method
	 * @returns {Array<IOption>} Configuration method options
	 */
	get ipv6Methods(): Array<IOption> {
		const methods = this.powerUser ?
			['auto', 'dhcp', 'ignore', 'link-local', 'manual', 'shared']:
			['auto', 'dhcp', 'manual'];
		let methodOptions: Array<IOption> = methods.map((method: string) =>
			({
				value: method,
				label: this.$t('network.connection.ipv6.methods.' + method).toString(),
			})
		);
		methodOptions.push({
			value: 'disabled',
			label: this.$t('states.disabled').toString()
		});
		return methodOptions;
	}

	/**
	 * Initializes validation rules
	 */
	created(): void {
		extend('required', required);
	}

	/**
	 * Fetches connection configuration prop is set
	 */
	mounted(): void {
		if (this.$store.getters['user/getRole'] === 'power') {
			this.powerUser = true;
		}
		if (this.uuid !== null) {
			this.getConnection();
		}
	}

	/**
	 * Computes page title
	 * @returns {string} Page title
	 */
	get pageTitle(): string {
		return this.$t(
			'network.ethernet.' + 
			(this.$route.path === '/network/add/' ? 'add' : 'edit')
		).toString();
	}

	/**
	 * Get connection specified by prop
	 */
	private getConnection(): void {
		this.$store.commit('spinner/SHOW');
		NetworkConnectionService.get(this.uuid)
			.then((response: AxiosResponse) => {
				this.$store.commit('spinner/HIDE');
				this.storeConnectionData(response.data);
			})
			.catch(() => {
				this.$store.commit('spinner/HIDE');
				this.$toast.error(
					this.$t('network.messages.loadFailed').toString()
				);
				this.$router.push('/network/ethernet/');
			});
	}

	/**
	 * Initializes empty arrays for the form and stores configuration
	 * @param {IConnection} connection Connection details
	 */
	private storeConnectionData(connection: IConnection): void {
		if (connection.ipv4.addresses.length === 0) {
			connection.ipv4.addresses.push({address: '', mask: ''});
		}
		if (connection.ipv4.dns.length === 0) {
			connection.ipv4.dns.push({address: ''});
		}
		if (connection.ipv6.addresses.length === 0) {
			connection.ipv6.addresses.push({address: '', prefix: 0, gateway: ''});
		}
		if (connection.ipv6.dns.length === 0) {
			connection.ipv6.dns.push({address: ''});
		}
		this.connection = connection;
	}

	private saveConnection(): void {
		let connection = JSON.parse(JSON.stringify(this.connection));
		console.warn(connection);
	}

}
</script>
