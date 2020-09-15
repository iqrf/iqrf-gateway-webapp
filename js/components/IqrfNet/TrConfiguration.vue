<template>
	<CCard>
		<CCardHeader>{{ $t('iqrfnet.trConfiguration.title') }}</CCardHeader>
		<CCardBody>
			<ValidationObserver v-if='config !== null' v-slot='{ invalid }'>
				<CForm @submit.prevent='handleSubmit'>
					<CRow>
						<CCol md='6'>
							<h2>{{ $t('iqrfnet.trConfiguration.form.rf') }}</h2>
							<CSelect
								v-model='config.rfBand'
								:label='$t("iqrfnet.trConfiguration.form.rfBand")'
								:options='[
									{value: "443", label: $t("iqrfnet.trConfiguration.form.rfBands.443")},
									{value: "868", label: $t("iqrfnet.trConfiguration.form.rfBands.868")},
									{value: "916", label: $t("iqrfnet.trConfiguration.form.rfBands.916")},
								]'
								disabled='true'
							/>
							<ValidationProvider
								v-slot='{ valid, touched, errors }'
								:rules='rfChannelRules.rule'
								:custom-messages='rfChannelValidatorMessages'
							>
								<CInput
									v-model.number='config.rfChannelA'
									:label='$t("iqrfnet.trConfiguration.form.rfChannelA")'
									:is-valid='touched ? valid : null'
									:invalid-feedback='$t(errors[0])'
									type='number'
									:max='rfChannelRules.max'
									:min='rfChannelRules.min'
								/>
							</ValidationProvider>
							<ValidationProvider
								v-slot='{ valid, touched, errors }'
								:rules='rfChannelRules.rule'
								:custom-messages='rfChannelValidatorMessages'
							>
								<CInput
									v-model.number='config.rfChannelB'
									:label='$t("iqrfnet.trConfiguration.form.rfChannelB")'
									:is-valid='touched ? valid : null'
									:invalid-feedback='$t(errors[0])'
									type='number'
									:max='rfChannelRules.max'
									:min='rfChannelRules.min'
								/>
							</ValidationProvider>
							<ValidationProvider
								v-if='config.rfSubChannelA !== undefined'
								v-slot='{ valid, touched, errors }'
								:rules='rfChannelRules.rule'
								:custom-messages='rfChannelValidatorMessages'
							>
								<CInput
									v-model.number='config.rfSubChannelA'
									:label='$t("iqrfnet.trConfiguration.form.rfSubChannelA")'
									:is-valid='touched ? valid : null'
									:invalid-feedback='$t(errors[0])'
									type='number'
									:max='rfChannelRules.max'
									:min='rfChannelRules.min'
								/>
							</ValidationProvider>
							<ValidationProvider
								v-if='config.rfSubChannelB !== undefined'
								v-slot='{ valid, touched, errors }'
								:rules='rfChannelRules.rule'
								:custom-messages='rfChannelValidatorMessages'
							>
								<CInput
									v-model.number='config.rfSubChannelB'
									:label='$t("iqrfnet.trConfiguration.form.rfSubChannelB")'
									:is-valid='touched ? valid : null'
									:invalid-feedback='$t(errors[0])'
									type='number'
									:max='rfChannelRules.max'
									:min='rfChannelRules.min'
								/>
							</ValidationProvider>
							<ValidationProvider
								v-if='config.rfAltDsmChannel !== undefined'
								v-slot='{ valid, touched, errors }'
								:rules='rfChannelRules.rule'
								:custom-messages='rfChannelValidatorMessages'
							>
								<CInput
									v-model.number='config.rfAltDsmChannel'
									:label='$t("iqrfnet.trConfiguration.form.rfAltDsmChannel")'
									:is-valid='touched ? valid : null'
									:invalid-feedback='$t(errors[0])'
									type='number'
									:max='rfChannelRules.max'
									:min='rfChannelRules.min'
								/>
							</ValidationProvider>
							<CAlert v-if='config.stdAndLpNetwork === false' color='warning'>
								{{ $t('iqrfnet.trConfiguration.form.messages.breakInteroperability') }}
							</CAlert>
							<CInputCheckbox
								v-if='config.stdAndLpNetwork !== undefined'
								:checked.sync='config.stdAndLpNetwork'
								:label='$t("iqrfnet.trConfiguration.form.stdAndLpNetwork")'
							/>
							<ValidationProvider
								v-slot='{ valid, touched, errors }'
								rules='integer|between:0,7|required'
								:custom-messages='{
									between: "iqrfnet.trConfiguration.form.messages.txPower",
									integer: "iqrfnet.trConfiguration.form.messages.txPower",
									required: "iqrfnet.trConfiguration.form.messages.txPower",
								}'
							>
								<CInput
									v-model.number='config.txPower'
									:label='$t("iqrfnet.trConfiguration.form.txPower")'
									:is-valid='touched ? valid : null'
									:invalid-feedback='$t(errors[0])'
									type='number'
									max='7'
									min='0'
								/>
							</ValidationProvider>
							<ValidationProvider
								v-slot='{ valid, touched, errors }'
								rules='integer|between:0,64|required'
								:custom-messages='{
									between: "iqrfnet.trConfiguration.form.messages.rxFilter",
									integer: "iqrfnet.trConfiguration.form.messages.rxFilter",
									required: "iqrfnet.trConfiguration.form.messages.rxFilter",
								}'
							>
								<CInput
									v-model.number='config.rxFilter'
									:label='$t("iqrfnet.trConfiguration.form.rxFilter")'
									:is-valid='touched ? valid : null'
									:invalid-feedback='$t(errors[0])'
									type='number'
									max='64'
									min='0'
								/>
							</ValidationProvider>
							<ValidationProvider
								v-slot='{ valid, touched, errors }'
								rules='integer|between:1,255|required'
								:custom-messages='{
									between: "iqrfnet.trConfiguration.form.messages.lpRxTimeout",
									integer: "iqrfnet.trConfiguration.form.messages.lpRxTimeout",
									required: "iqrfnet.trConfiguration.form.messages.lpRxTimeout",
								}'
							>
								<CInput
									v-model.number='config.lpRxTimeout'
									:label='$t("iqrfnet.trConfiguration.form.lpRxTimeout")'
									:is-valid='touched ? valid : null'
									:invalid-feedback='$t(errors[0])'
									type='number'
								/>
							</ValidationProvider>
							<h2>{{ $t('iqrfnet.trConfiguration.form.rfPgm') }}</h2>
							<CInputCheckbox
								:checked.sync='config.rfPgmEnableAfterReset'
								:label='$t("iqrfnet.trConfiguration.form.rfPgmEnableAfterReset")'
							/>
							<CInputCheckbox
								:checked.sync='config.rfPgmTerminateAfter1Min'
								:label='$t("iqrfnet.trConfiguration.form.rfPgmTerminateAfter1Min")'
							/>
							<CInputCheckbox
								:checked.sync='config.rfPgmTerminateMcuPin'
								:label='$t("iqrfnet.trConfiguration.form.rfPgmTerminateMcuPin")'
							/>
							<CInputCheckbox
								:checked.sync='config.rfPgmDualChannel'
								:label='$t("iqrfnet.trConfiguration.form.rfPgmDualChannel")'
							/>
							<CInputCheckbox
								:checked.sync='config.rfPgmLpMode'
								:label='$t("iqrfnet.trConfiguration.form.rfPgmLpMode")'
							/>
							<CInputCheckbox
								:checked.sync='config.rfPgmIncorrectUpload'
								:label='$t("iqrfnet.trConfiguration.form.rfPgmIncorrectUpload")'
								disabled='true'
							/>
						</CCol>
						<CCol md='6'>
							<h2>{{ $t('iqrfnet.trConfiguration.form.dpa.embeddedPeripherals') }}</h2>
							<CInputCheckbox
								v-for='peripheral of peripherals'
								:key='peripheral.name'
								:checked.sync='peripheral.enabled'
								:disabled='unchangeablePeripherals.includes(peripheral.name)'
								:label='$t("iqrfnet.trConfiguration.form.embPers." + peripheral.name)'
							/>
							<h2>{{ $t('iqrfnet.trConfiguration.form.dpa.other') }}</h2>
							<CInputCheckbox
								:checked.sync='config.customDpaHandler'
								:label='$t("iqrfnet.trConfiguration.form.customDpaHandler")'
								:disabled='!dpaHandlerDetected'
							/>
							<CInputCheckbox
								:checked.sync='config.ioSetup'
								:label='$t("iqrfnet.trConfiguration.form.ioSetup")'
							/>
							<CInputCheckbox
								:checked.sync='config.dpaAutoexec'
								:label='$t("iqrfnet.trConfiguration.form.dpaAutoexec")'
							/>
							<CInputCheckbox
								:checked.sync='config.routingOff'
								:label='$t("iqrfnet.trConfiguration.form.routingOff")'
							/>
							<CInputCheckbox
								:checked.sync='config.peerToPeer'
								:label='$t("iqrfnet.trConfiguration.form.peerToPeer")'
							/>
							<CInputCheckbox
								v-if='config.dpaPeerToPeer !== undefined'
								:checked.sync='config.dpaPeerToPeer'
								:label='$t("iqrfnet.trConfiguration.form.dpaPeerToPeer")'
							/>
							<CInputCheckbox
								v-if='config.neverSleep !== undefined'
								:checked.sync='config.neverSleep'
								:label='$t("iqrfnet.trConfiguration.form.neverSleep")'
							/>
							<CSelect
								:value.sync='config.uartBaudrate'
								:label='$t("iqrfnet.trConfiguration.form.uartBaudrate")'
								:options='[
									{value: 1200, label: $t("iqrfnet.trConfiguration.form.uartBaudrates.1200")},
									{value: 2400, label: $t("iqrfnet.trConfiguration.form.uartBaudrates.2400")},
									{value: 4800, label: $t("iqrfnet.trConfiguration.form.uartBaudrates.4800")},
									{value: 9600, label: $t("iqrfnet.trConfiguration.form.uartBaudrates.9600")},
									{value: 19200, label: $t("iqrfnet.trConfiguration.form.uartBaudrates.19200")},
									{value: 38400, label: $t("iqrfnet.trConfiguration.form.uartBaudrates.38400")},
									{value: 57600, label: $t("iqrfnet.trConfiguration.form.uartBaudrates.57600")},
									{value: 115200, label: $t("iqrfnet.trConfiguration.form.uartBaudrates.115200")},
									{value: 230400, label: $t("iqrfnet.trConfiguration.form.uartBaudrates.230400")},
								]'
							/>
							<CInputCheckbox
								v-if='config.nodeDpaInterface !== undefined'
								:checked.sync='config.nodeDpaInterface'
								:label='$t("iqrfnet.trConfiguration.form.nodeDpaInterface")'
							/>
						</CCol>
					</CRow>
					<CButton color='primary' type='submit' :disabled='invalid'>
						{{ $t('forms.write') }}
					</CButton>
				</CForm>
			</ValidationObserver>
		</CCardBody>
	</CCard>
