<template>
	<CCard>
		<CCardHeader>{{ $t('iqrfnet.standard.binaryOutput.title') }}</CCardHeader>
		<CCardBody>
			<ValidationObserver v-slot='{ invalid }'>
				<CForm>
					<ValidationProvider
						v-slot='{ errors, touched, valid }'
						rules='integer|required|between:1,239'
						:custom-messages='{
							integer: "forms.messages.integer",
							required: "iqrfnet.standard.form.messages.address",
							between: "iqrfnet.standard.form.messages.address"
						}'
					>
						<CInput
							v-model.number='address'
							type='number'
							min='1'
							max='239'
							:label='$t("iqrfnet.standard.form.address")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
						/>
					</ValidationProvider>
					<ValidationProvider
						v-slot='{ errors, touched, valid }'
						rules='integer|required|between:0,31'
						:custom-messages='{
							integer: "forms.messages.integer",
							required: "iqrfnet.standard.binaryOutput.form.messages.index",
							between: "iqrfnet.standard.binaryOutput.form.messages.index"
						}'
					>
						<CInput
							v-model.number='index'
							type='number'
							min='0'
							max='31'
							:label='$t("iqrfnet.standard.binaryOutput.form.index")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
						/>
					</ValidationProvider>
					<div class='form-group'>
						<label for='formStateSwitch'>{{ $t('iqrfnet.standard.binaryOutput.form.state') }}</label><br>
						<CSwitch
							id='formStateSwitch'
							:checked.sync='state'
							color='primary'
							size='lg'
							shape='pill'
							:label-on='$t("iqrfnet.standard.binaryOutput.form.enabled")'
							:label-off='$t("iqrfnet.standard.binaryOutput.form.disabled")'
						/>
					</div>
					<CButton color='primary' :disabled='invalid' @click.prevent='submitEnumerate'>
						{{ $t('forms.enumerate') }}
					</CButton>
					<CButton color='secondary' :disabled='invalid' @click.prevent='submitGetStates'>
						{{ $t('iqrfnet.standard.binaryOutput.form.getStates') }}
					</CButton>
					<CButton color='secondary' :disabled='invalid' @click.prevent='submitSetState'>
						{{ $t('iqrfnet.standard.binaryOutput.form.setState') }}
					</CButton>
				</CForm>
			</ValidationObserver>
		</CCardBody>
		<CCardFooter v-if='responseType !== null'>
			<table v-if='responseType === "enum"' class='table'>
				<thead>
					<b>{{ $t('iqrfnet.standard.binaryOutput.enum') }}</b>
				</thead>
				<tbody>
					<tr>
						<th>{{ $t('iqrfnet.standard.binaryOutput.outputs') }}</th>
						<td>{{ numOutputs }}</td>
					</tr>
				</tbody>
			</table>
			<table v-if='responseType === "set"' class='table scroll-table'>
				<thead>
					<b>{{ $t('iqrfnet.standard.binaryOutput.prev') }}</b>
				</thead>
				<tbody>
					<tr>
						<th>{{ $t('iqrfnet.standard.binaryOutput.index') }}</th>
						<td v-for='col of Array(32).keys()' :key='col'>
							{{ col }}
						</td>
					</tr>
					<tr>
						<th>{{ $t('iqrfnet.standard.binaryOutput.state') }}</th>
						<td v-for='ind of Array(32).keys()' :key='ind'>
							<CIcon v-if='states[ind] === true' class='text-success' :content='$options.icons.on' />
							<CIcon v-if='states[ind] === false' class='text-danger' :content='$options.icons.off' />
						</td>
					</tr>
				</tbody>
			</table>
		</CCardFooter>
	</CCard>
</template>

<script>
import {CButton, CCard, CCardBody, CCardHeader, CForm, CIcon, CInput, CSwitch} from '@coreui/vue';
import {cilCheckAlt, cilX} from '@coreui/icons';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {timeout} from '../../helpers/timeout';
import {between, integer, required} from 'vee-validate/dist/rules';
import StandardBinaryOutputService from '../../services/DaemonApi/StandardBinaryOutputService';

export default {
	name: 'BinaryOutputManager',
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CForm,
		CIcon,
		CInput,
		CSwitch,
		ValidationObserver,
		ValidationProvider
	},
	data() {
		return {
			address: 1,
			allowedMTypes: [
				'iqrfBinaryoutput_Enumerate',
				'iqrfBinaryoutput_SetOutput'
			],
			index: 0,
			numOutputs: 0,
			responseType: null,
			state: false,
			states: null,
			timeout: null,
		};
	},
	created() {
		extend('integer', integer);
		extend('required', required);
		extend('between', between);
		this.unsubscribe = this.$store.subscribe(mutation => {
			if (mutation.type === 'SOCKET_ONSEND') {
				if (!this.allowedMTypes.includes(mutation.payload.mType)) {
					return;
				}
				this.responseType = null;
				this.timeout = timeout('iqrfnet.networkManager.messages.submit.timeout', 10000);
			}
			if (mutation.type === 'SOCKET_ONMESSAGE') {
				if (!this.allowedMTypes.includes(mutation.payload.mType)) {
					return;
				}
				clearTimeout(this.timeout);
				this.$store.commit('spinner/HIDE');
				switch(mutation.payload.data.status) {
					case -1:
						this.$toast.error(this.$t('iqrfnet.standard.binaryOutput.messages.timeout'));
						break;
					case 0:
						this.$toast.success(this.$t('iqrfnet.standard.binaryOutput.messages.success'));
						if (mutation.payload.mType === 'iqrfBinaryoutput_Enumerate') {
							this.numOutputs = mutation.payload.data.rsp.result.binOuts;
							this.responseType = 'enum';
						} else if (mutation.payload.mType === 'iqrfBinaryoutput_SetOutput') {
							this.parseSetOutput(mutation.payload.data.rsp.result.prevVals);
							this.responseType = 'set';
						}
						break;
					case 1:
						this.$toast.error(this.$t('iqrfnet.standard.binaryOutput.messages.fail'));
						break;
					case 3:
						this.$toast.error(this.$t('iqrfnet.standard.binaryOutput.messages.pnum'));
						break;
				}

			}
		});
		this.generateStates();
	},
	beforeDestroy() {
		this.unsubscribe();
	},
	methods: {
		generateStates() {
			this.states = new Array(60).fill(false);
		},
		parseSetOutput(states) {
			for(let i = 0; i < states.length; ++i) {
				this.states[i] = states[i];
			}
		},
		submitEnumerate() {
			this.$store.commit('spinner/SHOW');
			StandardBinaryOutputService.enumerate(this.address);
		},
		submitGetStates() {
			this.$store.commit('spinner/SHOW');
			StandardBinaryOutputService.getOutputs(this.address);
		},
		submitSetState() {
			this.$store.commit('spinner/SHOW');
			let state = {'index': this.index, 'state': this.state};
			StandardBinaryOutputService.setOutputs(this.address, [state]);
		},
	},
	icons: {
		on: cilCheckAlt,
		off: cilX,
	},
};
</script>

<style scoped>

.scroll-table {
    display: block;
    overflow-x: auto;
    white-space: nowrap;
}

</style>
