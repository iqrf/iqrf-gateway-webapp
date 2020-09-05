<template>
	<CCard body-wrapper>
		<table class='table table-striped'>
			<tbody>
				<tr>
					<th>{{ $t('cloud.pixla.token') }}</th>
					<td>{{ token }}</td>
				</tr>
				<tr>
					<th>{{ $t('service.status') }}</th>
					<td v-if='missing'>
						{{ $t('service.states.missing') }}
					</td>
					<td v-if='unsupported'>
						{{ $t('service.states.unsupported') }}
					</td>
					<td v-else>
						{{ $t('service.states.' + (enabled ? 'enabled' : 'disabled')) }},
						{{ $t('service.states.' + (active ? 'active' : 'inactive')) }}
					</td>
				</tr>
			</tbody>
		</table>
		<div v-if='!missing && !unsupported'>
			<CButton v-if='!enabled' color='success' @click='enable()'>
				{{ $t('service.actions.enable') }}
			</CButton>
			<CButton v-if='enabled' color='danger' @click='disable()'>
				{{ $t('service.actions.disable') }}
			</CButton>
			<CButton color='primary' href='https://www.pixla.online/' target='_blank'>
				{{ $t('cloud.pixla.dashboard') }}
			</CButton>
		</div>
	</CCard>
</template>

<script>
import {CButton, CCard} from '@coreui/vue';
import PixlaService from '../../services/PixlaService';
import ServiceService from '../../services/ServiceService';
import spinner from '../../spinner';

export default {
	name: 'PixlaControl',
	components: {
		CButton,
		CCard,
	},
	data() {
		return {
			serviceName: 'gwman-client',
			token: null,
			active: false,
			enabled: false,
			missing: false,
			status: null,
			unsupported: false,
		};
	},
	created() {
		this.getToken();
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
		getToken() {
			PixlaService.getToken()
				.then((response) => {
					this.token = response.data.token;
				})
				.catch(() => {
					this.token = null;
				});
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
		},
	},
};
</script>

<style scoped>

</style>
