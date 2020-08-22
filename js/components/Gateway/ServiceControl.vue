<template>
	<div class='panel panel-default'>
		<div class='panel-body'>
			<div v-if='!missing && !unsupported'>
				<button v-if='!enabled' class='btn btn-success' @click='enable()'>
					{{ $t('service.actions.enable') }}
				</button>
				<button v-if='enabled' class='btn btn-danger' @click='disable()'>
					{{ $t('service.actions.disable') }}
				</button>
				<button v-if='!active' class='btn btn-success' @click='start()'>
					{{ $t('service.actions.start') }}
				</button>
				<button v-if='active' class='btn btn-danger' @click='stop()'>
					{{ $t('service.actions.stop') }}
				</button>
				<button v-if='active' class='btn btn-primary' @click='restart()'>
					{{ $t('service.actions.restart') }}
				</button>
				<button class='btn btn-default' @click='refreshStatus()'>
					{{ $t('service.actions.status') }}
				</button>
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
		</div>
	</div>
</template>

<script>
import ServiceService from '../../services/ServiceService';
import spinner from '../../spinner';

const whitelisted = ['iqrf-gateway-daemon', 'ssh', 'unattended-upgrades'];

export default {
	name: 'ServiceControl',
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
			spinner.showSpinner();
			this.serviceName = newVal;
			this.getStatus();
		}
	},
	created() {
		spinner.showSpinner();
		this.getStatus();
	},
	methods: {
		enable() {
			spinner.showSpinner();
			ServiceService.enable(this.serviceName)
				.then(() => {
					this.getStatus();
					this.$toast.success(this.$t('service.' + this.serviceName + '.messages.enable'));
				})
				.catch(this.handleError);
		},
		disable() {
			spinner.showSpinner();
			ServiceService.disable(this.serviceName)
				.then(() => {
					this.getStatus();
					this.$toast.success(this.$t('service.' + this.serviceName + '.messages.disable'));
				})
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
				spinner.hideSpinner();
				this.$toast.error(this.$t('service.errors.unsupportedService'));
				return;
			}
			ServiceService.getStatus(this.serviceName)
				.then((response) => {
					this.unsupported = false;
					this.active = response.data.active;
					this.enabled = response.data.enabled;
					this.status = response.data.status;
					spinner.hideSpinner();
				})
				.catch(this.handleError);
		},
		handleError(error) {
			spinner.hideSpinner();
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
		refreshStatus() {
			spinner.showSpinner();
			this.getStatus();
		},
		restart() {
			spinner.showSpinner();
			ServiceService.restart(this.serviceName)
				.then(() => {
					this.getStatus();
					this.$toast.success(this.$t('service.' + this.serviceName + '.messages.restart'));
				})
				.catch(this.handleError);
		},
		start() {
			spinner.showSpinner();
			ServiceService.start(this.serviceName)
				.then(() => {
					this.getStatus();
					this.$toast.success(this.$t('service.' + this.serviceName + '.messages.start'));
				})
				.catch(this.handleError);
		},
		stop() {
			spinner.showSpinner();
			ServiceService.stop(this.serviceName)
				.then(() => {
					this.getStatus();
					this.$toast.success(this.$t('service.' + this.serviceName + '.messages.stop'));
				})
				.catch(this.handleError);
		}
	},
};
</script>

<style scoped>

</style>
