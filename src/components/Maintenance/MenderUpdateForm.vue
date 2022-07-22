<template>
	<v-card>
		<v-card-text>
			<h4>{{ $t('maintenance.mender.update.update') }}</h4>
			<ValidationObserver v-slot='{invalid}'>
				<v-form>
					<ValidationProvider
						v-slot='{errors, touched, valid}'
						rules='required|artifact'
						:custom-messages='{
							required: $t("maintenance.mender.update.errors.missingArtifact"),
							artifact: $t("maintenance.mender.update.errors.invalidArtifact"),
						}'
					>
						<v-file-input
							v-model='file'
							:label='$t("maintenance.mender.update.form.artifact")'
							accept='.mender'
							:success='touched ? valid : null'
							:error-messages='errors'
						/>
					</ValidationProvider>
				</v-form>
				<v-btn
					color='primary'
					:disabled='invalid'
					@click='install'
				>
					{{ $t('maintenance.mender.update.form.install') }}
				</v-btn> <v-btn
					color='success'
					:disabled='!installSuccess'
					@click='commit'
				>
					{{ $t('maintenance.mender.update.form.commit') }}
				</v-btn> <v-btn
					color='error'
					:disabled='!installSuccess'
					@click='rollback'
				>
					{{ $t('maintenance.mender.update.form.rollback') }}
				</v-btn>
			</ValidationObserver>
		</v-card-text>
	</v-card>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';

import {extendedErrorToast} from '@/helpers/errorToast';
import {required} from 'vee-validate/dist/rules';

import MenderService from '@/services/MenderService';

import {AxiosError, AxiosResponse} from 'axios';
import {ErrorResponse} from '@/types';

@Component({
	components: {
		ValidationObserver,
		ValidationProvider,
	},
})

/**
 * Mender update form component
 */
export default class MenderUpdateForm extends Vue {

	/**
	 * @var {File|null} file Artifact input file
	 */
	private file: File|null = null;

	/**
	 * @var {boolean} installSuccess Indicates that mender artifact has been installed
	 */
	private installSuccess = false;

	/**
	 * Initializes validation rules
	 */
	created(): void {
		extend('required', required);
		extend('artifact', (file: File|null) => {
			if (!file) {
				return false;
			}
			return file.name.endsWith('.mender');
		});
	}

	/**
	 * Performs mender install artifact task
	 */
	private install(): void {
		if (this.file === null) {
			return;
		}
		this.installSuccess = false;
		const formData = new FormData();
		formData.append('file', this.file);
		this.$store.commit('spinner/SHOW');
		this.$store.commit('spinner/UPDATE_TEXT', this.$t('maintenance.mender.update.messages.update').toString());
		MenderService.install(formData)
			.then((response: AxiosResponse) => {
				this.handleResponse(this.$t('maintenance.mender.update.messages.installSuccess').toString(), response.data);
				this.installSuccess = true;
			})
			.catch((error: AxiosError) => {
				extendedErrorToast(error, 'maintenance.mender.update.messages.installFailed');
				this.updateLog(error.response ? (error.response.data as ErrorResponse).message : error.message);
			});
	}

	/**
	 * Performs mender commit task
	 */
	private commit(): void {
		this.$store.commit('spinner/SHOW');
		MenderService.commit()
			.then((response: AxiosResponse) => this.handleResponse(
				this.$t('maintenance.mender.update.messages.commitSuccess').toString(),
				response.data
			))
			.catch((error: AxiosError) => {
				extendedErrorToast(error, 'maintenance.mender.update.messages.commitFailed');
				this.updateLog(error.response ? (error.response.data as ErrorResponse).message : error.message);
			});
	}

	/**
	 * Performs mender rollback task
	 */
	private rollback(): void {
		this.$store.commit('spinner/SHOW');
		MenderService.rollback()
			.then((response: AxiosResponse) => this.handleResponse(
				this.$t('maintenance.mender.update.messages.rollbackSuccess').toString(),
				response.data
			))
			.catch((error: AxiosError) =>{
				extendedErrorToast(error, 'maintenance.mender.update.messages.rollbackFailed');
				this.updateLog(error.response ? (error.response.data as ErrorResponse).message : error.message);
			});
	}

	/**
	 * Handles axios response
	 * @param {string} message Toast message to project
	 * @param {string} output Output log
	 */
	private handleResponse(message: string, output: string): void {
		this.$store.commit('spinner/HIDE');
		this.$toast.success(message);
		this.updateLog(output);
	}

	/**
	 * Updates execution log in the log component
	 */
	private updateLog(log: string) {
		this.$emit('update-log', log);
	}
}
</script>
