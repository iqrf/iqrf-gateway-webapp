<template>
	<div>
		<h1>{{ $t('config.daemon.misc.title') }}</h1>
		<CCard>
			<CTabs variant='tabs' :active-tab='activeTab' @update:activeTab='updateRouter'>
				<CTab :title='$t("config.jsonApi.title")'>
					<JsonApi v-if='!powerUser' />
					<JsonMngMetaDataApi v-if='powerUser' />
					<JsonRawApi v-if='powerUser' />
					<JsonSplitter v-if='powerUser' />
				</CTab>
				<CTab :title='$t("config.iqrfRepository.title")'>
					<IqrfRepository />
				</CTab>
				<CTab :title='$t("config.iqrfInfo.title")'>
					<IqrfInfo />
				</CTab>
				<CTab v-if='powerUser' :title='$t("config.iqmesh.title")'>
					<IqmeshServices />
				</CTab>
				<CTab :title='$t("config.monitor.title")'>
					<MonitorList />
				</CTab>
				<CTab :title='$t("config.tracer.title")'>
					<TracerList />
				</CTab>
			</CTabs>
		</CCard>
	</div>
</template>

<script lang='ts'>
import {Component, Prop, Vue} from 'vue-property-decorator';
import {CCard, CCardBody, CCardHeader, CTab, CTabs} from '@coreui/vue/src';
import JsonRawApi from '../../components/Config/JsonRawApi.vue';
import JsonMngMetaDataApi from '../../components/Config/JsonMngMetaDataApi.vue';
import JsonSplitter from '../../components/Config/JsonSplitter.vue';
import JsonApi from '../../components/Config/JsonApi.vue';
import TracerList from '../../components/Config/TracerList.vue';
import MonitorList from '../../components/Config/MonitorList.vue';
import IqrfRepository from '../../components/Config/IqrfRepository.vue';
import IqrfInfo from '../../components/Config/IqrfInfo.vue';
import IqmeshServices from '../../components/Config/IqmeshServices.vue';

@Component({
	components: {
		CCard,
		CCardBody,
		CCardHeader,
		CTab,
		CTabs,
		IqmeshServices,
		IqrfInfo,
		IqrfRepository,
		JsonApi,
		JsonMngMetaDataApi,
		JsonRawApi,
		JsonSplitter,
		MonitorList,
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
		'json-api',
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
	 * Updates URL to match the config tab
	 */
	private updateRouter(index: number): void {
		this.$router.replace('/config/daemon/misc/' + this.endpoints[index]);
	}

	/**
	 * @property {number} tabIndex Index of tab to load when accessing page
	 */
	@Prop({required: false, default: 'json-api'}) tabName!: string

	/**
	 * Vue lifecycle hook created
	 */
	created(): void {
		if (this.$store.getters['user/getRole'] === 'power') {
			this.powerUser = true;
		}
	}

	/**
	 * Vue lifecycle hook mounted
	 */
	mounted(): void {
		if (this.powerUser) {
			this.endpoints.splice(3, 0, 'iqmesh');
		}
		this.activeTab = this.endpoints.indexOf(this.tabName);
	}
}
</script>
