<template>
	<CCard>
		<CCardBody>
			<CForm>
				<div class='form-group'>
					<CInputFile
						ref='configZip'
						:label='$t("config.migration.form.importButton")'
						@click='isEmpty'
						@input='isEmpty'
					/>
					<p v-if='configEmpty && !firstConfig' style='color:red'>
						{{ $t("config.migration.messages.importButton") }}
					</p>
				</div>
				<CButton color='primary' :disabled='configEmpty' @click.prevent='importConfig'>
					{{ $t('config.migration.form.import') }}
				</CButton>
				<CButton color='secondary' @click.prevent='exportConfig'>
					{{ $t('config.migration.form.export') }}
				</CButton>
			</CForm>
		</CCardBody>
	</CCard>
</template>

<script>
import {CButton, CCard, CCardBody, CForm, CInputFile} from '@coreui/vue';
import ConfigService from '../../services/ConfigService';
import { fileDownloader } from '../../helpers/fileDownloader';

export default {
	name: 'ConfigMigration',
	components: {
		CButton,
		CCard,
		CCardBody,
		CForm,
		CInputFile
	},
	data() {
		return {
			configEmpty: true,
			firstConfig: true,
		};
	},
	methods: {
		exportConfig() {
			ConfigService.exportConfig(10000)
				.then((response) => {
					let blob = new Blob([response.data], {type: 'application/zip'});
					let fileDownload = window.URL.createObjectURL(blob);
					window.open(fileDownload);
				});
		},
		importConfig() {
			//
		},
		isEmpty() {
			if (this.firstConfig) {
				this.firstConfig = false;
			}
			this.configEmpty = this.$refs.configZip.$el.children[1].files.length === 0;
		}
	}
};
</script>
