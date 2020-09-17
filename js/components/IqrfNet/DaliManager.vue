<template>
	<CCard>
		<CCardHeader>{{ $t('iqrfnet.standard.dali.title') }}</CCardHeader>
		<CCardBody>
			<ValidationObserver v-slot='{ invalid }'>
				<CForm>
					<ValidationProvider
						v-slot='{ errors, touched, valid }'
						rules='integer|required|between:1,239'
						:custom-messages='{
							between: "iqrfnet.standard.form.messages.address",
							integer: "forms.messages.integer",
							required: "iqrfnet.standard.form.messages.address"
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
					<div v-for='i of commands.length' :key='i' class='form-group'>
						<ValidationProvider
							v-slot='{ errors, touched, valid }'
							rules='integer|required|between:0,65535'
							:custom-messages='{
								between: "iqrfnet.standard.dali.form.messages.command",
								integer: "forms.messages.integer",
								required: "iqrfnet.standard.dali.form.messages.command"
							}'
						>
							<CInput
								v-model.number='commands[i-1]'
								type='number'
								min='0'
								max='65535'
								:label='$t("iqrfnet.standard.dali.form.command")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<CButton v-if='commands.length > 1' color='danger' @click.prevent='removeDaliCommand(i-1)'>
							{{ $t('iqrfnet.standard.dali.form.removeCommand') }}
						</CButton>
						<CButton
							v-if='i === commands.length' 
							color='success' 
							:disabled='invalid' 
							@click.prevent='addDaliCommand'
						>
							{{ $t('iqrfnet.standard.dali.form.addCommand') }}
						</CButton>
					</div>
					<CButton color='primary' :disabled='invalid' @click.prevent='sendDali'>
						{{ $t('iqrfnet.standard.dali.form.sendCommand') }}
					</CButton>
				</CForm>
			</ValidationObserver>
		</CCardBody>
		<CCardFooter v-if='responseReceived'>
			<table class='table table-striped'>
				<thead>
					<b>{{ $t('iqrfnet.standard.dali.answers') }}</b>
				</thead>
				<tbody class='text-center'>
					<tr>
						<th>{{ $t('iqrfnet.standard.dali.status') }}</th>
						<th>{{ $t('iqrfnet.standard.dali.value') }}</th>
					</tr>
					<tr v-for='i of answers.length' :key='i'>
						<td>{{ answers[i-1].status }}</td>
						<td>{{ answers[i-1].value }}</td>
					</tr>
				</tbody>
			</table>
		</CCardFooter>
	</CCard>
</template>

<script>
import {CButton, CCard, CCardBody, CCardFooter, CCardHeader, CForm, CInput} from '@coreui/vue';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {between, integer, required} from 'vee-validate/dist/rules';
import IqrfStandardService from '../../services/IqrfStandardService';

export default {
	name: 'DaliManager',
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardFooter,
		CCardHeader,
		CForm,
		CInput,
		ValidationObserver,
		ValidationProvider
	},
	data() {
		return {
			address: 1,
			answers: null,
			commands: [null],
			responseReceived: false,
			timeoutVar: null
		};
	},
	created() {
		extend('between', between);
		extend('integer', integer);
		extend('required', required);
		this.unsubscribe = this.$store.subscribe(mutation => {
			if (mutation.type === 'SOCKET_ONSEND') {
				if (mutation.payload.mType === 'iqrfDali_SendCommands') {
					this.timeoutVar = setTimeout(() => {this.timedOut();}, 10000);
				}
			}
			if (mutation.type === 'SOCKET_ONMESSAGE') {
				if (mutation.payload.mType === 'iqrfDali_SendCommands') {
					clearTimeout(this.timeoutVar);
					this.$store.commit('spinner/HIDE');
					switch(mutation.payload.data.status) {
						case -1:
							this.$toast.error(this.$t('iqrfnet.standard.dali.messages.timeout'));
							break;
						case 0:
							this.$toast.success(this.$t('iqrfnet.standard.dali.messages.success'));
							this.answers = mutation.payload.data.rsp.result.answers;
							this.responseReceived = true;
							break;
						case 3:
							this.$toast.error(this.$t('iqrfnet.standard.dali.messages.pnum'));
							break;
						default:
							this.$toast.error(this.$t('iqrfnet.standard.dali.messages.failure'));
							break;
					}
				}
			}
		});
	},
	beforeDestroy() {
		this.unsubscribe();
	},
	methods: {
		addDaliCommand() {
			this.commands.push(null);
		},
		removeDaliCommand(index) {
			this.commands.splice(index, 1);
		},
		sendDali() {
			this.$store.commit('spinner/SHOW');
			IqrfStandardService.daliSend(this.address, this.commands);
		},
		timedOut() {
			this.$store.commit('spinner/HIDE');
			this.$toast.error(this.$t('iqrfnet.networkManager.messages.submit.timeout'));
		},
	}
};
</script>
