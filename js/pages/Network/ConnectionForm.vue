<template>
	<CCard body-wrapper>
		<ValidationObserver v-slot='{ invalid }'>
			<CForm @submit.prevent='handleSubmit'>
				<ValidationProvider
					v-slot='{ errors, touched, valid }'
					rules='required'
					:custom-messages='{required: "network.connection.form.messages.instance"}'
				>
					<CInput
						v-model='configuration.name'
						:label='$t("network.connection.name")'
						:is-valid='touched ? valid : null'
						:invalid-feedback='$t(errors[0])'
					/>
				</ValidationProvider>
				<fieldset>
					<legend>{{ $t('network.connection.ipv4.title') }}</legend>
					<ValidationProvider
						v-slot='{ errors, touched, valid}'
						rules='required'
						:custom-messages='{required: "network.connection.ipv4.messages.method"}'
					>
						<CSelect
							:value.sync='configuration.ipv4.method'
							:label='$t("network.connection.ipv4.method")'
							:options='ipv4Methods'
							:placeholder='$t("network.connection.ipv4.methods.null")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
						/>
					</ValidationProvider>
					<div
						v-for='(address, index) in configuration.ipv4.addresses'
						:key='index'
					>
						<ValidationProvider
							v-slot='{ errors, touched, valid}'
							rules='required'
							:custom-messages='{required: "network.connection.ipv4.messages.address"}'
						>
							<CInput
								v-model='address.address'
								:label='$t("network.connection.ipv4.address")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<ValidationProvider
							v-slot='{ errors, touched, valid}'
							rules='required'
							:custom-messages='{required: "network.connection.ipv4.messages.mask"}'
						>
							<CInput
								v-model='address.mask'
								:label='$t("network.connection.ipv4.mask")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<div class='form-group'>
							<CButton color='danger' @click='deleteIpv4Address(index)'>
								{{ $t('network.connection.ipv4.addresses.remove') }}
							</CButton>
							<CButton
								v-if='index === (configuration.ipv4.addresses.length - 1)'
								color='success'
								@click='addIpv4Address'
							>
								{{ $t('network.connection.ipv4.addresses.add') }}
							</CButton>
						</div>
					</div>
					<div class='form-group'>
						<CButton
							v-if='configuration.ipv4.addresses.length === 0'
							color='success'
							@click='addIpv4Address'
						>
							{{ $t('network.connection.ipv4.addresses.add') }}
						</CButton>
					</div>
					<ValidationProvider
						v-slot='{ errors, touched, valid}'
						:rules='configuration.ipv4.method === "manual" ? "required" : ""'
						:custom-messages='{required: "network.connection.ipv4.messages.gateway"}'
					>
						<CInput
							v-model='configuration.ipv4.gateway'
							:label='$t("network.connection.ipv4.gateway")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
						/>
					</ValidationProvider>
					<div v-for='(address, index) in configuration.ipv4.dns' :key='index'>
						<ValidationProvider
							v-slot='{ errors, touched, valid}'
							rules='required'
							:custom-messages='{required: "network.connection.ipv4.messages.dns"}'
						>
							<CInput
								v-model='address.address'
								:label='$t("network.connection.ipv4.dns.address")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<div class='form-group'>
							<CButton color='danger' @click='deleteIpv4Dns(index)'>
								{{ $t('network.connection.ipv4.dns.remove') }}
							</CButton>
							<CButton
								v-if='index === (configuration.ipv4.dns.length - 1)'
								color='success'
								@click='addIpv4Dns'
							>
								{{ $t('network.connection.ipv4.dns.add') }}
							</CButton>
						</div>
					</div>
					<div v-if='configuration.ipv4.dns.length === 0' class='form-group'>
						<CButton color='success' @click='addIpv4Dns'>
							{{ $t('network.connection.ipv4.dns.add') }}
						</CButton>
					</div>
				</fieldset>
				<fieldset>
					<legend>{{ $t('network.connection.ipv6.title') }}</legend>
					<ValidationProvider
						v-slot='{ errors, touched, valid}'
						rules='required'
						:custom-messages='{required: "network.connection.ipv6.messages.method"}'
					>
						<CSelect
							:value.sync='configuration.ipv6.method'
							:label='$t("network.connection.ipv6.method")'
							:options='ipv6Methods'
							:placeholder='$t("network.connection.ipv6.methods.null")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
						/>
					</ValidationProvider>
					<div
						v-for='(address, index) in configuration.ipv6.addresses'
						:key='index'
					>
						<ValidationProvider
							v-slot='{ errors, touched, valid}'
							rules='required'
							:custom-messages='{required: "network.connection.ipv6.messages.address"}'
						>
							<CInput
								v-model='address.address'
								:label='$t("network.connection.ipv6.address")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<ValidationProvider
							v-slot='{ errors, touched, valid}'
							rules='required'
							:custom-messages='{required: "network.connection.ipv6.messages.prefix"}'
						>
							<CInput
								v-model.number='address.prefix'
								:label='$t("network.connection.ipv6.prefix")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<ValidationProvider
							v-slot='{ errors, touched, valid}'
						>
							<CInput
								v-model='address.gateway'
								:label='$t("network.connection.ipv6.gateway")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<div class='form-group'>
							<CButton color='danger' @click='deleteIpv6Address(index)'>
								{{ $t('network.connection.ipv6.addresses.remove') }}
							</CButton>
							<CButton
								v-if='index === (configuration.ipv6.addresses.length - 1)'
								color='success'
								@click='addIpv6Address'
							>
								{{ $t('network.connection.ipv6.addresses.add') }}
							</CButton>
						</div>
					</div>
					<div class='form-group'>
						<CButton
							v-if='configuration.ipv6.addresses.length === 0'
							color='success'
							@click='addIpv6Address'
						>
							{{ $t('network.connection.ipv6.addresses.add') }}
						</CButton>
					</div>
					<div
						v-for='(address, index) in configuration.ipv6.dns'
						:key='index'
					>
						<ValidationProvider
							v-slot='{ errors, touched, valid}'
							rules='required'
							:custom-messages='{required: "network.connection.ipv6.messages.dns"}'
						>
							<CInput
								v-model='address.address'
								:label='$t("network.connection.ipv6.dns.address")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<div class='form-group'>
							<CButton color='danger' @click='deleteIpv6Dns(index)'>
								{{ $t('network.connection.ipv6.dns.remove') }}
							</CButton>
							<CButton
								v-if='index === (configuration.ipv6.dns.length - 1)'
								color='success'
								@click='addIpv6Dns'
							>
								{{ $t('network.connection.ipv6.dns.add') }}
							</CButton>
						</div>
					</div>
					<div v-if='configuration.ipv6.dns.length === 0' class='form-group'>
						<CButton color='success' @click='addIpv6Dns'>
							{{ $t('network.connection.ipv6.dns.add') }}
						</CButton>
					</div>
				</fieldset>
				<CButton type='submit' color='primary' :disabled='invalid'>
					{{ submitButton }}
				</CButton>
			</CForm>
		</ValidationObserver>
	</CCard>
