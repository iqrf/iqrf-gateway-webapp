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
					<p v-if='configEmpty && !firstConfig' class='text-danger'>
						{{ $t("config.migration.messages.importButton") }}
					</p>
				</div>
				<CButton
					color='primary'
					:disabled='configEmpty'
					@click.prevent='importConfig'
				>
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
import {CButton, CCard, CCardBody, CForm, CInputFile} from '@coreui/vue/src';
import DaemonConfigurationService	from '../../services/DaemonConfigurationService';
import {fileDownloader} from '../../helpers/fileDownloader';

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
			DaemonConfigurationService.export()
				.then((response) => {
					const fileName = 'iqrf-gateway-configuration_' + new Date().toISOString();
					const file = fileDownloader(response, 'application/zip', fileName);
					this.$store.commit('spinner/HIDE');
					file.click();
				});
		},
		importConfig() {
			this.$store.commit('spinner/SHOW');
			DaemonConfigurationService.import(this.$refs.configZip.$el.children[1].files[0])
				.then(() => {
					this.$store.commit('spinner/HIDE');
					this.$toast.success(
						this.$t('config.migration.messages.imported').toString()
					);
				})
				.catch((error) => {
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
