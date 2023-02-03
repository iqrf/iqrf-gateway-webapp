<!--
Copyright 2017-2023 IQRF Tech s.r.o.
Copyright 2019-2023 MICRORISC s.r.o.

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
	<CCard class='p-4'>
		<h1 class='text-center'>
			{{ $t('account.recovery.title') }}
		</h1>
		<CCardBody>
			<CElementCover
				v-if='requestInProgress'
				:opacity='0.75'
				style='z-index: 10000;'
			>
				<CSpinner color='primary' />
			</CElementCover>
			<div v-if='!sent'>
				<p>
					{{ $t('account.recovery.requestPrompt') }}
				</p>
				<ValidationObserver v-slot='{invalid}'>
					<CForm @submit.prevent='requestRecovery'>
						<ValidationProvider
							v-slot='{valid, touched, errors}'
							rules='required'
							:custom-messages='{
								required: $t("core.sign.in.messages.username"),
							}'
						>
							<CInput
								v-model='user'
								:label='$t("forms.fields.username")'
								autocomplete='username'
								:is-valid='touched ? valid : null'
								:invalid-feedback='errors.join(", ")'
							/>
						</ValidationProvider>
						<CButton
							color='primary'
							type='submit'
							:disabled='invalid'
						>
							{{ $t('account.recovery.sendEmail') }}
						</CButton>
					</CForm>
				</ValidationObserver>
			</div>
			<p v-else class='text-center'>
				{{ $t('account.recovery.messages.sendSuccess') }}
			</p>
		</CCardBody>
	</CCard>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CButton, CCard, CCardBody, CForm, CInput} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import TheWizard from '@/components/TheWizard.vue';

import {extendedErrorToast} from '@/helpers/errorToast';
import {required} from 'vee-validate/dist/rules';
import UserService from '@/services/UserService';

import {AxiosError} from 'axios';

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CForm,
		CInput,
		TheWizard,
		ValidationObserver,
		ValidationProvider
	},
	metaInfo: {
		title: 'account.recovery.title'
	}
})

/**
 * Account password recovery request component
 */
export default class RequestPasswordRecovery extends Vue {

	/**
	 * @var {string} user User name to recover password for
	 */
	private user = '';

	/**
	 * @var {bool} requestInProgress Indicates whether axios requests are in progress
	 */
	private requestInProgress = false;

	/**
	 * @var {bool} sent Indicates whether or not request was successfully sent
	 */
	private sent = false;

	/**
	 * Initializes validation rules
	 */
	created(): void {
		extend('required', required);
	}

	/**
	 * Sends a request for recovery email
	 */
	private requestRecovery(): void {
		this.requestInProgress = true;
		UserService.requestPasswordRecovery(this.user)
			.then(() => {
				this.requestInProgress = false;
				this.sent = true;
			})
			.catch((error: AxiosError) => {
				this.requestInProgress = false;
				extendedErrorToast(error, 'account.recovery.messages.sendFailed');
			});
	}
}
</script>

