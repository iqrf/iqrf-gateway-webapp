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
import JsonRawApi from '../../pages/Config/JsonRawApi.vue';
import JsonMngMetaDataApi from '../../pages/Config/JsonMngMetaDataApi.vue';
import JsonSplitter from '../../pages/Config/JsonSplitter.vue';
import JsonApi from '../../pages/Config/JsonApi.vue';
import SchedulerList from '../../pages/Config/SchedulerList.vue';
import TracerList from '../../pages/Config/TracerList.vue';
import MonitorList from '../../pages/Config/MonitorList.vue';
import IqrfRepository from '../../pages/Config/IqrfRepository.vue';
import IqrfInfo from '../../pages/Config/IqrfInfo.vue';
import IqmeshServices from '../../pages/Config/IqmeshServices.vue';

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
		SchedulerList,
		TracerList
	},
	metaInfo: {
		title: 'config.daemon.misc.title'
	}
})

/**
 * 
 */
export default class MiscConfiguration extends Vue {
	/**
	 * @var {number} activeTab Index of active tab in CoreUI tabs
	 */
	private activeTab = 0;

	/**
	 * @var {boolean} powerUser Indicates whether user profile is power user
	 */
	private powerUser = false;

	private updateRouter(index: number): void {
		this.$router.replace('/config/daemon/misc/' + index);
	}

	/**
	 * @property {number} tabIndex Index of tab to load when accessing page
	 */
	@Prop({required: false, default: 0}) tabIndex!: number

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
		this.activeTab = this.tabIndex;
	}
}
</script>
