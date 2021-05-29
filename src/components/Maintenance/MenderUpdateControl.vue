<template>
	<CCard>
		<CCardBody>
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
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CButton, CCard, CCardBody, CInputFile} from '@coreui/vue/src';

import MenderService from '../../services/MenderService';

import {AxiosError, AxiosResponse} from 'axios';

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
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
	private inputEmpty = true

	/**
	 * @var {boolean} installSuccess Indicates that mender artifact has been installed
	 */
	private installSuccess = false

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
				this.handleResponse('maintenance.mender.update.messages.installSuccess', response.data);
				this.installSuccess = true;
			})
			.catch((error: AxiosError) => this.handleError(
				'maintenance.mender.update.messages.installFailed',
				error.response ? error.response.data : error.message
			));
	}

	/**
	 * Performs mender commit task
	 */
	private commit(): void {
		this.$store.commit('spinner/SHOW');
		MenderService.commit()
			.then((response: AxiosResponse) => this.handleResponse(
				'maintenance.mender.update.messages.commitSuccess',
				response.data
			))
			.catch((error: AxiosError) => this.handleError(
				'maintenance.mender.update.messages.commitFailed',
				error.response ? error.response.data.message : error.message
			));
	}

	/**
	 * Performs mender rollback task
	 */
	private rollback(): void {
		this.$store.commit('spinner/SHOW');
		MenderService.rollback()
			.then((response: AxiosResponse) => this.handleResponse(
				'maintenance.mender.update.messages.rollbackSuccess',
				response.data
			))
			.catch((error: AxiosError) => this.handleError(
				'maintenance.mender.update.messages.rollbackFailed',
				error.response ? error.response.data.message : error.message
			));
	}

	/**
	 * Handles axios response
	 * @param {string} message Toast message to project
	 * @param {string} output Output log
	 */
	private handleResponse(message: string, output: string): void {
		this.$store.commit('spinner/HIDE');
		this.$toast.success(this.$t(message).toString());
		this.updateLog(output);
	}

	/**
	 * Handles axios error
	 */
	private handleError(message: string, output: string): void {
		this.$store.commit('spinner/HIDE');
		this.$toast.error(this.$t(message).toString());
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
