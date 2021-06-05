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
		<header class='d-flex'>
			<h1 class='mr-auto'>
				{{ $t('gateway.log.title') }}
			</h1>
			<div>
				<CButton
					color='primary'
					@click='downloadArchive'
				>
					{{ $t('gateway.log.download') }}
				</CButton> <CButton
					color='primary'
					@click='getLogs'
				>
					{{ $t('forms.refresh') }}
				</CButton>
			</div>
		</header>
		<SystemdJournal v-if='$store.getters["features/isEnabled"]("systemdJournal")' />
		<CCard>			
			<CTabs variant='tabs' :active-tab='activeTab'>
				<CTab 
					v-if='loaded && controllerLog !== null'
					:title='$t("service.iqrf-gateway-controller.title")'
				>
					<CCardBody>
						<CAlert 
							v-if='controllerLog.length === 0'
							class='card-margin-bottom'
							color='info'
						>
							{{ $t('gateway.log.messages.logEmpty') }}
						</CAlert>
						<pre v-else class='log card-margin-bottom'>{{ controllerLog }}</pre>
					</CCardBody>
				</CTab>
				<CTab
					v-if='loaded'
					:title='$t("service.iqrf-gateway-daemon.title")'
				>
					<CCardBody>
						<CAlert
							v-if='daemonLog === null'
							class='card-margin-bottom'
							color='danger'
						>
							{{ $t('gateway.log.messages.logNotFound') }}
						</CAlert>
						<CAlert
							v-else-if='daemonLog.length === 0'
							class='card-margin-bottom'
							color='info'
						>
							{{ $t('gateway.log.messages.logEmpty') }}
						</CAlert>
						<pre v-else class='log card-margin-bottom'>{{ daemonLog }}</pre>
					</CCardBody>
				</CTab>
			</CTabs>
		</CCard>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CAlert, CButton, CCard, CTab, CTabs} from '@coreui/vue/src';
import SystemdJournal from '../../components/Gateway/SystemdJournal.vue';

import {extendedErrorToast} from '../../helpers/errorToast';
import {fileDownloader} from '../../helpers/fileDownloader';

import GatewayService from '../../services/GatewayService';

import {AxiosError, AxiosResponse} from 'axios';
import {MetaInfo} from 'vue-meta';

@Component({
	components: {
		CAlert,
		CButton,
		CCard,
		CTab,
		CTabs,
		SystemdJournal,
	},
	metaInfo(): MetaInfo {
		return {
			title: 'gateway.log.title',
		};
	}
})

/**
 * IQRF Gateway log viewer component
 */
export default class LogViewer extends Vue {

	/**
	 * @var {number} activeTab CoreUI tabs active tab index
	 */
	private activeTab = 0

	/**
	 * @var {string|null} controllerLog Controller log file content
	 */
	private controllerLog: string|null = null

	/**
	 * @var {string|null} daemonLog Daemon log file content
	 */
	private daemonLog: string|null = null

	/**
	 * @var {boolean} loaded Indicates that logs have been loaded 
	 */
	private loaded = false;

	mounted(): void {
		this.getLogs();
	}

	private getLogs(): void {
		this.$store.commit('spinner/SHOW');
		GatewayService.getLatestLog()
			.then(
				(response: AxiosResponse) => {
					this.daemonLog = response.data.daemon;
					if (response.data.controller) {
						this.controllerLog = response.data.controller;
						this.activeTab = 1;
					}
					this.loaded = true;
					this.$store.commit('spinner/HIDE');
				}
			)
			.catch((error: AxiosError) => extendedErrorToast(error, 'gateway.log.messages.fetchFailed'));
	}
	
	/**
	 * Creates a daemon log blob and prompts file download
	 */
	private downloadArchive(): void {
		this.$store.commit('spinner/SHOW');
		GatewayService.getLogArchive().then(
			(response: AxiosResponse) => {
				const file = fileDownloader(response, 'application/zip', 'iqrf-gateway-logs.zip');
				this.$store.commit('spinner/HIDE');
				file.click();
			}
		).catch(() => (this.$store.commit('spinner/HIDE')));
	}
}
</script>