</template>

<script>import {CButton, CCard, CForm, CInput, CSelect} from '@coreui/vue';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {required} from 'vee-validate/dist/rules';
import NetworkConnectionService from '../../services/NetworkConnectionService';

export default {
	name: 'ConnectionForm',
	components: {
		CButton,
		CCard,
		CForm,
		CInput,
		CSelect,
		ValidationObserver,
		ValidationProvider,
	},
	props: {
		uuid: {
			type: String,
			required: false,
			default: null,
		},
	},
	data() {
		return {
			configuration: {
				id: undefined,
				ipv4: {
					addresses: [],
					dns: [],
					gateway: undefined,
					method: undefined,
				},
				ipv6: {
					addresses: [],
					dns: [],
					method: undefined,
				}
			},
		};
	},
	computed: {
		ipv4Methods() {
			const methods = ['auto', 'disabled', 'link-local', 'manual', 'shared'];
			return methods.map(
				(method) => ({
					value: method,
					label: this.$t('network.connection.ipv4.methods.' + method),
				})
			);
		},
		ipv6Methods() {
			const methods = [
				'auto', 'disabled', 'dhcp', 'ignore', 'link-local', 'manual', 'shared',
			];
			return methods.map((method) =>
				({
					value: method,
					label: this.$t('network.connection.ipv6.methods.' + method),
				})
			);
		},
		submitButton() {
			return this.$route.path === '/network/add' ?
				this.$t('forms.add') : this.$t('forms.save');
		},
	},
	created() {
		this.$store.commit('spinner/SHOW');
		extend('required', required);
		NetworkConnectionService.get(this.uuid)
			.then((response) => {
				this.configuration = response.data;
				this.$store.commit('spinner/HIDE');
			});
	},
	methods: {
		addIpv4Address() {
			this.configuration.ipv4.addresses.push({address: null, mask: null});
		},
		addIpv4Dns() {
			this.configuration.ipv4.dns.push({address: null});
		},
		deleteIpv4Address(index) {
			this.configuration.ipv4.addresses.splice(index, 1);
		},
		deleteIpv4Dns(index) {
			this.configuration.ipv4.dns.splice(index, 1);
		},
		addIpv6Address() {
			this.configuration.ipv6.addresses.push({address: null, mask: null});
		},
		addIpv6Dns() {
			this.configuration.ipv6.dns.push({address: null});
		},
		deleteIpv6Address(index) {
			this.configuration.ipv6.addresses.splice(index, 1);
		},
		deleteIpv6Dns(index) {
			this.configuration.ipv6.dns.splice(index, 1);
		},
		handleSubmit() {
			this.$store.commit('spinner/SHOW');
			NetworkConnectionService.edit(this.uuid, this.configuration)
				.then(() => {
					NetworkConnectionService.connect(this.uuid).then(() => {
						this.$toast.success(
							this.$t('network.connection.messages.edit.success').toString()
						);
						this.$store.commit('spinner/HIDE');
					});
				}).catch(() => {
					this.$toast.error(
						this.$t('network.connection.messages.edit.failure').toString()
					);
					this.$store.commit('spinner/HIDE');
				});
		}
	},
	metaInfo: {
		title: 'network.ethernet.edit',
	},
};
</script>
