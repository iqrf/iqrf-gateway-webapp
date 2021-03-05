<template>
	<div>
		<header class='d-flex'>
			<h1 class='mr-auto'>
				{{ $t('cloud.pixla.title') }}
			</h1>
			<div v-if='!missing && !unsupported'>
				<CButton
					color='primary'
					href='https://www.pixla.online/'
					target='_blank'
				>
					{{ $t('cloud.pixla.dashboard') }}
				</CButton>
			</div>
		</header>
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
						<td v-if='service !== null' class='d-flex'>
							<div class='mr-auto'>
								{{ $t('states.' + (service.enabled ? 'enabled' : 'disabled')) }},
								{{ $t('service.states.' + (service.active ? 'active' : 'inactive')) }}
							</div>
							<CButton
								v-if='!service.enabled'
								color='success'
								size='sm'
								@click='enable()'
							>
								{{ $t('service.actions.enable') }}
							</CButton> <CButton
								v-if='service.enabled'
								color='danger'
								size='sm'
								@click='disable()'
							>
								{{ $t('service.actions.disable') }}
							</CButton> <CButton
								color='primary'
								size='sm'
								@click='restart()'
							>
								{{ $t('service.actions.restart') }}
							</CButton>
						</td>
					</tr>
				</tbody>
			</table>
			<PixlaTokenEditor :show.sync='showEditor' @token-updated='getToken' />
		</CCard>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {AxiosError, AxiosResponse} from 'axios';
import {CButton, CCard} from '@coreui/vue/src';
import {NavigationGuardNext, Route} from 'vue-router';
import PixlaTokenEditor from '../../components/Cloud/PixlaTokenEditor.vue';
import PixlaService from '../../services/PixlaService';
import ServiceService, {ServiceStatus} from '../../services/ServiceService';

@Component({
	components: {
		CButton,
		CCard,
		PixlaTokenEditor,
	},
	beforeRouteEnter(to: Route, from: Route, next: NavigationGuardNext): void {
		next((vm: Vue) => {
			if (!vm.$store.getters['features/isEnabled']('pixla')) {
				vm.$toast.error(
					vm.$t('service.gwman-client.messages.disabled').toString()
				);
				vm.$router.push(from.path);
			}
		});
	},
	metaInfo: {
		title: 'cloud.pixla.title',
	},
})

/**
 * Pixla cloud service manager card
 */
export default class PixlaControl extends Vue {
	/**
	 * @var {boolean} showEditor Should pixla token editor be rendered
	 */
	private showEditor = false

	/**
	 * @constant {string} serviceName Pixla service name
	 */
	private serviceName = 'gwman-client'

	/**
	 * @var {string|null} token Pixla token
	 */
	private token: string|null = null

	/**
	 * @var {boolean} missing Indicates whether the pixla service is not installed
	 */
	private missing = false

	/**
	 * @var {boolean} unsupported Indicates whether the pixla service is supported
	 */
	private unsupported = false

	/**
	 * @var {ServiceStatus|null} service Pixla service status object
	 */
	private service: ServiceStatus|null = null

	/**
	 * Vue lifecycle hook created
	 */
	created(): void {
		this.$store.commit('spinner/SHOW');
		this.getToken();
		this.getStatus();
	}

	/**
	 * Enables the Pixla service
	 */
	private enable(): void {
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
	}

	/**
	 * Disables the Pixla service
	 */
	private disable(): void {
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
	}

	/**
	 * Restarts the Pixla service
	 */
	private restart(): void {
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
	}

	/**
	 * Retrieves status of the Pixla service
	 */
	private getStatus(): void {
		ServiceService.getStatus(this.serviceName)
			.then((status: ServiceStatus) => {
				this.service = status;
				this.unsupported = false;
				this.$store.commit('spinner/HIDE');
			})
			.catch(this.handleError);
	}

	/**
	 * Retrieves the Pixla service token
	 */
	private getToken(): void {
		PixlaService.getToken()
			.then((token: string) => {
				this.token = token;
				this.$store.commit('spinner/HIDE');
			})
			.catch(() => {
				this.token = null;
				this.$store.commit('spinner/HIDE');
			});
	}

	/**
	 * Axios response error handler
	 * @param {AxiosError} error Axios response error
	 */
	private handleError(error: AxiosError): void {
		this.$store.commit('spinner/HIDE');
		if (error.response === undefined) {
			return;
		}
		const response: AxiosResponse = error.response;
		if (response.status === 404) {
			this.missing = true;
			this.$toast.error(this.$t('service.errors.missingService').toString());
		}
		if (response.status === 500 &&
				response.data.message === 'Unsupported init system') {
			this.unsupported = false;
			this.$toast.error(this.$t('service.errors.unsupportedInit').toString());
		}
	}
	
}
</script>