</template>

<script>
import {CCard, CCardBody, CCardHeader, CForm, CInput} from '@coreui/vue';
import {
	between,
	integer,
	min_value,
	max_value,
	required,
} from 'vee-validate/dist/rules';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import IqrfNetService from '../../services/IqrfNetService';

export default {
	name: 'TrConfiguration',
	components: {
		CCard,
		CCardBody,
		CCardHeader,
		CForm,
		CInput,
		ValidationObserver,
		ValidationProvider,
	},
	props: {
		address: {
			type: Number,
			required: true
		},
	},
	data() {
		return {
			config: null,
			peripherals: [],
			dpaHandlerDetected: null,
			dpaVersion: null,
			unchangeablePeripherals: ['coordinator', 'node', 'os'],
		};
	},
	computed: {
		rfChannelRules() {
			switch (this.config.rfBand) {
				case '433':
					return {rule:'integer|between:0,16|required', min: 0, max: 16};
				case '868':
					return {rule: 'integer|between:0,67|required', min: 0, max: 67};
				case '916':
					return {rule: 'integer|between:0,255|required', min: 0, max: 255};
			}
			return undefined;
		},
		rfChannelValidatorMessages() {
			let message = '';
			switch (this.config.rfBand) {
				case '433':
					message = 'iqrfnet.trConfiguration.form.messages.rfChannel.433';
					break;
				case '868':
					message = 'iqrfnet.trConfiguration.form.messages.rfChannel.868';
					break;
				case '916':
					message = 'iqrfnet.trConfiguration.form.messages.rfChannel.916';
					break;
			}
			return {
				between: message,
				integer: message,
				required: message
			};
		}
	},
	created() {
		extend('between', between);
		extend('integer', integer);
		extend('min', min_value);
		extend('max', max_value);
		extend('required', required);
		this.unsubscribe = this.$store.subscribe(mutation => {
			if (mutation.type === 'SOCKET_ONOPEN') {
				this.$store.commit('spinner/SHOW');
				IqrfNetService.enumerateDevice(this.address);
				return;
			}
			if (mutation.type !== 'SOCKET_ONMESSAGE') {
				return;
			}
			if (mutation.payload.mType === 'iqmeshNetwork_WriteTrConf') {
				this.$store.commit('spinner/HIDE');
				this.$toast.success(this.$t(''));
				return;
			}
			if (mutation.payload.mType !== 'iqmeshNetwork_EnumerateDevice') {
				return;
			}
			const response = mutation.payload.data.rsp;
			this.config = response.trConfiguration;
			this.dpaHandlerDetected = response.osRead.flags.dpaHandlerDetected;
			this.dpaVersion = response.peripheralEnumeration.dpaVer;
			this.setEmbeddedPeripherals();
			this.$store.commit('spinner/HIDE');
		});
		if (this.$store.getters.isSocketConnected) {
			this.$store.commit('spinner/SHOW');
			IqrfNetService.enumerateDevice(this.address);
		}
	},
	beforeDestroy() {
		this.unsubscribe();
	},
	methods: {
		handleSubmit() {
			let config = JSON.parse(JSON.stringify(this.config));
			config.embPers = this.getEmbeddedPeripherals();
			this.$store.commit('spinner/SHOW');
			IqrfNetService.writeTrConfiguration(this.address, config);
		},
		setEmbeddedPeripherals() {
			let peripherals = JSON.parse(JSON.stringify(this.config.embPers));
			for (let peripheral in peripherals) {
				if (!peripherals.hasOwnProperty(peripheral)) {
					continue;
				}
				if (typeof this.config.embPers[peripheral] !== 'boolean') {
					continue;
				}
				if (this.unchangeablePeripherals.includes(peripheral) &&
						this.$store.getters['user/getRole'] === 'normal') {
					continue;
				}
				this.peripherals.push({
					name: peripheral,
					enabled: this.config.embPers[peripheral],
				});
			}
		},
		getEmbeddedPeripherals() {
			let peripherals = {};
			for (let peripheral of this.peripherals) {
				peripherals[peripheral.name] = peripheral.enabled;
			}
			return peripherals;
		}
	},
};
</script>

<style scoped>

</style>
