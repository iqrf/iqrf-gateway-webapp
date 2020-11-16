<template>
	<div>
		<h1>{{ $t('config.daemon.main.title') }}</h1>
		<CCard body-wrapper>
			<CForm @submit.prevent='saveConfig'>
				<CInput
					v-model='configuration.applicationName'
					:label='$t("config.daemon.main.form.applicationName")'
				/>
				<CInput
					v-model='configuration.resourceDir'
					:label='$t("config.daemon.main.form.resourceDir")'
				/>
				<CInput
					v-model='configuration.dataDir'
					:label='$t("config.daemon.main.form.dataDir")'
				/>
				<CInput
					v-model='configuration.cacheDir'
					:label='$t("config.daemon.main.form.cacheDir")'
				/>
				<CInput
					v-model='configuration.userDir'
					:label='$t("config.daemon.main.form.userDir")'
				/>
				<CInput
					v-model='configuration.configurationDir'
					:label='$t("config.daemon.main.form.configurationDir")'
				/>
				<CInput
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
import {Component, Vue} from 'vue-property-decorator';
import {AxiosError, AxiosResponse} from 'axios';
import {CButton, CCard, CForm, CInput} from '@coreui/vue/src';
import DaemonConfigurationService from '../../services/DaemonConfigurationService';
import FormErrorHandler from '../../helpers/FormErrorHandler';

interface IMainConfig {
	applicationName: string
	resourceDir: string
	cacheDir: string
	configurationDir: string
	dataDir: string
	deploymentDir: string
	userDir: string
}

@Component({
	components: {
		CButton,
		CCard,
		CForm,
		CInput,
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
			.catch((error: AxiosError) => FormErrorHandler.configError(error));
	}

	/**
	 * Updates main configuration of IQRF Gateway Daemon
	 */
	private saveConfig(): void {
		this.$store.commit('spinner/SHOW');
		DaemonConfigurationService.updateComponent('', this.configuration)
			.then(() => this.successfulSave())
			.catch((error: AxiosError) => FormErrorHandler.configError(error));
	}

	/**
	 * Handles successful REST API response
	 */
	private successfulSave(): void {
		this.$store.commit('spinner/HIDE');
		this.$toast.success(this.$t('config.success').toString());
	}
}
</script>
