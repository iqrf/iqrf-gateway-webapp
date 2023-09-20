<!--
Copyright 2017-2023 IQRF Tech s.r.o.
Copyright 2019-2023 MICRORISC s.r.o.

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
		<v-card>
			<v-card-text>
				<v-form @submit.prevent='saveConfig'>
					<v-text-field
						v-model='configuration.applicationName'
						:label='$t("config.daemon.main.form.applicationName")'
					/>
					<v-text-field
						v-model='configuration.resourceDir'
						:label='$t("config.daemon.main.form.resourceDir")'
					/>
					<v-text-field
						v-model='configuration.dataDir'
						:label='$t("config.daemon.main.form.dataDir")'
					/>
					<v-text-field
						v-model='configuration.cacheDir'
						:label='$t("config.daemon.main.form.cacheDir")'
					/>
					<v-text-field
						v-model='configuration.userDir'
						:label='$t("config.daemon.main.form.userDir")'
					/>
					<v-text-field
						v-model='configuration.configurationDir'
						:label='$t("config.daemon.main.form.configurationDir")'
					/>
					<v-text-field
						v-model='configuration.deploymentDir'
						:label='$t("config.daemon.main.form.deploymentDir")'
					/>
					<v-btn
						type='submit'
						color='primary'
					>
						{{ $t('forms.save') }}
					</v-btn>
				</v-form>
			</v-card-text>
		</v-card>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';

import {extendedErrorToast} from '@/helpers/errorToast';
import DaemonConfigurationService from '@/services/DaemonConfigurationService';

import {AxiosError, AxiosResponse} from 'axios';
import {IMainConfig} from '@/interfaces/Config/Daemon';

@Component({
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
	};

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
