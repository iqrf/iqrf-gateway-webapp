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
		<h1>{{ $t('cloud.msAzure.form.title') }}</h1>
		<CCard>
			<CCardHeader>
				<CButton
					color='primary'
					size='sm'
					href='https://github.com/iqrfsdk/iot-starter-kit/blob/master/install/pdf/iqrf-part3b.pdf'
				>
					<CIcon :content='cilFile' size='sm' />
					{{ $t('cloud.guides.pdf') }}
				</CButton> <CButton
					color='danger'
					size='sm'
					href='https://youtu.be/SIBoTrYwR2g'
				>
					<CIcon :content='cibYoutube' size='sm' />
					{{ $t('cloud.guides.video') }}
				</CButton>
			</CCardHeader>
			<CCardBody>
				<ValidationObserver v-slot='{invalid}'>
					<CForm>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='required'
							:custom-messages='{
								required: $t("cloud.msAzure.errors.connectionString"),
							}'
						>
							<CInput
								v-model='connectionString'
								:label='$t("cloud.msAzure.form.connectionString")'
								:is-valid='touched ? valid : null'
								:invalid-feedback='errors.join(", ")'
							/>
						</ValidationProvider>
						<CButton
							color='primary'
							:disabled='invalid'
							@click.prevent='save(false)'
						>
							{{ $t('forms.save') }}
						</CButton> <CButton
							color='secondary'
							:disabled='invalid'
							@click.prevent='save(true)'
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
import {CButton, CCard, CCardBody, CCardHeader, CForm, CInput} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';

import {cibYoutube, cilFile} from '@coreui/icons';
import {daemonErrorToast, extendedErrorToast} from '@/helpers/errorToast';
import {required} from 'vee-validate/dist/rules';
import CloudService from '@/services/CloudService';
import ServiceService from '@/services/ServiceService';

import {AxiosError} from 'axios';

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CForm,
		CInput,
		ValidationObserver,
		ValidationProvider
	},
	data: () => ({
		cibYoutube,
		cilFile,
	}),
	metaInfo: {
		title: 'cloud.msAzure.form.title',
	},
})

/**
 * Azure cloud mqtt connection configuration creator card
 */
export default class AzureCreator extends Vue {
	/**
	 * @var {string} connectionString Azure cloud connection string
	 */
	private connectionString = '';

	/**
	 * @constant {string} serviceName Azure cloud service name
	 */
	private serviceName = 'azure';

	/**
	 * Vue lifecycle hook created
	 */
	created(): void {
		extend('required', required);
	}

	/**
	 * Stores new Azure cloud connection configuration in the gateway filesystem
	 * @param {boolean} restart Restart daemon on save?
	 */
	private save(restart: boolean): void {
		this.$store.commit('spinner/SHOW');
		CloudService.create(this.serviceName, {'connectionString': this.connectionString})
			.then(async () => {
				if (restart) {
					await ServiceService.restart('iqrf-gateway-daemon')
						.then(() => {
							this.$toast.success(
								this.$t('service.iqrf-gateway-daemon.messages.restart')
									.toString()
							);
						})
						.catch((error: AxiosError) => daemonErrorToast(error, 'service.messages.restartFailed'));
				}
				this.$store.commit('spinner/HIDE');
				this.$toast.success(this.$t('cloud.messages.success').toString());
			})
			.catch((error: AxiosError) => {
				extendedErrorToast(error, 'cloud.msAzure.messages.saveFailed');
			});
	}

}
</script>
