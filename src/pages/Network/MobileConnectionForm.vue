<!--
Copyright 2017-2021 IQRF Tech s.r.o.
Copyright 2019-2021 MICRORISC s.r.o.

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
		<h1>{{ pageTitle }}</h1>
		<CCard>
			<CCardBody>
				<ValidationObserver v-slot='{invalid}'>
					<CForm @submit.prevent='saveConnection'>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='required'
							:custom-messages='{
								required: "network.connection.errors.name"
							}'
						>
							<CInput
								v-model='connection.name'
								:label='$t("network.connection.name")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='required'
							:custom-messages='{
								required: "network.mobile.errors.interfaceMissing"
							}'
						>
							<CSelect
								:value.sync='connection.interface'
								:label='$t("network.connection.interface")'
								:placeholder='$t("network.mobile.form.interface")'
								:options='interfaceOptions'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<div class='form-group'>
							<label for='autoconnect'>
								<b>{{ $t("network.connection.autoconnect") }}</b>
							</label><br>
							<CSwitch
								id='autoconnect'
								size='lg'
								shape='pill'
								color='primary'
								label-on='ON'
								label-off='OFF'
							/>
						</div>
						<legend>{{ $t('network.mobile.form.title') }}</legend>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='required|apn'
							:custom-messages='{
								required: "network.mobile.errors.apnMissing",
								apn: "network.mobile.errors.apnInvalid"
							}'
						>
							<CInput
								v-model='connection.gsm.apn'
								:label='$t("network.mobile.form.apn")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<CInput
							v-model='connection.gsm.number'
							:label='$t("network.mobile.form.number")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
						/>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='pin'
							:custom-messages='{
								pin: "network.mobile.errors.pinInvalid"
							}'
						>
							<CInput
								v-model='connection.gsm.pin'
								:label='$t("network.mobile.form.pin")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							:rules='{
								required: connection.gsm.password.length > 0
							}'
							:custom-messages='{
								required: "network.mobile.errors.credentialsMissing"
							}'
						>
							<CInput
								v-model='connection.gsm.username'
								:rules='{
									required: connection.gsm.password.length > 0
								}'
								:label='$t("forms.fields.username")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							:rules='{
								required: connection.gsm.username.length > 0
							}'
							:custom-messages='{
								required: "network.mobile.errors.credentialsMissing"
							}'
						>
							<CInput
								v-model='connection.gsm.password'
								:label='$t("forms.fields.password")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<CButton
							color='primary'
							type='submit'
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
import {CButton, CCard, CCardBody, CForm, CInput, CSelect, CSwitch} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';

import {extendedErrorToast} from '../../helpers/errorToast';
import {required} from 'vee-validate/dist/rules';
import {v4 as uuidv4} from 'uuid';
import NetworkConnectionService from '../../services/NetworkConnectionService';
import NetworkInterfaceService, {InterfaceType} from '../../services/NetworkInterfaceService';

import {AxiosError, AxiosResponse} from 'axios';
import {IConnection} from '../../interfaces/network';
import {IOption} from '../../interfaces/coreui';
import {MetaInfo} from 'vue-meta';
import {NetworkInterface} from '../../interfaces/gatewayInfo';

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CForm,
		CInput,
		CSelect,
		CSwitch,
		ValidationObserver,
		ValidationProvider,
	},
	metaInfo(): MetaInfo {
		return {
			title: (this as MobileConnectionForm).pageTitle
		};
	}
})

/**
 * Mobile connection form
 */
export default class MobileConnectionForm extends Vue {

	/**
	 * @var {IConnection} connection Connection configuration
	 */
	private connection: IConnection = {
		autoConnect: {
			enabled: true,
			priority: 700,
			retries: -1,
		},
		name: '',
		type: 'gsm',
		interface: '',
		ipv4: {
			addresses: [],
			dns: [],
			gateway: '',
			method: 'auto',
		},
		ipv6: {
			addresses: [],
			dns: [],
			gateway: '',
			method: 'ignore',
		},
		gsm: {
			apn: '',
			number: '',
			username: '',
			password: '',
			pin: '',
		},
	}

