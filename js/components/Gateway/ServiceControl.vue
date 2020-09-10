<template>
	<CCard body-wrapper>
		<div v-if='!missing && !unsupported'>
			<CButton v-if='!enabled' color='success' @click='enable()'>
				{{ $t('service.actions.enable') }}
			</CButton>
			<CButton v-if='enabled' color='danger' @click='disable()'>
				{{ $t('service.actions.disable') }}
			</CButton>
			<CButton v-if='!active' color='success' @click='start()'>
				{{ $t('service.actions.start') }}
			</CButton>
			<CButton v-if='active' color='danger' @click='stop()'>
				{{ $t('service.actions.stop') }}
			</CButton>
			<CButton v-if='active' color='primary' @click='restart()'>
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
			{{ $t('service.states.' + (enabled ? 'enabled' : 'disabled')) }},
			{{ $t('service.states.' + (active ? 'active' : 'inactive')) }}
		</span>
		<br><br>
		<pre v-if='status' class='log'>{{ status }}</pre>
	</CCard>
</template>

<script>
import {CButton, CCard} from '@coreui/vue';
import ServiceService from '../../services/ServiceService';

const whitelisted = ['iqrf-gateway-daemon', 'ssh', 'unattended-upgrades'];

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
			active: false,
			enabled: false,
			missing: false,
			status: null,
			unsupported: false,
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
				const title = this.$t('service.unsupported.title');
				document.title = title + ' | '  + this.$t('core.title');
				let titleEl = document.getElementById('title');
				if (titleEl !== null) {
					titleEl.innerText = title;
				}
				this.status = null;
				this.$store.commit('spinner/HIDE');
				this.$toast.error(this.$t('service.errors.unsupportedService'));
				return;
			}
			ServiceService.getStatus(this.serviceName)
				.then((response) => {
					this.unsupported = false;
					this.active = response.data.active;
					this.enabled = response.data.enabled;
					this.status = response.data.status;
					this.$store.commit('spinner/HIDE');
				})
				.catch(this.handleError);
		},
		handleError(error) {
			this.$store.commit('spinner/HIDE');
			let response = error.response;
			if (response.status === 404) {
				this.missing = true;
				this.$toast.error(this.$t('service.errors.missingService'));
			}
			if (response.status === 500 &&
					response.data.message === 'Unsupported init system') {
				this.unsupported = false;
				this.$toast.error(this.$t('service.errors.unsupportedInit'));
			}
		},
		handleSuccess(action) {
			this.getStatus();
			this.$toast.success(this.$t('service.' + this.serviceName + '.messages.' + action));
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
};
</script>
