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
				<v-btn
					color='primary'
					@click='downloadArchive'
				>
					<v-icon>mdi-download</v-icon>
					{{ $t('gateway.log.download') }}
				</v-btn> <v-btn
					color='primary'
					@click='getAvailableLogs'
				>
					<v-icon>mdi-refresh</v-icon>
					{{ $t('forms.refresh') }}
				</v-btn>
			</div>
		</header>
		<v-card>
			<v-tabs v-model='tab' :show-arrows='true'>
				<v-tab v-for='key in Object.keys(logs)' :key='key'>
					{{ $t(`gateway.log.services.${key}`) }}
				</v-tab>
				<v-tab>
					{{ $t('gateway.log.journal.title') }}
				</v-tab>
			</v-tabs>
			<v-divider />
			<v-tabs-items v-model='tab'>
				<v-tab-item
					v-for='(item, i) in logs'
					:key='i'
					:transition='false'
				>
					<v-card
						v-if='!item.available || !item.loaded || item.log.length === 0'
						flat
						tile
					>
						<v-card-text>
							<v-alert
								v-if='!item.available'
								class='mb-0'
								type='error'
								text
							>
								{{ $t('gateway.log.messages.notAvailable') }}
							</v-alert>
							<v-alert
								v-else-if='!item.loaded'
								class='mb-0'
								type='warning'
								text
							>
								{{ $t('gateway.log.messages.notLoaded') }}
							</v-alert>
							<v-alert
								v-else-if='item.log.length === 0'
								class='mb-0'
								type='info'
								text
							>
								{{ $t('gateway.log.messages.noLogs') }}
							</v-alert>
						</v-card-text>
					</v-card>
					<LogViewer v-else :log.sync='item.log' />
				</v-tab-item>
				<v-tab-item
					key='journal'
					:transition='false'
				>
					<JournalViewer v-if='tab === Object.keys(logs).length' />
				</v-tab-item>
			</v-tabs-items>
		</v-card>
	</div>
</template>

<script lang='ts'>
import { LogService } from '@iqrf/iqrf-gateway-webapp-client/services/Gateway';
import { FileResponse } from '@iqrf/iqrf-gateway-webapp-client/types';
import { FileDownloader } from '@iqrf/iqrf-gateway-webapp-client/utils';
import { AxiosError } from 'axios';
import { Component, Vue, Watch } from 'vue-property-decorator';
import { MetaInfo } from 'vue-meta';

import JournalViewer from '@/components/Gateway/JournalViewer.vue';
import LogViewer from '@/components/Gateway/LogViewer.vue';
import { extendedErrorToast } from '@/helpers/errorToast';
import { ILog } from '@/interfaces/Gateway/Log';
import { useApiClient } from '@/services/ApiClient';

@Component({
	components: {
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

	/**
	 * @property {LogService} service Log service
	 */
	private service: LogService = useApiClient().getGatewayServices().getLogService();

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
		this.service.listAvailable()
			.then((response: string[]) => {
				if (response.length === 0) {
					this.$store.commit('spinner/HIDE');
					return;
				}
				response.forEach((item: string) => {
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
		this.service.getServiceLog(service)
			.then((response: string) => {
				this.logs[service].log = response;
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
		this.service.exportLogs()
			.then((response: FileResponse<Blob>) => {
				FileDownloader.downloadFileResponse(response);
				this.$store.commit('spinner/HIDE');
			})
			.catch(() => (this.$store.commit('spinner/HIDE')));
	}
}
</script>
