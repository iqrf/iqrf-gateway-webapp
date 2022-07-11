<template>
	<div>
		<v-card>
			<v-card-text>
				<h4>{{ $t('maintenance.mender.update.update') }}</h4>
				<ValidationObserver v-slot='{invalid}'>
					<form>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='required'
							:custom-messages='{
								required: $t("maintenance.mender.update.messages.missingArtifact"),
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
					</form>
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
		<v-card>
			<v-card-text>
				<h4>{{ $t('maintenance.mender.update.control') }}</h4>
				<v-btn
					color='primary'
					@click='reboot()'
				>
					{{ $t('gateway.power.reboot') }}
				</v-btn> <v-btn
					v-if='$store.getters["features/isEnabled"]("remount")'
					color='primary'
					@click='remount(false)'
				>
					{{ $t('maintenance.mender.update.remountRo') }}
				</v-btn> <v-btn
					v-if='$store.getters["features/isEnabled"]("remount")'
					color='primary'
					@click='remount(true)'
				>
					{{ $t('maintenance.mender.update.remountRw') }}
				</v-btn>
			</v-card-text>
		</v-card>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {required} from 'vee-validate/dist/rules';


import MenderService from '@/services/MenderService';

import {AxiosError, AxiosResponse} from 'axios';
import GatewayService from '@/services/GatewayService';
import { MountModes } from '@/enums/Maintenance/Mender';
import { extendedErrorToast } from '@/helpers/errorToast';
import {ErrorResponse} from '@/types';

@Component({
	components: {
		ValidationObserver,
		ValidationProvider,
	},
})

/**
 * Mender update control card component
 */
export default class MenderUpdateControl extends Vue {

	/**
	 * @var {File|null} file Artifact input file
	 */
	private file: File|null = null;

	/**
	 * @var {boolean} installSuccess Indicates that mender artifact has been installed
	 */
	private installSuccess = false;

	/**
	 * Vue lifecycle hook created
	 */
	created(): void {
		extend('required', required);
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
			.catch((error: AxiosError) => this.handleError(
				this.$t('maintenance.mender.update.messages.installFailed').toString(),
				error.response ? (error.response.data as ErrorResponse).message : error.message
			));
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
			.catch((error: AxiosError) => this.handleError(
				this.$t('maintenance.mender.update.messages.commitFailed').toString(),
				error.response ? (error.response.data as ErrorResponse).message : error.message
			));
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
			.catch((error: AxiosError) => this.handleError(
				this.$t('maintenance.mender.update.messages.rollbackFailed').toString(),
				error.response ? (error.response.data as ErrorResponse).message : error.message
			));
	}

	/**
	 * Performs reboot after commit
	 */
	private reboot(): void {
		this.$store.commit('spinner/SHOW');
		GatewayService.performReboot()
			.then((response: AxiosResponse) => {
				const time = new Date(response.data.timestamp * 1000).toLocaleTimeString();
				this.$store.commit('spinner/HIDE');
				this.$toast.success(
					this.$t(
						'gateway.power.messages.rebootSuccess',
						{time: time},
					).toString()
				);
			});
	}

	/**
	 * Remounts filesystem
	 * @param {boolean} writable Make filesystem writable
	 */
	private remount(writable: boolean): void {
		const conf = {
			mode: writable ? MountModes.RW : MountModes.RO
		};
		this.$store.commit('spinner/SHOW');
		MenderService.remount(conf)
			.then(() => {
				this.$store.commit('spinner/HIDE');
				this.$toast.success(
					this.$t('maintenance.mender.update.messages.remountSuccess').toString()
				);
			})
			.catch((error: AxiosError) => extendedErrorToast(
				error,
				'maintenance.mender.update.messages.remountFailed',
			));
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
	 * Handles axios error
   * @param {string} message Toast message to project
   * @param {string} output Output log
	 */
	private handleError(message: string, output: string): void {
		this.$store.commit('spinner/HIDE');
		this.$toast.error(message.toString());
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
