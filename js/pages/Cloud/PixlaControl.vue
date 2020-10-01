<template>
	<CCard body-wrapper>
		<table class='table table-striped'>
			<tbody>
				<tr>
					<th>{{ $t('cloud.pixla.token') }}</th>
					<td class='d-flex'>
						<div class='mr-auto'>
							{{ token }}
						</div>
						<CButton color='primary' size='sm' @click='showEditor = true'>
							{{ $t('forms.edit') }}
						</CButton>
					</td>
				</tr>
				<tr>
					<th>{{ $t('service.status') }}</th>
					<td v-if='missing'>
						{{ $t('service.states.missing') }}
					</td>
					<td v-if='unsupported'>
						{{ $t('service.states.unsupported') }}
					</td>
					<td v-else class='d-flex'>
						<div class='mr-auto'>
							{{ $t('service.states.' + (enabled ? 'enabled' : 'disabled')) }},
							{{ $t('service.states.' + (active ? 'active' : 'inactive')) }}
						</div>
						<CButton
							v-if='!enabled'
							color='success'
							size='sm'
							@click='enable()'
						>
							{{ $t('service.actions.enable') }}
						</CButton>
						<CButton
							v-if='enabled'
							color='danger'
							size='sm'
							@click='disable()'
						>
							{{ $t('service.actions.disable') }}
						</CButton>
					</td>
				</tr>
			</tbody>
		</table>
		<div v-if='!missing && !unsupported'>
			<CButton color='primary' href='https://www.pixla.online/' target='_blank'>
				{{ $t('cloud.pixla.dashboard') }}
			</CButton>
		</div>
		<PixlaTokenEditor :show.sync='showEditor' @tokenUpdated='getToken' />
	</CCard>
</template>

<script>
import {CButton, CCard} from '@coreui/vue/src';
import PixlaTokenEditor from '../../components/Cloud/PixlaTokenEditor';
import PixlaService from '../../services/PixlaService';
import ServiceService from '../../services/ServiceService';

export default {
	name: 'PixlaControl',
	components: {
		CButton,
		CCard,
		PixlaTokenEditor,
	},
	data() {
		return {
			showEditor: false,
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
		this.$store.commit('spinner/SHOW');
		this.getToken();
		this.getStatus();
	},
	methods: {
		enable() {
			this.$store.commit('spinner/SHOW');
			ServiceService.enable(this.serviceName)
				.then(() => {
					this.getStatus();
					this.$toast.success(
						this.$t('service.' + this.serviceName + '.messages.enable')
							.toString()
					);
				})
				.catch(this.handleError);
		},
		disable() {
			this.$store.commit('spinner/SHOW');
			ServiceService.disable(this.serviceName)
				.then(() => {
					this.getStatus();
					this.$toast.success(
						this.$t('service.' + this.serviceName + '.messages.disable')
							.toString()
					);
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
					this.$store.commit('spinner/HIDE');
				})
				.catch(this.handleError);
		},
		getToken() {
			PixlaService.getToken()
				.then((token) => {
					this.token = token;
					this.$store.commit('spinner/HIDE');
				})
				.catch(() => {
					this.token = null;
					this.$store.commit('spinner/HIDE');
				});
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
		restart() {
			this.$store.commit('spinner/SHOW');
			ServiceService.restart(this.serviceName)
				.then(() => {
					this.getStatus();
					this.$toast.success(
						this.$t('service.' + this.serviceName + '.messages.restart')
							.toString()
					);
				})
				.catch(this.handleError);
		},
		start() {
			this.$store.commit('spinner/SHOW');
			ServiceService.start(this.serviceName)
				.then(() => {
					this.getStatus();
					this.$toast.success(
						this.$t('service.' + this.serviceName + '.messages.start')
							.toString()
					);
				})
				.catch(this.handleError);
		},
		stop() {
			this.$store.commit('spinner/SHOW');
			ServiceService.stop(this.serviceName)
				.then(() => {
					this.getStatus();
					this.$toast.success(
						this.$t('service.' + this.serviceName + '.messages.stop')
							.toString()
					);
				})
				.catch(this.handleError);
		},
	},
	beforeRouteEnter(to, from, next) {
		next(vm => {
			if (!vm.$store.getters['features/isEnabled']('pixla')) {
				vm.$toast.error(
					vm.$t('cloud.pixla.messages.disabled').toString()
				);
				vm.$router.push(from.path);
			}
		});
	},
	metaInfo: {
		title: 'cloud.pixla.title',
	},
};
</script>
