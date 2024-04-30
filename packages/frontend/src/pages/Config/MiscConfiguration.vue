<!--
Copyright 2017-2024 IQRF Tech s.r.o.
Copyright 2019-2024 MICRORISC s.r.o.

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
			<v-tabs v-model='activeTab' :show-arrows='true'>
				<v-tab>{{ $t("config.daemon.misc.jsonApi.title") }}</v-tab>
				<v-tab>{{ $t("config.daemon.misc.iqrfRepository.title") }}</v-tab>
				<v-tab>{{ $t("config.daemon.misc.iqrfDb.title") }}</v-tab>
				<v-tab>{{ $t("config.daemon.misc.monitor.title") }}</v-tab>
				<v-tab>{{ $t("config.daemon.misc.tracer.title") }}</v-tab>
			</v-tabs>
			<v-tabs-items v-model='activeTab'>
				<v-tab-item :transition='false'>
					<div v-if='!isAdmin'>
						<JsonApi />
					</div>
					<div v-else>
						<JsonRawApi />
						<JsonSplitter />
					</div>
				</v-tab-item>
				<v-tab-item :transition='false'>
					<IqrfRepository />
				</v-tab-item>
				<v-tab-item :transition='false'>
					<IqrfDb />
				</v-tab-item>
				<v-tab-item :transition='false'>
					<MonitorList />
				</v-tab-item>
				<v-tab-item :transition='false'>
					<TracerList />
				</v-tab-item>
			</v-tabs-items>
		</v-card>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import IqrfDb from '@/components/Config/Misc/IqrfDb.vue';
import IqrfRepository from '@/components/Config/Misc/IqrfRepository.vue';
import JsonApi from '@/components/Config/Misc/JsonApi.vue';
import JsonRawApi from '@/components/Config/Misc/JsonRawApi.vue';
import JsonSplitter from '@/components/Config/Misc/JsonSplitter.vue';
import MonitorList from '@/components/Config/Misc/MonitorList.vue';
import TracerList from '@/components/Config/Misc/TracerList.vue';
import {UserRole} from '@iqrf/iqrf-gateway-webapp-client/types/User';


@Component({
	components: {
		IqrfDb,
		IqrfRepository,
		JsonApi,
		JsonRawApi,
		JsonSplitter,
		MonitorList,
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
	 * @var {number} activeTab Index of active tab
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
	 * Checks if user is an administrator
	 * @returns {boolean} True if user is an administrator
	 */
	get isAdmin(): boolean {
		return this.$store.getters['user/getRole'] === UserRole.Admin;
	}

	/**
	 * Vue lifecycle hook mounted
	 */
	mounted(): void {
		if (this.$attrs.tabName !== undefined) {
			this.activeTab = this.endpoints.indexOf(this.$attrs.tabName);
		}
	}

}
</script>
