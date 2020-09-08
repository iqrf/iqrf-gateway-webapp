<template>
	<CSidebar
		fixed
		:minimize='minimize'
		:show='show'
		@update:show='(value) => $store.commit("sidebar/set", ["show", value])'
	>
		<CSidebarBrand class='d-md-down-none' to='/'>
			<img class='c-sidebar-brand-full' src='/img/logo-big.svg'>
			<img class='c-sidebar-brand-minimized' src='/img/logo-small.svg'>
		</CSidebarBrand>
		<CRenderFunction flat :content-to-render='getNav' />
		<CSidebarMinimizer
			class='d-md-down-none'
			@click.native='$store.commit("sidebar/set", ["minimize", !minimize])'
		/>
	</CSidebar>
</template>

<script>
import {
	CRenderFunction,
	CSidebar,
	CSidebarBrand,
	CSidebarMinimizer,
} from '@coreui/vue';
import {cilCloud, cilWifiSignal4, cilStorage, cilSettings} from '@coreui/icons';
import FeatureService from '../services/FeatureService';

export default {
	name: 'TheSidebar',
	components: {
		CRenderFunction,
		CSidebar,
		CSidebarBrand,
		CSidebarMinimizer,
	},
	data() {
		return {
			features: null,
		};
	},
	computed: {
		show() {
			return this.$store.state.sidebar.show;
		},
		minimize() {
			return this.$store.state.sidebar.minimize;
		},
		getNav() {
			const data = [
				{
					_name: 'CSidebarNav',
					_children: [
						{
							_name: 'CSidebarNavDropdown',
							name: this.$t('gateway.title'),
							to: '/gateway/',
							route: '/gateway/',
							icon: {
								content: cilStorage,
							},
							roles: [
								'power', 'normal',
							],
							items: [
								{
									name: this.$t('gateway.info.title'),
									to: '/gateway/info/',
									roles: [
										'power', 'normal',
									],
								},
								{
									name: this.$t('gateway.log.title'),
									to: '/gateway/log/',
									roles: [
										'power', 'normal',
									],
								},
								{
									name: this.$t('gateway.mode.title'),
									to: '/gateway/change-mode/',
									roles: [
										'power', 'normal',
									],
								},
								{
									name: this.$t('service.iqrf-gateway-daemon.title'),
									to: '/service/iqrf-gateway-daemon/',
									roles: [
										'power', 'normal',
									],
								},
								{
									name: this.$t('service.ssh.title'),
									to: '/service/ssh/',
									feature: 'ssh',
									roles: [
										'power', 'normal',
									],
								},
								{
									name: this.$t('service.unattended-upgrades.title'),
									to: '/service/unattended-upgrades/',
									feature: 'unattendedUpgrades',
									roles: [
										'power', 'normal',
									],
								},
								{
									name: this.$t('gateway.updater.title'),
									to: '/gateway/updater/',
									feature: 'updater',
									roles: [
										'power', 'normal',
									],
								},
								{
									name: this.$t('gateway.power.title'),
									to: '/gateway/power/',
									roles: [
										'power', 'normal',
									],
								},
							],
						},
						{
							_name: 'CSidebarNavDropdown',
							name: this.$t('config.title'),
							to: '/config/',
							route: '/config/',
							icon: {
								content: cilSettings,
							},
							roles: [
								'power', 'normal',
							],
							items: [
								{
									name: this.$t('config.main.title'),
									to: '/config/main/',
									roles: [
										'power',
									],
								},
								{
									name: this.$t('config.components.title'),
									to: '/config/component/',
									roles: [
										'power',
									],
								},
								{
									name: this.$t('config.iqrfSpi.title'),
									to: '/config/iqrf-spi/',
									component: 'iqrf::IqrfSpi',
									roles: [
										'power', 'normal',
									],
								},
								{
									name: this.$t('config.iqrfCdc.title'),
									to: '/config/iqrf-cdc/',
									component: 'iqrf::IqrfCdc',
									roles: [
										'power', 'normal',
									],
								},
								{
									name: this.$t('config.iqrfUart.title'),
									to: '/config/iqrf-uart/',
									component: 'iqrf::IqrfUart',
									roles: [
										'power', 'normal',
									],
								},
								{
									name: this.$t('config.iqrfDpa.title'),
									to: '/config/iqrf-dpa/',
									roles: [
										'power', 'normal',
									],
								},
								{
									name: this.$t('config.iqrfRepository.title'),
									to: '/config/iqrf-repository/',
									roles: [
										'power', 'normal',
									],
								},
								{
									name: this.$t('config.iqrfInfo.title'),
									to: '/config/iqrf-info/',
									roles: [
										'power', 'normal',
									],
								},
								{
									name: this.$t('config.iqmesh.title'),
									to: '/config/iqmesh/',
									roles: [
										'power',
									],
								},
								{
									name: this.$t('config.mqtt.title'),
									to: '/config/mqtt/',
									roles: [
										'power', 'normal',
									],
								},
								{
									name: this.$t('config.websocket.title'),
									to: '/config/websocket/',
									roles: [
										'power', 'normal',
									],
								},
								{
									name: this.$t('config.mq.title'),
									to: '/config/mq/',
									roles: [
										'power', 'normal',
									],
								},
								{
									name: this.$t('config.udp.title'),
									to: '/config/udp/',
									roles: [
										'power', 'normal',
									],
								},
								{
									name: this.$t('config.jsonSplitter.title'),
									to: '/config/json-splitter/',
									roles: [
										'power',
									],
								},
								{
									name: this.$t('config.scheduler.title'),
									to: '/config/scheduler/',
									roles: [
										'power', 'normal',
									],
								},
								{
									name: this.$t('config.tracer.title'),
									to: '/config/tracer/',
									roles: [
										'power', 'normal',
									],
								},
								{
									name: this.$t('config.monitor.title'),
									to: '/config/monitor/',
									roles: [
										'power', 'normal',
									],
								},
								{
									name: this.$t('config.migration.title'),
									to: '/config/migration/',
									roles: [
										'power', 'normal',
									],
								},
								{
									name: this.$t('translatorConfig.title'),
									to: '/config/translator/',
									roles: [
										'power', 'normal',
									]
								},
								{
									name: this.$t('controllerConfig.title'),
									to: '/config/controller/',
									roles: [
										'power', 'normal',
									]
								}
							],
						},
						{
							_name: 'CSidebarNavDropdown',
							name: this.$t('iqrfnet.title'),
							to: '/iqrfnet/',
							route: '/iqrfnet/',
							icon: {
								content: cilWifiSignal4,
							},
							roles: [
								'power', 'normal'
							],
							items: [
								{
									name: this.$t('iqrfnet.sendPacket.title'),
									to: '/iqrfnet/send-raw/',
									roles: [
										'power', 'normal'
									],
								},
								{
									name: this.$t('iqrfnet.sendJson.title'),
									to: '/iqrfnet/send-json/',
									roles: [
										'power', 'normal'
									],
								},
								{
									name: this.$t('iqrfnet.trUpload.title'),
									to: '/iqrfnet/tr-upload/',
									feature: 'trUpload',
									roles: [
										'power', 'normal'
									],
								},
								{
									name: this.$t('iqrfnet.trConfiguration.title'),
									to: '/iqrfnet/tr-config/',
									roles: [
										'power', 'normal'
									],
								},
								{
									name: this.$t('iqrfnet.networkManager.title'),
									to: '/iqrfnet/network/',
									roles: [
										'power', 'normal'
									],
								},
								{
									name: this.$t('iqrfnet.standard.title'),
									to: '/iqrfnet/standard/',
									feature: 'pixla',
									roles: [
										'power', 'normal'
									],
								},
							],
						},
						{
							_name: 'CSidebarNavDropdown',
							name: this.$t('cloud.title'),
							to: '/cloud/',
							route: '/cloud/',
							icon: {
								content: cilCloud,
							},
							roles: [
								'power', 'normal'
							],
							items: [
								{
									name: this.$t('cloud.ibmCloud.title'),
									to: '/cloud/ibm-cloud/',
									roles: [
										'power', 'normal'
									],
								},
								{
									name: this.$t('cloud.msAzure.title'),
									to: '/cloud/azure/',
									roles: [
										'power', 'normal'
									],
								},
								{
									name: this.$t('cloud.amazonAws.title'),
									to: '/cloud/aws/',
									roles: [
										'power', 'normal'
									],
								},
								{
									name: this.$t('cloud.hexio.title'),
									to: '/cloud/hexio/',
									roles: [
										'power', 'normal'
									],
								},
								{
									name: this.$t('cloud.intelimentsInteliGlue.title'),
									to: '/cloud/inteli-glue/',
									roles: [
										'power', 'normal'
									],
								},
								{
									name: this.$t('cloud.pixla.title'),
									to: '/cloud/pixla/',
									feature: 'pixla',
									roles: [
										'power', 'normal'
									],
								},
							],
						},
					],
				},
			];
			return data.filter((element) => {
				element._children = element._children.filter((element) => {
					if (!element.roles.includes(this.$store.getters['user/getRole'])) {
						return null;
					}
					if (element.feature === undefined ||
							this.features === null ||
							this.features[element.feature].enabled) {
						return element;
					}
					if (element.items) {
						element.items = element.items.filter((element) => {
							if (!element.roles.includes(this.$store.getters['user/getRole'])) {
								return null;
							}
							if (element.feature === undefined ||
									this.features === null ||
									this.features[element.feature].enabled) {
								return element;
							}
						});
					}
					return element;
				});
				return element;
			});
		}
	},
	created() {
		FeatureService.fetchAll().then((response) => (this.features = response.data));
	},
};
</script>

<style scoped>

</style>
