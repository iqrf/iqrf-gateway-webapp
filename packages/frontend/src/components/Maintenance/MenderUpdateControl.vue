<template>
	<CCard>
		<CCard class='border-top-0 border-left-0 border-right-0 card-margin-bottom'>
			<CCardBody>
				<h4>{{ $t('maintenance.mender.update.update') }}</h4>
				<CForm>
					<CInputFile
						ref='artifactInput'
						:label='$t("maintenance.mender.update.form.artifact")'
						accept='.mender'
						@input='isInputEmpty'
						@click='isInputEmpty'
					/>
				</CForm>
				<CButton
					color='primary'
					:disabled='inputEmpty'
					@click='install'
				>
					{{ $t('maintenance.mender.update.form.install') }}
				</CButton> <CButton
					color='success'
					:disabled='!installSuccess'
					@click='commit'
				>
					{{ $t('maintenance.mender.update.form.commit') }}
				</CButton> <CButton
					color='danger'
					:disabled='!installSuccess'
					@click='rollback'
				>
					{{ $t('maintenance.mender.update.form.rollback') }}
				</CButton>
			</CCardBody>
		</CCard>
		<CCard class='border-0 card-margin-bottom'>
			<CCardBody>
				<h4>{{ $t('maintenance.mender.update.control') }}</h4>
				<CButton
					color='primary'
					@click='reboot()'
				>
					{{ $t('gateway.power.reboot') }}
				</CButton> <CButton
					v-if='$store.getters["features/isEnabled"]("remount")'
					color='primary'
					@click='remount(false)'
				>
					{{ $t('maintenance.mender.update.remountRo') }}
				</CButton> <CButton
					v-if='$store.getters["features/isEnabled"]("remount")'
					color='primary'
					@click='remount(true)'
				>
					{{ $t('maintenance.mender.update.remountRw') }}
				</CButton>
			</CCardBody>
		</CCard>
	</CCard>
</template>

<script lang='ts'>
import {CButton, CCard, CCardBody, CForm, CInputFile} from '@coreui/vue/src';
import {AxiosError, AxiosResponse} from 'axios';
import {TranslateResult} from 'vue-i18n';
import {Component, Vue} from 'vue-property-decorator';

import { extendedErrorToast } from '@/helpers/errorToast';
import {MenderMountMode} from '@/interfaces/Maintenance/Mender';
import GatewayService from '@/services/GatewayService';
import MenderService from '@/services/MenderService';
import {ErrorResponse} from '@/types';

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CForm,
		CInputFile,
	},
})

/**
 * Mender update control card component
 */
export default class MenderUpdateControl extends Vue {

	/**
	 * @var {boolean} inputEmpty Indicates that mender artifact file input is empty
	 */
	private inputEmpty = true;

	/**
	 * @var {boolean} installSuccess Indicates that mender artifact has been installed
	 */
	private installSuccess = false;

	/**
	 * Retrieves selected file from file input
	 * @return {FileList} List of uploaded files
	 */
	private getInputFile(): FileList {
		const input = ((this.$refs.artifactInput as CInputFile).$el.children[1] as HTMLInputElement);
		return (input.files as FileList);
	}

	/**
	 * Checks if artifact file input is empty
	 */
	private isInputEmpty(): void {
		this.inputEmpty = this.getInputFile().length === 0;
	}

	/**
	 * Performs mender install artifact task
	 */
	private install(): void {
		this.installSuccess = false;
		const formData = new FormData();
		const file = this.getInputFile()[0];
		formData.append('file', file);
		this.$store.commit('spinner/SHOW');
		this.$store.commit('spinner/UPDATE_TEXT', this.$t('maintenance.mender.update.messages.update').toString());
		MenderService.install(formData)
			.then((response: AxiosResponse) => {
				this.handleResponse(this.$t('maintenance.mender.update.messages.installSuccess'), response.data);
				this.installSuccess = true;
			})
			.catch((error: AxiosError) => this.handleError(
				this.$t('maintenance.mender.update.messages.installFailed'),
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
				this.$t('maintenance.mender.update.messages.commitSuccess'),
				response.data
			))
			.catch((error: AxiosError) => this.handleError(
				this.$t('maintenance.mender.update.messages.commitFailed'),
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
				this.$t('maintenance.mender.update.messages.rollbackSuccess'),
				response.data
			))
			.catch((error: AxiosError) => this.handleError(
				this.$t('maintenance.mender.update.messages.rollbackFailed'),
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
			mode: writable ? MenderMountMode.RW : MenderMountMode.RO
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
	 * @param {TranslateResult} message Toast message to project
	 * @param {string} output Output log
	 */
	private handleResponse(message: TranslateResult, output: string): void {
		this.$store.commit('spinner/HIDE');
		this.$toast.success(message.toString());
		this.updateLog(output);
	}

	/**
	 * Handles axios error
   * @param {TranslateResult} message Toast message to project
   * @param {string} output Output log
	 */
	private handleError(message: TranslateResult, output: string): void {
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
