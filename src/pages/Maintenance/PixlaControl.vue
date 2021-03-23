<template>
	<div>
		<header class='d-flex'>
			<h1 class='mr-auto'>
				{{ $t('maintenance.pixla.title') }}
			</h1>
			<div v-if='!missing && !unsupported'>
				<CButton
					color='primary'
					href='https://www.pixla.online/'
					target='_blank'
				>
					{{ $t('maintenance.pixla.dashboard') }}
				</CButton>
			</div>
		</header>
		<CCard body-wrapper>
			<div class='box'>
				<div>
					<b>{{ $t('service.status') }}</b>
				</div>
				<div v-if='missing'>
					{{ $t('service.states.missing') }}
				</div>
				<div v-if='unsupported'>
					{{ $t('service.states.unsupported') }}
				</div>
				<div v-if='service !== null'>
					{{ $t('states.' + (service.enabled ? 'enabled' : 'disabled')) }},
					{{ $t('service.states.' + (service.active ? 'running' : 'stopped')) }}
				</div>
				<div v-if='service !== null'>
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
				</div>
			</div>
			<div class='box'>
				<div>
					<b>{{ $t('maintenance.pixla.token') }}</b>
				</div>
				<div>
					{{ token }}
				</div>
				<div>
					<CButton color='primary' size='sm' @click='showEditor = true'>
						{{ $t('forms.edit') }}
					</CButton>
				</div>
			</div>
			<PixlaTokenEditor :show.sync='showEditor' @token-updated='getToken' />
		</CCard>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CButton, CCard} from '@coreui/vue/src';
import PixlaTokenEditor from '../../components/Maintenance/PixlaTokenEditor.vue';

import {extendedErrorToast, pixlaErrorToast} from '../../helpers/errorToast';
import {NavigationGuardNext, Route} from 'vue-router';
import PixlaService from '../../services/PixlaService';
import ServiceService, {ServiceStatus} from '../../services/ServiceService';

import {AxiosError} from 'axios';

@Component({
	components: {
		CButton,
		CCard,
		PixlaTokenEditor,
	},
	beforeRouteEnter(to: Route, from: Route, next: NavigationGuardNext): void {
		next((vm: Vue) => {
			let message = '';
			if (!vm.$store.getters['features/isEnabled']('maintenance')) {
				message = vm.$t('maintenance.disabled').toString();
			} else if (!vm.$store.getters['features/isEnabled']('pixla')) {
				message = vm.$t('service.gwman-client.messages.disabled').toString();
			}
			if (message !== '') {
				vm.$toast.error(message);
				vm.$router.push(from.path);
			}
		});
	},
	metaInfo: {
		title: 'maintenance.pixla.title',
	},
})

/**
 * Pixla maintenance service manager card
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
			.catch((error: AxiosError) => pixlaErrorToast(error, 'service.messages.enableFailed'));
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
			.catch((error: AxiosError) => pixlaErrorToast(error, 'service.messages.disableFailed'));
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
			.catch((error: AxiosError) => pixlaErrorToast(error, 'service.messages.restartFailed'));
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
			.catch((error: AxiosError) => pixlaErrorToast(error, 'service.messages.statusFailed'));
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
			.catch((error: AxiosError) => extendedErrorToast(error, 'maintenance.pixla.messages.fetchFailed'));
	}

}
</script>
