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
			>
				{{ $t('maintenance.mender.update.form.commit') }}
			</CButton> <CButton
				color='danger'
				:disabled='!installSuccess'
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
import { AxiosError, AxiosResponse } from 'axios';
import { extendedErrorToast } from '../../helpers/errorToast';

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
		MenderService.install(formData)
			.then((response: AxiosResponse) => {
				this.$store.commit('spinner/HIDE');
				this.updateLog(response.data);
				this.installSuccess = true;
			})
			.catch((error: AxiosError) => {
				this.$store.commit('spinner/HIDE');
				this.$toast.error(
					this.$t('maintenance.mender.update.messages.installFailed').toString()
				);
				this.updateLog(error.response ? error.response.data : error.message);
			});
	}

	/**
	 * Updates execution log in the log component
	 */
	private updateLog(log: string) {
		this.$emit('update-log', log);
	}
}
</script>
