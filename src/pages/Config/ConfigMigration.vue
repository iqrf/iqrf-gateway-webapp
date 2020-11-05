<template>
	<div>
		<h1>{{ $t('config.migration.title') }}</h1>
		<CCard>
			<CCardBody>
				<CForm>
					<div class='form-group'>
						<CInputFile
							ref='configZip'
							accept='.zip'
							:label='$t("config.migration.form.importButton")'
							@click='isEmpty'
							@input='isEmpty'
						/>
						<p v-if='configEmpty && !configUntouched' class='text-danger'>
							{{ $t("config.migration.messages.importButton") }}
						</p>
					</div>
					<CButton
						color='primary'
						:disabled='configEmpty'
						@click.prevent='importConfig'
					>
						{{ $t('config.migration.form.import') }}
					</CButton> <CButton
						color='secondary'
						@click.prevent='exportConfig'
					>
						{{ $t('config.migration.form.export') }}
					</CButton>
				</CForm>
			</CCardBody>
		</CCard>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {AxiosError, AxiosResponse} from 'axios';
import {CButton, CCard, CCardBody, CCardHeader, CForm, CInputFile} from '@coreui/vue/src';
import DaemonConfigurationService	from '../../services/DaemonConfigurationService';
import {fileDownloader} from '../../helpers/fileDownloader';

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CForm,
		CInputFile
	},
	metaInfo: {
		title: 'config.migration.title',
	},
})

/**
 * Daemon configuration migration card
 */
export default class ConfigMigration extends Vue {
	/**
	 * @var {boolean} configEmpty Indicates whether form configuration file input is empty
	 */
	private configEmpty = true

	/**
	 * @var {boolean} configUntouched Indicates whether form configuration file input has been interacted with
	 */
	private configUntouched = true

	/**
	 * Extracts uploaded files from form configuration file input
	 * @returns {FileList} List of uploaded files
	 */
	private getFiles(): FileList {
		const input = ((this.$refs.configZip as CInputFile).$el.children[1] as HTMLInputElement);
		return (input.files as FileList);
	}

	/**
	 * Exports Daemon configuration
	 */
	private exportConfig(): void {
		this.$store.commit('spinner/SHOW');
		DaemonConfigurationService.export()
			.then((response: AxiosResponse) => {
				const fileName = 'iqrf-gateway-configuration_' + new Date().toISOString();
				const file = fileDownloader(response, 'application/zip', fileName);
				this.$store.commit('spinner/HIDE');
				file.click();
			});
	}

	/**
	 * Imports uploaded Daemon configuration
	 */
	private importConfig(): void {
		this.$store.commit('spinner/SHOW');
		const files = this.getFiles();
		if (files === null || files.length === 0) {
			this.$toast.error(
				this.$t('config.migration.messages.importButton').toString()
			);
			return;
		}
		DaemonConfigurationService.import(files[0])
			.then(() => {
				this.$store.commit('spinner/HIDE');
				this.$toast.success(
					this.$t('config.migration.messages.imported').toString()
				);
			})
			.catch((error: AxiosError) => {
				this.$store.commit('spinner/HIDE');
				if (error.response === undefined) {
					console.error(error);
					return;
				}
				if (error.response.status === 400) {
					this.$toast.error(
						this.$t('config.migration.messages.invalidConfig').toString()
					);
				} else if (error.response.status === 415) {
					this.$toast.error(
						this.$t('config.migration.messages.invalidFormat').toString()
					);
				}
			});
	}

	/**
	 * Checks if form configuration file input is empty
	 */
	private isEmpty(): void {
		if (this.configUntouched) {
			this.configUntouched = false;
		}
		const files = this.getFiles();
		this.configEmpty = files === null || files.length === 0;
	}

}
</script>
