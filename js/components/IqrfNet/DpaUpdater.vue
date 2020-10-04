<template>
	<CCard>
		<CCardHeader>{{ $t('iqrfnet.dpaUpload.title') }}</CCardHeader>
		<CCardBody>
			<ValidationObserver v-slot='{ invalid }'>
				<CForm>
					<ValidationProvider
						v-slot='{ valid, touched, errors }'
						rules='required'
						:custom-messages='{
							required: "iqrfnet.dpaUpload.missing.version",
						}'
					>
						<CSelect
							:value.sync='version'
							:label='$t("iqrfnet.dpaUpload.version")'
							:is-valid='touched ? valid : null'
							:invalid-feedback='$t(errors[0])'
							:placeholder='$t("iqrfnet.dpaUpload.missing.version")'
							:options='versions'
						/>
					</ValidationProvider>
					<CButton type='submit' color='primary' :disabled='invalid'>
						{{ $t('forms.upload') }}
					</CButton>
				</CForm>
			</ValidationObserver>
		</CCardBody>
	</CCard>
</template>

<script lang='ts'>
import Vue from 'vue';
import {CButton, CCard, CCardBody, CCardHeader, CForm, CSelect} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import {required} from 'vee-validate/dist/rules';
import DpaService from '../../services/IqrfRepository/DpaService';
import OsService from '../../services/DaemonApi/OsService';

export default Vue.extend({
	name: 'DpaUpdater',
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CForm,
		CSelect,
		ValidationObserver,
		ValidationProvider,
	},
	data(): any {
		return {
			address: 0,
			osBuild: undefined,
			unsubscribe: () => {},
			version: undefined,
			versions: [],
		};
	},
	created() {
		extend('required', required);
		if (this.$store.state.webSocketClient.socket.isConnected) {
			this.getOsInfo();
		}

		this.unsubscribe = this.$store.subscribe((mutation: any) => {
			if (mutation.type === 'SOCKET_ONOPEN' &&
					this.osBuild === undefined) {
				this.getOsInfo();
				return;
			}
			if (mutation.type !== 'SOCKET_ONMESSAGE' ||
					mutation.payload.mType !== 'iqrfEmbedOs_Read') {
				return;
			}
			this.handleResponse(mutation.payload);
		});
	},
	methods: {
		convertVersion(version: any): string {
			return Number.parseInt(version).toString(16).padStart(4, '0').toUpperCase();
		},
		getOsInfo() {
			this.$store.dispatch('spinner/show', {timeout: 10000});
			OsService.sendRead(this.address);
		},
		handleResponse(response: any) {
			const result = response.data.rsp.result;
			this.osBuild = this.convertVersion(result.osBuild);
			this.version = this.convertVersion(result.dpaVer);
			this.$store.dispatch('spinner/hide');
			DpaService.getVersions(this.osBuild)
				.then((versions) => {
					for (const version of versions) {
						this.versions.push({
							value: version.getVersion(false),
							label: version.getVersion(true),
						});
					}
					this.versions.sort().reverse();
				});
		},
	},
});
</script>

