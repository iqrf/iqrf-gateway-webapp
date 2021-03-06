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
	<ValidationObserver
		v-if='token !== null'
		v-slot='{invalid}'
	>
		<hr>
		<CForm @submit.prevent='saveToken'>
			<ValidationProvider
				v-slot='{ errors, touched, valid }'
				rules='required'
				:custom-messages='{
					required: "maintenance.pixla.errors.token"
				}'
			>
				<CInput
					v-model='token'
					:label='$t("maintenance.pixla.form.token")'
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
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CButton, CForm, CInput, CModal} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';

import {required} from 'vee-validate/dist/rules';
import {extendedErrorToast} from '../../helpers/errorToast';

import PixlaService from '../../services/PixlaService';

import {AxiosError, AxiosResponse} from 'axios';

@Component({
	components: {
		CButton,
		CForm,
		CInput,
		CModal,
		ValidationObserver,
		ValidationProvider,
	},
})

/**
 * Pixla form component
 */
export default class PixlaForm extends Vue {
	/**
	 * @var {string|null} token pixla token
	 */
	private token: string|null = null
	
	/**
	 * Initializes validation rules
	 */
	created(): void {
		extend('required', required);
	}

	/**
	 * Retrieves pixla token
	 */
	mounted(): void {
		this.getToken();
	}

	/**
	 * Retrieves the Pixla service token
	 */
	private getToken(): void {
		if (this.$store.getters['spinner/isEnabled']) {
			this.$store.commit('spinner/SHOW');
		}
		PixlaService.getToken()
			.then((response: AxiosResponse) => {
				this.token = response.data.token;
				this.$store.commit('spinner/HIDE');
			})
			.catch((error: AxiosError) => extendedErrorToast(error, 'maintenance.pixla.messages.fetchFailed'));
	}

	/**
	 * Sends REST API request to update pixla token
	 */
	private saveToken(): void {
		if (this.token === null) {
			return;
		}
		this.$store.commit('spinner/SHOW');
		PixlaService.setToken(this.token)
			.then(() => {
				this.$store.commit('spinner/HIDE');
				this.$toast.success(
					this.$t('maintenance.pixla.messages.success').toString()
				);
			})
			.catch((error: AxiosError) => extendedErrorToast(error, 'maintenance.pixla.messages.failure'));
	}
}
</script>
