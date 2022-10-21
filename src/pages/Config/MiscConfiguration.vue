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
		<CCard>
			<CTabs variant='tabs' :active-tab='activeTab'>
				<CTab :title='$t("config.daemon.misc.jsonApi.title")'>
					<JsonApi
						v-if='!isAdmin'
						@fetched='configFetch'
					/>
					<div v-else>
						<JsonRawApi @fetched='configFetch' />
						<JsonSplitter @fetched='configFetch' />
					</div>
				</CTab>
				<CTab :title='$t("config.daemon.misc.iqrfRepository.title")'>
					<IqrfRepository @fetched='configFetch' />
				</CTab>
				<CTab :title='$t("config.daemon.misc.iqrfInfo.title")'>
					<IqrfInfo @fetched='configFetch' />
				</CTab>
				<CTab v-if='isAdmin' :title='$t("config.daemon.misc.iqmesh")'>
					<OtaUpload @fetched='configFetch' />
				</CTab>
				<CTab :title='$t("config.daemon.misc.monitor.title")'>
					<MonitorList @fetched='configFetch' />
				</CTab>
				<CTab :title='$t("config.daemon.misc.tracer.title")'>
					<TracerList @fetched='configFetch' />
				</CTab>
			</CTabs>
		</CCard>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CCard, CCardBody, CCardHeader, CTab, CTabs} from '@coreui/vue/src';
import IqrfInfo from '@/components/Config/Misc/IqrfInfo.vue';
import IqrfRepository from '@/components/Config/Misc/IqrfRepository.vue';
import JsonApi from '@/components/Config/Misc/JsonApi.vue';
import JsonRawApi from '@/components/Config/Misc/JsonRawApi.vue';
import JsonSplitter from '@/components/Config/Misc/JsonSplitter.vue';
import MonitorList from '@/components/Config/Misc/MonitorList.vue';
import OtaUpload from '@/components/Config/Misc/OtaUpload.vue';
import TracerList from '@/components/Config/Misc/TracerList.vue';

import {UserRole} from '@/services/AuthenticationService';

import {IConfigFetch} from '@/interfaces/Config/Daemon';

@Component({
	components: {
		CCard,
		CCardBody,
		CCardHeader,
		CTab,
		CTabs,
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
		this.$store.commit('spinner/SHOW',
			this.$t('config.daemon.messages.configMiscFetch').toString()
		);
	}

	/**
	 * Handles successful child component config fetch event
	 * @param {IConfigFetch} data Component fetch meta
	 */
	private configFetch(data: IConfigFetch): void {
		this.children = this.children.filter((item: string) => item !== data.name);
		if (!data.success) {
			this.failed.push(this.$t(`config.daemon.misc.${data.name}.title`).toString());
		}
		if (this.children.length > 0) {
			return;
		}
		this.$store.commit('spinner/HIDE');
		if (this.failed.length === 0) {
			return;
		}
		this.$toast.error(
			this.$t(
				'config.daemon.messages.configFetchFailed',
				{children: this.failed.sort().join(', ')},
			).toString()
		);
		this.failed = [];
	}

}
</script>
