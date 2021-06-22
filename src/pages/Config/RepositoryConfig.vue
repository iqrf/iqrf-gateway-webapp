<template>
	<div>
		<h1>{{ $t('config.repository.title') }}</h1>
		<CCard>
			<CCardBody>
				<ValidationObserver v-slot='{invalid}'>
					<CForm>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='required'
							:custom-messages='{
								required: "config.repository.errors.endpointMissing"
							}'
						>
							<CInput
								v-model='config.apiEndpoint'
								:label='$t("config.repository.form.endpoint")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='$t(errors[0])'
							/>
						</ValidationProvider>
						<div class='form-group'>
							<b>
								<label>
									{{ $t('config.repository.form.credentials') }}
								</label>
							</b><br>
							<CSwitch
								:checked.sync='credentials'
								color='primary'
								size='lg'
								shape='pill'
								label-on='ON'
								label-off='OFF'
							/>
						</div>
						<div
							v-if='credentials'
						>
							<ValidationProvider
								v-slot='{errors, touched, valid}'
								rules='required'
								:custom-messages='{
									required: "forms.errors.username"
								}'
							>
								<CInput
									v-model='config.credentials.username'
									:label='$t("forms.fields.username")'
									:is-valid='touched ? valid : null'
									:invalid-feedback='$t(errors[0])'
								/>
							</ValidationProvider>
							<ValidationProvider
								v-slot='{errors, touched, valid}'
								rules='required'
								:custom-messages='{
									required: "forms.errors.password"
								}'
							>
								<CInput
									v-model='config.credentials.password'
									:label='$t("forms.fields.password")'
									:is-valid='touched ? valid : null'
									:invalid-feedback='$t(errors[0])'
								/>
							</ValidationProvider>
						</div>
						<CButton
							color='primary'
							:disabled='invalid'
							@click='saveConfig'
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
import {Component, Vue} from 'vue-property-decorator';
import {CButton, CCard, CCardBody, CForm, CInput, CSwitch} from '@coreui/vue/src';
import {extend, ValidationProvider, ValidationObserver} from 'vee-validate';

import {extendedErrorToast} from '../../helpers/errorToast';
import {required} from 'vee-validate/dist/rules';

import RepositoryConfigService from '../../services/IqrfRepository/RepositoryConfigService';

import {AxiosError, AxiosResponse} from 'axios';
import {IIqrfRepositoryConfig} from '../../interfaces/iqrfRepository';

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CForm,
		CInput,
		CSwitch,
		ValidationObserver,
		ValidationProvider
	},
	metaInfo: {
		title: 'config.repository.title',
	},
})

/**
 * IQRF repository configuration component
 */
export default class RepositoryConfig extends Vue {

	/**
	 * @var {IIqrfRepositoryConfig} config IQRF repository configuration
	 */
	private config: IIqrfRepositoryConfig = {
		apiEndpoint: 'repository.iqrfalliance.org/api',
		credentials: {
			username: '',
			password: '',
		},
	}

	/**
	 * @var {boolean} credentials Controls credentials rendering in form
	 */
	private credentials = false;

	/**
	 * Initializes validation rules
	 */
	created(): void {
		extend('required', required);
	}

	/**
	 * Retrieves IQRF repository config on load
	 */
	mounted(): void {
		this.getConfig();
	}

	/**
	 * Retrieves IQRF repository configuration
	 */
	private getConfig(): Promise<void> {
		this.$store.commit('spinner/SHOW');
		return RepositoryConfigService.get()
			.then((response: AxiosResponse) => {
				this.config = {...this.config, ...response.data};
				this.credentials = response.data.credentials !== undefined;
				this.$store.commit('spinner/HIDE');
			})
			.catch((error: AxiosError) => {
				extendedErrorToast(error, 'config.repository.messages.fetchFailed');
			});
	}

	/**
	 * Saves IQRF repository configuration
	 */
	private saveConfig(): void {
		this.$store.commit('spinner/SHOW');
		let config = this.config;
		if (!this.credentials) {
			delete config.credentials;
		}
		RepositoryConfigService.save(config)
			.then(() => {
				this.getConfig().then(() => {
					this.$store.commit('spinner/HIDE');
					this.$toast.success(
						this.$t('config.repository.messages.saveSuccess').toString()
					);
				});
			})
			.catch((error: AxiosError) => {
				extendedErrorToast(error, 'config.repository.messages.saveFailed');
			});

	}

}
</script>