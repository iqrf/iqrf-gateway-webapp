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
		<h1>{{ $t('config.daemon.misc.title') }}</h1>
		<v-card>
			<v-tabs v-model='activeTab'>
				<v-tab>{{ $t("config.daemon.misc.jsonApi.title") }}</v-tab>
				<v-tab>{{ $t("config.daemon.misc.iqrfRepository.title") }}</v-tab>
				<v-tab>{{ $t("config.daemon.misc.iqrfInfo.title") }}</v-tab>
				<v-tab v-if='isAdmin'>
					{{ $t("config.daemon.misc.iqmesh") }}
				</v-tab>
				<v-tab>{{ $t("config.daemon.misc.monitor.title") }}</v-tab>
				<v-tab>{{ $t("config.daemon.misc.tracer.title") }}</v-tab>
			</v-tabs>
			<v-tabs-items v-model='activeTab'>
				<v-tab-item>
					<JsonApi v-if='!isAdmin' />
					<div v-else>
						<JsonRawApi />
						<hr>
						<JsonSplitter />
					</div>
				</v-tab-item>
				<v-tab-item><IqrfRepository /></v-tab-item>
				<v-tab-item><IqrfInfo /></v-tab-item>
				<v-tab-item v-if='isAdmin'>
					<OtaUpload />
				</v-tab-item>
				<v-tab-item><MonitorList /></v-tab-item>
				<v-tab-item><TracerList /></v-tab-item>
			</v-tabs-items>
		</v-card>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import IqrfInfo from '@/components/Config/IqrfInfo.vue';
import IqrfRepository from '@/components/Config/IqrfRepository.vue';
import JsonApi from '@/components/Config/JsonApi.vue';
import JsonRawApi from '@/components/Config/JsonRawApi.vue';
import JsonSplitter from '@/components/Config/JsonSplitter.vue';
import MonitorList from '@/components/Config/MonitorList.vue';
import OtaUpload from '@/components/Config/OtaUpload.vue';
import TracerList from '@/components/Config/TracerList.vue';

import {UserRole} from '@/services/AuthenticationService';

@Component({
	components: {
		IqrfInfo,
		IqrfRepository,
		JsonApi,
		JsonRawApi,
		JsonSplitter,
		MonitorList,
		OtaUpload,
		TracerList,
	},
	metaInfo: {
		title: 'config.daemon.misc.title',
	},
})

/**
 * Miscellaneous configuration page component
 */
export default class MiscConfiguration extends Vue {
	/**
	 * @var {number} activeTab Index of active tab in CoreUI tabs
	 */
	private activeTab = 0;

	/**
	 * @var {Array<string>} endpoints Array of misc tab endpoints
	 */
	private endpoints: Array<string> = [
		'json',
		'repository',
		'db',
		'monitor',
		'tracer'
	];

	/**
	 * @var {Array<string>} children Children components loading configuration
	 */
	private children: Array<string> = [
		'iqrfInfo',
		'iqrfRepository',
		'monitor',
		'tracer',
	];

	/**
	 * @var {Array<string>} failed Children components config fetch failed
	 */
	private failed: Array<string> = [];

	/**
	 * Checks if user is an administrator
	 * @returns {boolean} True if user is an administrator
	 */
	get isAdmin(): boolean {
		return this.$store.getters['user/getRole'] === UserRole.ADMIN;
	}

	/**
	 * Vue lifecycle hook created
	 */
	created(): void {
		if (this.isAdmin) {
			this.children.push('jsonRawApi', 'jsonSplitter');
		} else {
			this.children.push('jsonApi');
		}
	}

	/**
	 * Vue lifecycle hook mounted
	 */
	mounted(): void {
		if (this.isAdmin) {
			this.endpoints.splice(3, 0, 'iqmesh');
		}
		if (this.$attrs.tabName !== undefined) {
			this.activeTab = this.endpoints.indexOf(this.$attrs.tabName);
		}
	}

}
</script>
