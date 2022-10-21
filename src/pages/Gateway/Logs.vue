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
					@click='getAvailableLogs'
				>
					{{ $t('forms.refresh') }}
				</CButton>
			</div>
		</header>
		<CAlert
			v-if='loaded && logs.length === 0'
			color='danger'
		>
			{{ $t('gateway.log.messages.noLogs') }}
		</CAlert>
		<CCard v-else-if='loaded && logs.length > 0'>
			<CTabs variant='tabs' :active-tab.sync='tab'>
				<CTab v-for='(item, i) in logs' :key='i' :title='$t(`gateway.log.services.${item.name}`)'>
					<LogTab v-if='item.loaded' :log.sync='item.log' />
				</CTab>
			</CTabs>
		</CCard>
	</div>
</template>

<script lang='ts'>
import {Component, Vue, Watch} from 'vue-property-decorator';
import {CButton, CCard, CTab, CTabs} from '@coreui/vue/src';
import LogTab from '@/components/Gateway/LogTab.vue';

import {extendedErrorToast} from '@/helpers/errorToast';
import {fileDownloader} from '@/helpers/fileDownloader';

import GatewayService from '@/services/GatewayService';

import {AxiosError, AxiosResponse} from 'axios';
import {IServiceLog} from '@/interfaces/Gateway/Log';
import {MetaInfo} from 'vue-meta';

@Component({
	components: {
		CButton,
		CCard,
		CTab,
		CTabs,
		LogTab,
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
	 * @var {number} tab Number of active tab
	 */
	private tab = 0;

	/**
	 * Array of service logs
	 */
	private logs: Array<IServiceLog> = [];

	/**
	 * @var {boolean} loaded Indicates that logs have been loaded
	 */
	private loaded = false;

	@Watch('tab')
	private onTabChanged(): void {
		this.getServiceLog();
	}

	/**
	 * Retrieve list of available logs when component is mounted
	 */
	mounted(): void {
		this.getAvailableLogs();
	}

	/**
	 * Retrieves a list of available logs
	 */
	private getAvailableLogs(): void {
		if (this.loaded) {
			this.loaded = false;
		}
		this.$store.commit('spinner/SHOW');
		GatewayService.getAvailableLogs()
			.then((response: AxiosResponse) => {
				const logs: Array<IServiceLog> = [];
				response.data.forEach((item: string) => {
					logs.push({
						name: item,
						log: null,
						loaded: false,
					});
				});
				if (logs.length === 0) {
					this.$store.commit('spinner/HIDE');
				} else {
					this.logs = logs;
					this.getServiceLog();
				}
				this.loaded = true;
			})
			.catch((error: AxiosError) => {
				extendedErrorToast(error, 'gateway.log.messages.listFailed');
				this.loaded = true;
			});
	}

	/**
	 * Retrieves service log
	 */
	private getServiceLog(): void {
		if (!this.$store.getters['spinner/isEnabled']) {
			this.$store.commit('spinner/SHOW');
		}
		GatewayService.getServiceLog(this.logs[this.tab].name)
			.then((response: AxiosResponse) => {
				this.logs[this.tab].log = response.data;
				this.logs[this.tab].loaded = true;
				this.$store.commit('spinner/HIDE');
			})
			.catch((error: AxiosError) => {
				if (error.response === undefined || error.response.status !== 404) {
					extendedErrorToast(error, 'gateway.log.messages.fetchFailed');
				} else {
					this.$store.commit('spinner/HIDE');
				}
				this.logs[this.tab].loaded = true;
			});
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
