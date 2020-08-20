<template>
	<div class='panel panel-default'>
		<div class='panel-body'>
			<div v-if='!missing'>
				<button class='btn btn-success' @click='enable()' v-if='!enabled'
								role="button">
					{{ $t('service.actions.enable') }}
				</button>
				<button class='btn btn-danger' @click='disable()' v-if='enabled'
								role="button">
					{{ $t('service.actions.disable') }}
				</button>
			</div>
			<br/><br/>
			<strong>{{ $t('service.status') }}: </strong>
			<span v-if='missing'>
				{{ $t('service.states.missing') }}
			</span>
			<span v-else>
				{{ $t('service.states.' + (enabled ? 'enabled' : 'disabled')) }}
				{{ $t('service.states.' + (active ? 'active' : 'inactive')) }}
			</span>
			<br/><br/>
			<pre v-if='status' class='log'>{{ status }}</pre>
		</div>
	</div>
</template>

<script>
import ServiceService from '../services/ServiceService';
import spinner from '../spinner';

export default {
	name: 'service-control',
	props: {
		serviceName: String
	},
	created() {
		spinner.showSpinner();
		ServiceService.getStatus(this.serviceName)
				.then((response) => {
					this.active = response.data.active;
					this.enabled = response.data.enabled;
					this.status = response.data.status;
					spinner.hideSpinner();
				})
				.catch(() => {
					this.missing = true;
					spinner.hideSpinner();
				});
	},
	data() {
		return {
			active: false,
			enabled: false,
			status: null,
			missing: false
		};
	},
	methods: {
		enable() {
			spinner.showSpinner();
			ServiceService.enable(this.serviceName).then(
					() => {
						spinner.hideSpinner();
					}
			).catch(() => (spinner.hideSpinner()));
		},
		disable() {
			spinner.showSpinner();
			ServiceService.disable(this.serviceName).then(
					() => {
						spinner.hideSpinner();
					}
			).catch(() => (spinner.hideSpinner()));
		}
	}
};
</script>

<style scoped>

</style>
