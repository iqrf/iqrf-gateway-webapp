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
	<v-card class='p-4'>
		<v-card-title>{{ $t('account.recovery.title') }}</v-card-title>
		<v-card-text>
			<v-overlay
				v-if='requestInProgress'
				:opacity='0.65'
				absolute
			>
				<v-progress-circular color='primary' indeterminate />
			</v-overlay>
			<div v-if='!sent'>
				<p>
					{{ $t('account.recovery.requestPrompt') }}
				</p>
				<ValidationObserver v-slot='{invalid}'>
					<form @submit.prevent='requestRecovery'>
						<ValidationProvider
							v-slot='{valid, touched, errors}'
							rules='required'
							:custom-messages='{
								required: $t("core.sign.in.messages.username"),
							}'
						>
							<v-text-field
								v-model='user'
								:label='$t("forms.fields.username")'
								autocomplete='username'
								:success='touched ? valid : null'
								:error-messages='errors'
							/>
						</ValidationProvider>
						<v-btn
							color='primary'
							type='submit'
							:disabled='invalid'
						>
							{{ $t('account.recovery.sendEmail') }}
						</v-btn>
					</form>
				</ValidationObserver>
			</div>
			<p v-else class='text-center'>
				{{ $t('account.recovery.messages.sendSuccess') }}
			</p>
		</v-card-text>
	</v-card>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import TheWizard from '@/components/TheWizard.vue';

import {extendedErrorToast} from '@/helpers/errorToast';
import {required} from 'vee-validate/dist/rules';
import UserService from '@/services/UserService';

import {AxiosError} from 'axios';

@Component({
	components: {
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

