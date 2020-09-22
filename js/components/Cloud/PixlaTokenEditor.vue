<template>
	<ValidationObserver v-slot='{ invalid }'>
		<CForm @submit.prevent='processSubmit'>
			<CModal
				color='primary'
				:show.sync='show'
			>
				<template #header>
					<h5 class='modal-title'>
						{{ $t('cloud.pixla.editModal.title') }}
					</h5>
					<CButtonClose class='text-white' @click='close' />
				</template>
				<ValidationProvider
					v-slot='{ errors, touched, valid }'
					rules='required'
					:custom-messages='{
						required: "cloud.pixla.editModal.messages.token"
					}'
				>
					<CInput
						v-model='token'
						:label='$t("cloud.pixla.editModal.token")'
						:is-valid='touched ? valid : null'
						:invalid-feedback='$t(errors[0])'
					/>
				</ValidationProvider>
				<template #footer>
					<CButton color='danger' @click='close'>
						{{ $t('forms.cancel') }}
					</CButton>
					<CButton color='success' type='submit' :disabled='invalid'>
						{{ $t('forms.edit') }}
					</CButton>
				</template>
			</CModal>
		</CForm>
	</ValidationObserver>
</template>

<script>
import {CButton, CForm, CInput, CModal} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {required} from 'vee-validate/dist/rules';
import PixlaService from '../../services/PixlaService';

export default {
	name: 'PixlaTokenEditor',
	components: {
		CButton,
		CForm,
		CInput,
		CModal,
		ValidationObserver,
		ValidationProvider,
	},
	props: {
		show: {
			type: Boolean,
			required: true,
		}
	},
	data() {
		return {
			token: '',
		};
	},
	created() {
		extend('required', required);
	},
	methods: {
		close() {
			this.$emit('update:show', false);
		},
		processSubmit() {
			PixlaService.setToken(this.token)
				.then(() => {
					this.token = null;
					this.$store.commit('spinner/SHOW');
					this.$emit('update:show', false);
					this.$emit('tokenUpdated');
					this.$toast.success(this.$t('cloud.pixla.editModal.messages.success').toString());
				})
				.catch(() => {
					this.$toast.error(this.$t('cloud.pixla.editModal.messages.failure').toString());
				});
		}
	},
};
</script>
