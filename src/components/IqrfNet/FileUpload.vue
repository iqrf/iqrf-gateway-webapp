<template>
	<CCard>
		<CCardHeader>{{ $t('iqrfnet.trUpload.title') }}</CCardHeader>
		<CCardBody>
			<CForm @submit.prevent='submitUpload'>
				<div class='form-group'>
					<CInputFile
						ref='fileUpload'
						accept='.hex,.iqrf,.trcnfg'
						:label='$t("iqrfnet.trUpload.file")'
						@input='isEmpty'
						@click='isEmpty'
					/>
					<p v-if='fileEmpty && !fileUntouched' style='color:red'>
						{{ $t('iqrfnet.trUpload.messages.file') }}
					</p>
				</div>
				<CSelect 
					v-if='$store.getters["user/getRole"] === "power"'
					:value.sync='format'
					:label='$t("iqrfnet.trUpload.fileFormat")'
					:options='selectOptions'
					:placeholder='$t("iqrfnet.trUpload.messages.fileFormat")'
				/>
				<CButton
					type='submit'
					color='primary'
					:disabled='fileEmpty'
				>
					{{ $t('forms.upload') }}
				</CButton>
			</CForm>
		</CCardBody>
	</CCard>
</template>

<script lang='ts'>
import Vue from 'vue';
import {MutationPayload} from 'vuex';
import {CButton, CCard, CCardBody, CCardHeader, CForm, CInputFile, CSelect} from '@coreui/vue/src';
import {FileFormat} from '../../iqrfNet/fileFormat';
import NativeUploadService from '../../services/NativeUploadService';

export default Vue.extend({
	name: 'FileUpload',
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CForm,
		CInputFile,
		CSelect
	},
	data(): any {
		return {
			fileEmpty: true,
			fileUntouched: true,
			selectOptions: [
				{value: null, label: this.$t('iqrfnet.trUpload.messages.fileFormat')},
				{value: FileFormat.HEX, label: this.$t('iqrfnet.trUpload.fileFormats.hex')},
				{value: FileFormat.IQRF, label: this.$t('iqrfnet.trUpload.fileFormats.iqrf')},
				{value: FileFormat.TRCNFG, label: this.$t('iqrfnet.trUpload.fileFormats.trcnfg')}
			],
			failed: false,
			format: null,
			requestSent: false,
			timeout: null,
		};
	},
	created() {
		this.unsubscribe = this.$store.subscribe((mutation: MutationPayload) => {
			if (mutation.type === 'SOCKET_ONSEND') {
				if (!this.requestSent) {
					return;
				}
				if (mutation.payload.mType !== 'mngDaemon_Upload') {
					return;
				}
				this.timeout = setTimeout(() => {
					this.$toast.error(
						this.$t('iqrfnet.trUpload.messages.timeout').toString()
					);
					this.failed = true;
				}, 30000);
			}
			if (mutation.type === 'SOCKET_ONMESSAGE') {
				if (this.failed) {
					return;
				}
				if (mutation.payload.mType === 'mngDaemon_Upload') {
					if (!this.requestSent) {
						return;
					}
					this.requestSent = false;
					this.$store.dispatch('spinner/hide');
					clearTimeout(this.timeout);
					if (mutation.payload.data.status === 0) {
						this.$toast.success(
							this.$t('iqrfnet.trUpload.messages.success').toString()
						);
					} else {
						this.$toast.error(
							this.$t('iqrfnet.trUpload.messages.failure').toString()
						);
					}
				}
			}
		});
	},
	beforeDestroy() {
		clearTimeout(this.timeout);
		this.unsubscribe();
	},
	methods: {
		getFiles(): FileList|null {
			const input = (this.$refs.fileUpload as CInputFile).$el.children[1] as HTMLInputElement;
			return input.files;
		},
		submitUpload() {
			this.$store.commit('spinner/SHOW');
			const formData = new FormData();
			if (this.$store.getters['user/getRole'] === 'power' && this.format !== null) {
				formData.append('format', this.format);
			}
			const files = this.getFiles();
			if (files == null || files.length === 0) {
				this.$toast.error(this.$t('iqrfnet.trUpload.messages.file').toString());
				return;
			}
			formData.append('file', this.getFiles()[0]);
			this.requestSent = true;
			NativeUploadService.uploadREST(formData)
				.then((response) => {
					this.$store.dispatch('spinner/show', {timeout: 30000});
					NativeUploadService.upload(response.data.fileName, response.data.format);
				})
				.catch(() => {
					this.$store.commit('spinner/HIDE');
					this.$toast.error(
						this.$t('iqrfnet.trUpload.messages.failure').toString()
					);
				});
		},
		isEmpty() {
			if (this.fileUntouched) {
				this.fileUntouched = false;
			}
			const files = this.getFiles();
			this.fileEmpty = files === null || files.length === 0;
		}
	},
});
</script>
