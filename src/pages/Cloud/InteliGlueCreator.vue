<template>
	<div>
		<h1>{{ $t('cloud.intelimentsInteliGlue.form.title') }}</h1>
		<CCard>
			<CCardBody>
				<ValidationObserver v-slot='{ invalid }'>
					<CForm>
						<ValidationProvider
							v-slot='{ errors, touched, valid }'
							rules='required'
							:custom-messages='{
								required: "cloud.intelimentsInteliGlue.errors.rootTopic"
							}'
						>
							<CInput
								v-model='config.rootTopic'
								:label='$t("cloud.intelimentsInteliGlue.form.rootTopic")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<ValidationProvider
							v-slot='{ errors, touched, valid }'
							rules='required|integer|between:0,65535'
							:custom-messages='{
								between: "cloud.intelimentsInteliGlue.errors.assignedPortRange",
								integer: "cloud.intelimentsInteliGlue.errors.assignedPortRange",
								required: "cloud.intelimentsInteliGlue.errors.assignedPort"
							}'
						>
							<CInput
								v-model.number='config.assignedPort'
								type='number'
								min='0'
								max='65535'
								:label='$t("cloud.intelimentsInteliGlue.form.assignedPort")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<ValidationProvider
							v-slot='{ errors, touched, valid }'
							rules='required'
							:custom-messages='{
								required: "cloud.intelimentsInteliGlue.errors.clientId"
							}'
						>
							<CInput
								v-model='config.clientId'
								:label='$t("cloud.intelimentsInteliGlue.form.clientId")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<ValidationProvider
							v-slot='{ errors, touched, valid }'
							rules='required'
							:custom-messages='{
								required: "cloud.intelimentsInteliGlue.errors.password"
							}'
						>
							<CInput
								v-model='config.password'
								:label='$t("cloud.intelimentsInteliGlue.form.password")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
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
						</ValidationProvider>
						<CButton
							color='primary'
							:disabled='invalid'
							@click.prevent='save'
						>
							{{ $t('forms.save') }}
						</CButton> <CButton
							color='secondary'
							:disabled='invalid'
							@click.prevent='saveAndRestart'
						>
							{{ $t('forms.saveRestart') }}
						</CButton>
					</CForm>
				</ValidationObserver>
			</CCardBody>
		</CCard>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {AxiosError} from 'axios';
import {CButton, CCard, CCardBody, CForm, CInput} from '@coreui/vue/src';
import {cilLockLocked, cilLockUnlocked} from '@coreui/icons';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {between, integer, required} from 'vee-validate/dist/rules';
import FormErrorHandler from '../../helpers/FormErrorHandler';
import CloudService from '../../services/CloudService';
import ServiceService from '../../services/ServiceService';
import {Dictionary} from 'vue-router/types/router';
import {IInteliGlueCloud} from '../../interfaces/clouds';

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CForm,
		CInput,
		ValidationObserver,
		ValidationProvider,
	},
	metaInfo: {
		title: 'cloud.intelimentsInteliGlue.form.title',
	}
})

/**
 * InteliGlue inteliments cloud mqtt connection configuration creator card
 */
export default class InteliGlueCreator extends Vue {
	/**
	 * @constant {string} serviceName InteliGlue cloud service name
	 */
	private serviceName = 'inteliGlue'

	/**
	 * @var {InteliGlueConfig} config InteliGlue cloud connection configuration
	 */
	private config: IInteliGlueCloud = {
		rootTopic: '',
		assignedPort: 1234,
		clientId: '',
		password: ''
	}

	/**
	 * @constant {Dictionary<Array<string>>} icons Dictionary of CoreUI icons
	 */
	private icons: Dictionary<Array<string>> = {
		hidden: cilLockLocked,
		shown: cilLockUnlocked
	}

	/**
	 * @var {string} visibility Form password field visibility type
	 */
	private visibility = 'password'

	/**
	 * Vue lifecycle hook created
	 */
	created(): void {
		extend('between', between);
		extend('integer', integer);
		extend('required', required);
	}

	/**
	 * Stores new inteliglue cloud connection configuration in the gateway filesystem
	 * @returns {Promise<void>} Empty promise for request chaining
	 */
	private save(): Promise<void> {
		this.$store.commit('spinner/SHOW');
		return CloudService.create(this.serviceName, this.config)
			.then(() => {
				this.$store.commit('spinner/HIDE');
				this.$toast.success(this.$t('cloud.messages.success').toString());
			})
			.catch((error: AxiosError) => {
				FormErrorHandler.cloudError(error);
				return Promise.reject(error);
			});
	}

	/**
	 * Stores new inteliglue cloud connection configuration in the gateway filesystem and restarts Daemon
	 */
	private saveAndRestart(): void {
		this.save()
			.then(() => {
				this.$store.commit('spinner/SHOW');
				ServiceService.restart('iqrf-gateway-daemon')
					.then(() => {
						this.$store.commit('spinner/HIDE');
						this.$toast.success(
							this.$t('service.iqrf-gateway-daemon.messages.restart')
								.toString()
						);
					})
					.catch((error: AxiosError) => {
						FormErrorHandler.serviceError(error);
					});
			})
			.catch(() => {return;});
	}

	/**
	 * Changes password input field visibility
	 */
	private changeVisibility(): void {
		this.visibility = this.visibility === 'password' ? 'text': 'password';
	}
}
</script>
