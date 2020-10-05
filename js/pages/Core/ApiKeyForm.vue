<template>
	<CCard>
		<CCardBody>
			<ValidationObserver v-slot='{ invalid }'>
				<CForm @submit.prevent='saveKey'>
					<ValidationProvider
						v-slot='{ errors, touched, valid }'
						rules='required'
						:custom-messages='{required: "core.apiKey.form.messages.description"}'
					>
						<CInput
							v-model='metadata.description'
							:label='$t("core.apiKey.form.description")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
						/>
					</ValidationProvider>
					<div class='form-group'>
						<CInputCheckbox
							:checked.sync='useExpiration'
							:label='$t("core.apiKey.form.expiration")'
							@change='clear'
						/>
						<Datetime
							v-model='metadata.expiration'
							type='datetime'
							:format='dateFormat'
							:min-datetime='new Date().toISOString()'
							input-class='form-control'
							:disabled='!useExpiration'
						/>
					</div>
					<CButton
						type='submit'
						color='primary'
						:disabled='invalid'
					>
						{{ submitButton }}
					</CButton>
				</CForm>
			</ValidationObserver>
		</CCardBody>
	</CCard>
</template>

<script>
import {CButton, CCard, CCardBody, CForm, CInput, CInputCheckbox} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {required} from 'vee-validate/dist/rules';
import ApiKeyService from '../../services/ApiKeyService';
import {Datetime} from 'vue-datetime';
import FormErrorHandler from '../../helpers/FormErrorHandler';

export default {
	name: 'ApiKeyForm',
	components: {
		CButton,
		CCard,
		CCardBody,
		CForm,
		CInput,
		CInputCheckbox,
		Datetime,
		ValidationObserver,
		ValidationProvider,
	},
	props: {
		keyId: {
			type: Number,
			required: false,
			default: null,
		}
	},
	data() {
		return {
			metadata: {
				description: null,
				expiration: null,
			},
			useExpiration: false,
			dateFormat: {
				year: 'numeric',
				month: 'short',
				day: 'numeric',
				hour12: false,
				hour: 'numeric',
				minute: 'numeric',
				second: 'numeric',
			},
		};
	},
	computed: {
		submitButton() {
			return this.$route.path === '/api-key/add' ?
				this.$t('forms.add') : this.$t('forms.edit');
		},
	},
	created() {
		extend('required', required);
		if (this.keyId) {
			this.getKey();
		}
	},
	methods: {
		clear() {
			if (!this.useExpiration) {
				this.metadata.expiration = null;
			}
		},
		getKey() {
			this.$store.commit('spinner/SHOW');
			ApiKeyService.getApiKey(this.keyId)
				.then((response) => {
					this.$store.commit('spinner/HIDE');
					this.metadata = response.data;
					if (this.metadata.expiration !== null) {
						this.useExpiration = true;
					}
				})
				.catch((error) => {
					this.$router.push('/api-key/');
					FormErrorHandler.apiKeyError(error);
				});
		},
		saveKey() {
			this.$store.commit('spinner/SHOW');
			if (this.keyId !== null) {
				ApiKeyService.editApiKey(this.keyId, this.metadata)
					.then(() => this.successfulSave())
					.catch((error) => FormErrorHandler.apiKeyError(error));
			} else {
				ApiKeyService.addApiKey(this.metadata)
					.then(() => this.successfulSave())
					.catch((error) => FormErrorHandler.apiKeyError(error));
			}
		},
		successfulSave() {
			this.$router.push('/api-key');
			this.$store.commit('spinner/HIDE');
			if (this.$route.path === '/api-key/add') {
				this.$toast.success(
					this.$t('core.apiKey.messages.addSuccess')
						.toString()
				);
			} else {
				this.$toast.success(
					this.$t('core.apiKey.messages.editSuccess', {key: this.keyId})
						.toString()
				);
			}
		}
	},
	metaInfo() {
		return {
			title: this.$route.path === '/api-key/add' ?
				this.$t('core.apiKey.add').toString() :
				this.$t('core.apiKey.edit').toString()
		};
	}
};
</script>