	/**
	 * @var {Array<IOption>} interfaceOptions Available GSM interfaces
	 */
	private interfaceOptions: Array<IOption> = []

	/**
	 * @property {string} uuid GSM connection ID
	 */
	@Prop({required: false, default: null}) uuid!: string

	/**
	 * Computes page title from url
	 * @returns {string} Page title
	 */
	get pageTitle(): string {
		return this.$t(
			'network.mobile.' + (this.$route.path.includes('/add') ? 'add' : 'edit')
		).toString();
	}

	/**
	 * Initializes validation rules
	 */
	created(): void {
		extend('required', required);
		extend('apn', (apn: string) => {
			return new RegExp(/^[a-z0-9\\.\\-]+$/).test(apn);
		});
		extend('pin', (pin: string) => {
			return new RegExp(/^[0-9]{4}$/).test(pin);
		});
	}

	/**
	 * Retrieve list of available interfaces and connection details if editing one
	 */
	mounted(): void {
		this.getInterfaces();
	}

	/**
	 * Retrieves GSM interfaces
	 */
	private getInterfaces(): void {
		this.$store.commit('spinner/SHOW');
		NetworkInterfaceService.list(InterfaceType.GSM)
			.then((response: AxiosResponse) => {
				let interfaces: Array<IOption> = [];
				response.data.forEach((item: NetworkInterface) => {
					interfaces.push({label: item.name, value: item.name});
				});
				this.interfaceOptions = interfaces;
				this.$store.commit('spinner/HIDE');
				/*if (this.interfaceOptions.length === 0) {
					this.$toast.error(
						this.$t('network.mobile.messages.noInterfaces').toString()
					);
					this.$router.push('/network/mobile');
					return;
				}*/
				if (this.uuid !== null) {
					this.getConnection();
				}
			})
			.catch((error: AxiosError) => {
				extendedErrorToast(error, 'network.connection.messages.interfacesFetchFailed');
				this.$router.push('/network/mobile');
			});
	}

	/**
	 * Retrieves GSM connection
	 */
	private getConnection(): void {
		this.$store.commit('spinner/SHOW');
		NetworkConnectionService.get(this.uuid)
			.then((response: AxiosResponse) => {
				console.warn(response.data);
				this.connection = response.data;
				this.$store.commit('spinner/HIDE');
			})
			.catch((error: AxiosError) => {
				extendedErrorToast(
					error,
					'network.connection.messages.fetchFailed',
					{connection: this.uuid}
				);
				this.$router.push('/network/mobile');
			});
	}

	/**
	 * Saves connection configuration
	 */
	private saveConnection(): void {
		let connection: IConnection = JSON.parse(JSON.stringify(this.connection));
		this.$store.commit('spinner/SHOW');
		if (this.uuid === null) {
			connection.uuid = uuidv4();
			NetworkConnectionService.add(connection)
				.then(() => this.handleSuccess(connection.name))
				.catch(this.handleError);
		} else {
			NetworkConnectionService.edit(this.uuid, connection)
				.then(() => this.handleSuccess(connection.name))
				.catch(this.handleError);
		}
	}

	/**
	 * Handles connection save success
	 */
	private handleSuccess(name: string): void {
		this.$store.commit('spinner/HIDE');
		this.$toast.success(
			this.$t(
				'network.connection.messages.' +
				(this.$route.path.includes('/add') ? 'add' : 'edit') + '.success',
				{connection: name}).toString()
		);
		this.$router.push('/network/mobile');
	}

	/**
	 * Handles connection save errors
	 * @param {AxiosError} error Axios error
	 */
	private handleError(error: AxiosError): void {
		extendedErrorToast(
			error,
			'network.connection.messages.' +
			(this.$route.path.includes('/add') ? 'add' : 'edit') + '.failed'
		);
		this.$router.push('/network/mobile');
	}
}
</script>
