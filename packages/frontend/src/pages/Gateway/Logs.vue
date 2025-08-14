<!--
Copyright 2017-2025 IQRF Tech s.r.o.
Copyright 2019-2025 MICRORISC s.r.o.

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
		<CCard>
			<CTabs :active-tab.sync='tab'>
				<CTab
					v-for='(item, key) in logs'
					:key='key'
					:title='$t(`gateway.log.services.${key}`)'
				>
					<CCard
						v-if='!item.available || !item.loaded || item.log.length === 0'
						class='border-0 mb-0'
					>
						<CCardBody>
							<CAlert
								v-if='!item.available'
								class='mb-0'
								color='danger'
							>
								{{ $t('gateway.log.messages.notAvailable') }}
							</CAlert>
							<CAlert
								v-else-if='!item.loaded'
								class='mb-0'
								color='warning'
							>
								{{ $t('gateway.log.messages.notLoaded') }}
							</CAlert>
							<CAlert
								v-else-if='item.log.length === 0'
								class='mb-0'
								color='info'
							>
								{{ $t('gateway.log.messages.noLogs') }}
							</CAlert>
						</CCardBody>
					</CCard>
					<LogViewer v-else :log='item.log' />
				</CTab>
				<CTab :title='$t("gateway.log.journal.title")'>
					<JournalViewer v-if='tab === Object.keys(logs).length' />
				</CTab>
			</CTabs>
		</CCard>
	</div>
</template>

<script lang='ts'>
import {Component, Vue, Watch} from 'vue-property-decorator';
import {CButton, CCard, CTab, CTabs} from '@coreui/vue/src';
import JournalViewer from '@/components/Gateway/JournalViewer.vue';
import LogViewer from '@/components/Gateway/LogViewer.vue';

import {extendedErrorToast} from '@/helpers/errorToast';
import {fileDownloader} from '@/helpers/fileDownloader';

import GatewayService from '@/services/GatewayService';

import {AxiosError, AxiosResponse} from 'axios';
import {ILog} from '@/interfaces/Gateway/Log';
import {MetaInfo} from 'vue-meta';

@Component({
	components: {
		CButton,
		CCard,
		CTab,
		CTabs,
		JournalViewer,
		LogViewer,
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
export default class Logs extends Vue {
	/**
	 * @var {number} tab Number of active tab
	 */
	private tab = 0;

	/**
	 * Service logs
	 */
	private logs: Record<string, ILog> = {
		'iqrf-gateway-controller': {
			available: false,
			loaded: false,
			log: '',
		},
		'iqrf-gateway-daemon': {
			available: false,
			loaded: false,
			log: '',
		},
		'iqrf-gateway-setter': {
			available: false,
			loaded: false,
			log: '',
		},
		'iqrf-gateway-uploader': {
			available: false,
			loaded: false,
			log: '',
		},
	};

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
		this.$store.commit('spinner/SHOW');
		GatewayService.getAvailableLogs()
			.then((response: AxiosResponse) => {
				if (response.data.length === 0) {
					this.$store.commit('spinner/HIDE');
					return;
				}
				response.data.forEach((item: string) => {
					if (this.logs[item] === undefined) {
						return;
					}
					this.logs[item].available = true;
				});
				this.$store.commit('spinner/HIDE');
				this.getServiceLog();
			})
			.catch((error: AxiosError) => {
				extendedErrorToast(error, 'gateway.log.messages.listFailed');
			});
	}

	/**
	 * Retrieves service log
	 */
	private getServiceLog(): void {
		const keys = Object.keys(this.logs);
		if (this.tab > keys.length - 1) {
			return;
		}
		const service = keys[this.tab];
		if (!this.logs[service].available) {
			return;
		}
		if (!this.$store.getters['spinner/isEnabled']) {
			this.$store.commit('spinner/SHOW');
		}
		GatewayService.getServiceLog(service)
			.then((response: AxiosResponse) => {
				this.logs[service].log = response.data;
				this.logs[service].loaded = true;
				this.$store.commit('spinner/HIDE');
			})
			.catch((error: AxiosError) => {
				if (error.response === undefined || error.response.status !== 404) {
					extendedErrorToast(error, 'gateway.log.messages.fetchFailed');
				} else {
					this.logs[service].loaded = true;
					this.$store.commit('spinner/HIDE');
				}
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
