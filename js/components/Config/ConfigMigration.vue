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
			this.$store.commit('spinner/SHOW');
			ConfigService.exportConfig(20000)
				.then((response) => {
					const file = fileDownloader(response, 'application/zip', 'iqrf-gateway-configuration_' + new Date().toISOString().replace(":", " "));
					this.$store.commit('spinner/HIDE');
					file.click();
				});
		},
		importConfig() {
			this.$store.commit('spinner/SHOW');
			ConfigService.importConfig(this.$refs.configZip.$el.children[1].files[0], 20000)
				.then(() => {
					this.$store.commit('spinner/HIDE');
					this.$toast.success(this.$t('config.migration.messages.imported'));
				})
				.catch((error) => {
					this.$store.commit('spinner/HIDE')
					if (error.response) {
						if (error.response.status === 400) {
							this.$toast.error(this.$t('config.migration.messages.invalidConfig'));
						} else if (error.response.status === 415) {
							this.$toast.error(this.$t('config.migration.messages.invalidFormat'));
						}
					} else {
						console.error(error);
					}
				});
		},
		isEmpty() {
			if (this.firstConfig) {
				this.firstConfig = false;
			}
			this.configEmpty = this.$refs.configZip.$el.children[1].files.length === 0;
		}
	},
	metaInfo: {
		title: 'config.migration.title',
	},
};
</script>
