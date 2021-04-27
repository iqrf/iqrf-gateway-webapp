<template>
	<div>
		<h1>{{ $t('config.daemon.misc.title') }}</h1>
		<CCard>
			<CTabs variant='tabs' :active-tab='activeTab'>
				<CTab :title='$t("config.daemon.misc.jsonApi.title")'>
					<JsonApi 
						v-if='!powerUser'
						@fetched='configFetch'
					/>
					<div v-else>
						<JsonMngMetaDataApi @fetched='configFetch' />
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
				<CTab v-if='powerUser' :title='$t("config.daemon.misc.iqmesh")'>
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
import IqrfInfo from '../../components/Config/IqrfInfo.vue';
import IqrfRepository from '../../components/Config/IqrfRepository.vue';
import JsonApi from '../../components/Config/JsonApi.vue';
import JsonMngMetaDataApi from '../../components/Config/JsonMngMetaDataApi.vue';
import JsonRawApi from '../../components/Config/JsonRawApi.vue';
import JsonSplitter from '../../components/Config/JsonSplitter.vue';
import MonitorList from '../../components/Config/MonitorList.vue';
import OtaUpload from '../../components/Config/OtaUpload.vue';
import TracerList from '../../components/Config/TracerList.vue';

import {IConfigFetch} from '../../interfaces/daemonComponent';

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
		JsonMngMetaDataApi,
		JsonRawApi,
		JsonSplitter,
		MonitorList,
		OtaUpload,
		TracerList
	},
	metaInfo: {
		title: 'config.daemon.misc.title'
	}
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
	]

	/**
	 * @var {boolean} powerUser Indicates whether user profile is power user
	 */
	private powerUser = false;

	/**
	 * @var {Array<string>} children Children components loading configuration
	 */
	private children: Array<string> = [
		'iqrfInfo',
		'iqrfRepository',
		'monitor',
		'tracer',
	]

	/**
	 * @var {Array<string>} failed Children components config fetch failed
	 */
	private failed: Array<string> = []

	/**
	 * Vue lifecycle hook created
	 */
	created(): void {
		if (this.$store.getters['user/getRole'] === 'power') {
			this.powerUser = true;
			this.children.push('jsonMngMetaDataApi', 'jsonRawApi', 'jsonSplitter');
		} else {
			this.children.push('jsonApi');
		}
	}

	/**
	 * Vue lifecycle hook mounted
	 */
	mounted(): void {
		if (this.powerUser) {
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
			this.failed.push(this.$t('config.daemon.misc.' + data.name + '.title').toString());
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
