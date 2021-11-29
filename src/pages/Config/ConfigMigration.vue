<!--
Copyright 2017-2021 IQRF Tech s.r.o.
Copyright 2019-2021 MICRORISC s.r.o.

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
							@click='fileInputEmpty'
							@input='fileInputEmpty'
						/>
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
import { extendedErrorToast } from '../../helpers/errorToast';

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
	private configEmpty = true;

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
			})
			.catch((error: AxiosError) => extendedErrorToast(error, 'config.migration.messages.exportFailed'));
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
					this.$t('config.migration.messages.importSuccess').toString()
				);
			})
			.catch((error: AxiosError) => extendedErrorToast(error, 'config.migration.messages.importFailed'));
	}

	/**
	 * Extracts uploaded files from form configuration file input
	 * @returns {FileList} List of uploaded files
	 */
	private getFiles(): FileList {
		const input = ((this.$refs.configZip as CInputFile).$el.children[1] as HTMLInputElement);
		return (input.files as FileList);
	}

	/**
	 * Checks if form configuration file input is empty
	 */
	private fileInputEmpty(): void {
		const files = this.getFiles();
		this.configEmpty = files.length === 0;
	}

}
</script>
