<template>
	<CCard body-wrapper>
		<div v-if='!missing && !unsupported'>
			<CButton v-if='!service.enabled' color='success' @click='enable()'>
				{{ $t('service.actions.enable') }}
			</CButton>
			<CButton v-if='service.enabled' color='danger' @click='disable()'>
				{{ $t('service.actions.disable') }}
			</CButton>
			<CButton v-if='!service.active' color='success' @click='start()'>
				{{ $t('service.actions.start') }}
			</CButton>
			<CButton v-if='service.active' color='danger' @click='stop()'>
				{{ $t('service.actions.stop') }}
			</CButton>
			<CButton v-if='service.active' color='primary' @click='restart()'>
				{{ $t('service.actions.restart') }}
			</CButton>
			<CButton color='secondary' @click='refreshStatus()'>
				{{ $t('service.actions.status') }}
			</CButton>
		</div>
		<br>
		<strong>{{ $t('service.status') }}: </strong>
		<span v-if='missing'>
			{{ $t('service.states.missing') }}
		</span>
		<span v-if='unsupported'>
			{{ $t('service.states.unsupported') }}
		</span>
		<span v-else>
			{{ $t('service.states.' + (service.enabled ? 'enabled' : 'disabled')) }},
			{{ $t('service.states.' + (service.active ? 'active' : 'inactive')) }}
		</span>
		<br><br>
		<pre v-if='service !== null' class='log'>{{ service.status }}</pre>
	</CCard>
</template>

<script>
import {CButton, CCard} from '@coreui/vue/src';
import ServiceService from '../../services/ServiceService';

const whitelisted = [
	'iqrf-gateway-controller',
	'iqrf-gateway-daemon',
	'iqrf-gateway-translator',
	'ssh',
	'unattended-upgrades',
];

const features = {
	'iqrf-gateway-controller': 'iqrfGatewayController',
	'iqrf-gateway-translator': 'iqrfGatewayTranslator',
	'ssh': 'ssh',
	'unattended-upgrades': 'unattendedUpgrades',
};

export default {
	name: 'ServiceControl',
	components: {
		CButton,
		CCard,
	},
	props: {
		serviceName: {
			type: String,
			required: true,
		}
	},
	data() {
		return {
			missing: false,
			unsupported: false,
			service: null,
		};
	},
	watch: {
		serviceName: function (newVal) {
			this.$store.commit('spinner/SHOW');
			this.serviceName = newVal;
			this.getStatus();
		}
	},
	created() {
		this.$store.commit('spinner/SHOW');
		this.getStatus();
	},
	methods: {
		enable() {
			this.$store.commit('spinner/SHOW');
			ServiceService.enable(this.serviceName)
				.then(() => (this.handleSuccess('enable')))
				.catch(this.handleError);
		},
		disable() {
			this.$store.commit('spinner/SHOW');
			ServiceService.disable(this.serviceName)
				.then(() => (this.handleSuccess('disable')))
				.catch(this.handleError);
		},
		getStatus() {
			if (!whitelisted.includes(this.serviceName)) {
				this.unsupported = true;
				this.$store.commit('spinner/HIDE');
				this.$toast.error(
					this.$t('service.errors.unsupportedService').toString()
				);
				return;
			}
			ServiceService.getStatus(this.serviceName)
				.then((status) => {
					this.service = status;
					this.unsupported = false;
					this.$store.commit('spinner/HIDE');
				})
				.catch(this.handleError);
		},
		handleError(error) {
			this.$store.commit('spinner/HIDE');
			let response = error.response;
			if (response.status === 404) {
				this.missing = true;
				this.$toast.error(this.$t('service.errors.missingService').toString());
			}
			if (response.status === 500 &&
					response.data.message === 'Unsupported init system') {
				this.unsupported = false;
				this.$toast.error(this.$t('service.errors.unsupportedInit').toString());
			}
		},
		handleSuccess(action) {
			this.getStatus();
			this.$toast.success(
				this.$t('service.' + this.serviceName + '.messages.' + action)
					.toString()
			);
		},
		refreshStatus() {
			this.$store.commit('spinner/SHOW');
			this.getStatus();
		},
		restart() {
			this.$store.commit('spinner/SHOW');
			ServiceService.restart(this.serviceName)
				.then(() => (this.handleSuccess('restart')))
				.catch(this.handleError);
		},
		start() {
			this.$store.commit('spinner/SHOW');
			ServiceService.start(this.serviceName)
				.then(() => (this.handleSuccess('start')))
				.catch(this.handleError);
		},
		stop() {
			this.$store.commit('spinner/SHOW');
			ServiceService.stop(this.serviceName)
				.then(() => (this.handleSuccess('stop')))
				.catch(this.handleError);
		},
	},
	beforeRouteEnter(to, from, next) {
		next(vm => {
			const feature = features[vm.serviceName];
			if (feature !== undefined &&
					!vm.$store.getters['features/isEnabled'](feature)) {
				vm.$toast.error(
					vm.$t('service.' +vm.serviceName + '.messages.disabled').toString()
				);
				vm.$router.push(from.path);
			}
		});
	},
	metaInfo() {
		return {
			title: whitelisted.includes(this.serviceName) ?
				'service.' + this.serviceName + '.title' :
				'service.unsupported.title',
		};
	},
};
</script>
