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
		<h1>{{ $t('config.daemon.main.title') }}</h1>
		<CCard body-wrapper>
			<CForm @submit.prevent='saveConfig'>
				<CFormInput
					v-model='configuration.applicationName'
					:label='$t("config.daemon.main.form.applicationName")'
				/>
				<CFormInput
					v-model='configuration.resourceDir'
					:label='$t("config.daemon.main.form.resourceDir")'
				/>
				<CFormInput
					v-model='configuration.dataDir'
					:label='$t("config.daemon.main.form.dataDir")'
				/>
				<CFormInput
					v-model='configuration.cacheDir'
					:label='$t("config.daemon.main.form.cacheDir")'
				/>
				<CFormInput
					v-model='configuration.userDir'
					:label='$t("config.daemon.main.form.userDir")'
				/>
				<CFormInput
					v-model='configuration.configurationDir'
					:label='$t("config.daemon.main.form.configurationDir")'
				/>
				<CFormInput
					v-model='configuration.deploymentDir'
					:label='$t("config.daemon.main.form.deploymentDir")'
				/>
				<CButton type='submit' color='primary'>
					{{ $t('forms.save') }}
				</CButton>
			</CForm>
		</CCard>
	</div>
</template>

<script lang='ts'>
import {Options, Vue} from 'vue-property-decorator';
import {CButton, CCard, CForm, CFormInput} from '@coreui/vue/src';

import {extendedErrorToast} from '../../helpers/errorToast';
import DaemonConfigurationService from '../../services/DaemonConfigurationService';

import {AxiosError, AxiosResponse} from 'axios';
import {IMainConfig} from '../../interfaces/daemonComponent';

@Options({
	components: {
		CButton,
		CCard,
		CForm,
		CFormInput,
	},
	metaInfo: {
		title: 'config.main.title',
	}
})

/**
 * IQRF Gateway Daemon main configuration component
 */
export default class MainConfiguration extends Vue {
	/**
	 * @var {IMainConfig} configuration Daemon main configuration
	 */
	private configuration: IMainConfig = {
		applicationName: '',
		resourceDir: '',
		cacheDir: '',
		configurationDir: '',
		dataDir: '',
		deploymentDir: '',
		userDir: ''
	}

	/**
	 * Vue lifecycle hook mounted
	 */
	mounted(): void {
		this.getConfig();
	}

	/**
	 * Retrieves main configuration of IQRF Gateway Daemon
	 */
	private getConfig(): void {
		this.$store.commit('spinner/SHOW');
		DaemonConfigurationService.getComponent('')
			.then((response: AxiosResponse) =>  {
				this.$store.commit('spinner/HIDE');
				this.configuration = response.data;
			})
			.catch((error: AxiosError) => extendedErrorToast(error, 'config.daemon.main.messages.fetchFailed'));
	}

	/**
	 * Updates main configuration of IQRF Gateway Daemon
	 */
	private saveConfig(): void {
		this.$store.commit('spinner/SHOW');
		DaemonConfigurationService.updateComponent('', this.configuration)
			.then(() => {
				this.$store.commit('spinner/HIDE');
				this.$toast.success(
					this.$t('config.daemon.main.messages.saveSuccess').toString()
				);
			})
			.catch((error: AxiosError) => extendedErrorToast(error, 'config.daemon.main.messages.saveFailed'));
	}
}
</script>
