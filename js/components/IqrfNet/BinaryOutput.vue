<template>
	<CCard>
		<CCardHeader>{{ $t('iqrfnet.standard.binaryOutput.title') }}</CCardHeader>
		<CCardBody>
			<ValidationObserver v-slot='{ invalid }'>
				<CForm>
					<ValidationProvider
						v-slot='{ errors, touched, valid }'
						rules='required|between:1,239'
						:custom-messages='{
							required: "iqrfnet.standard.binaryOutput.from.messages.address",
							between: "iqrfnet.standard.binaryOutput.from.messages.address"
						}'
					>
						<CInput
							v-model.number='address'
							type='number'
							min='1'
							max='239'
							:label='$t("iqrfnet.standard.binaryOutput.form.address")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
						/>
					</ValidationProvider>
					<CInput
						v-model.number='index'
						type='number'
						min='0'
						max='31'
						:label='$t("iqrfnet.standard.binaryOutput.form.index")'
					/>
					<CSwitch
						color='primary'
						size='lg'
						shape='pill'
						class='switch'
						:label-on='$t("iqrfnet.standard.binaryOutput.form.enabled")'
						:label-off='$t("iqrfnet.standard.binaryOutput.form.disabled")'
						:checked.sync='state'
					/><br>
					<CButton color='primary' :disabled='invalid' @click.prevent='submitEnumerate'>
						{{ $t('forms.enumerate') }}
					</CButton>
					<CButton color='secondary' :disabled='invalid' @click.prevent='submitGetStates'>
						{{ $t('forms.getStates') }}
					</CButton>
					<CButton color='secondary' :disabled='invalid' @click.prevent='submitSetState'>
						{{ $t('forms.setState') }}
					</CButton>
				</CForm>
			</ValidationObserver>
		</CCardBody>
		<CCardFooter v-if='responseType !== null'>
			<table v-if='responseType === "enum"' class='table'>
				<tr>
					<th>{{ $t('iqrfnet.standard.binaryOutput.outputs') }}</th>
					<td>{{ numOutputs }}</td>
				</tr>
			</table>
			<b v-if='responseType === "set"'>{{ $t('iqrfnet.standard.binaryOutput.prev') }}</b>
			<table v-if='responseType === "set"' class='table scroll-table'>
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
import {between, required} from 'vee-validate/dist/rules';
import IqrfStandardService from '../../services/IqrfStandardService';

export default {
	name: 'BinaryOutput',
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
			timeoutVar: null,
		};
	},
	created() {
		extend('required', required);
		extend('between', between);
		this.unsubscribe = this.$store.subscribe(mutation => {
			if (mutation.type === 'SOCKET_ONSEND') {
				if (!this.allowedMTypes.includes(mutation.payload.mType)) {
					return;
				}
				this.responseType = null;
				this.timeoutVar = setTimeout(() => {this.timedOut();}, 10000);
			}
			if (mutation.type === 'SOCKET_ONMESSAGE') {
				if (!this.allowedMTypes.includes(mutation.payload.mType)) {
					return;
				}
				clearTimeout(this.timeoutVar);
				this.$store.commit('spinner/HIDE');
				if (mutation.payload.mType === 'iqrfBinaryoutput_Enumerate') {
					switch(mutation.payload.data.status) {
						case -1:
							this.$toast.error(this.$t('iqrfnet.standard.binaryOutput.messages.timeout'));
							break;
						case 0:
							this.$toast.success(this.$t('iqrfnet.standard.binaryOutput.messages.success'));
							this.numOutputs = mutation.payload.data.rsp.result.binOuts;
							this.responseType = 'enum';
							break;
						case 1:
							this.$toast.error(this.$t('iqrfnet.standard.binaryOutput.messages.fail'));
							break;
						case 3:
							this.$toast.error(this.$t('iqrfnet.standard.binaryOutput.messages.pnum'));
							break;
					}
				} else if (mutation.payload.mType === 'iqrfBinaryoutput_SetOutput') {
					switch(mutation.payload.data.status) {
						case -1:
							this.$toast.error(this.$t('iqrfnet.standard.binaryOutput.messages.timeout'));
							break;
						case 0:
							this.$toast.success(this.$t('iqrfnet.standard.binaryOutput.messages.success'));
							this.parseSetOutput(mutation.payload.data.rsp.result.prevVals);
							this.responseType = 'set';
							break;
						case 1:
							this.$toast.error(this.$t('iqrfnet.standard.binaryOutput.messages.fail'));
							break;
						case 3:
							this.$toast.error(this.$t('iqrfnet.standard.binaryOutput.messages.pnum'));
							break;
					}
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
			for(var i = 0; i < states.length; ++i) {
				this.states[i] = states[i];
			}
		},
		submitEnumerate() {
			this.$store.commit('spinner/SHOW');
			IqrfStandardService.binaryOutputEnumerate(this.address);
		},
		submitGetStates() {
			this.$store.commit('spinner/SHOW');
			IqrfStandardService.binaryOutputSetOutputs(this.address, null);
		},
		submitSetState() {
			this.$store.commit('spinner/SHOW');
			let state = {'index': this.index, 'state': this.state};
			IqrfStandardService.binaryOutputSetOutputs(this.address, state);
		},
		timedOut() {
			this.$store.commit('spinner/HIDE');
			this.$toast.error(this.$t('iqrfnet.networkManager.messages.submit.timeout'));
		},
	},
	icons: {
		on: cilCheckAlt,
		off: cilX
	}
};
</script>

<style scoped>

.switch {
	margin-bottom: 15pt;
}

.scroll-table {
    display: block;
    overflow-x: auto;
    white-space: nowrap;
}

</style>