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
								{{ $t('service.states.' + (service.enabled ? 'enabled' : 'disabled')) }},
								{{ $t('service.states.' + (service.active ? 'active' : 'inactive')) }}
							</div>
							<CButton
								v-if='!service.enabled'
								color='success'
								size='sm'
								@click='enable()'
							>
								{{ $t('service.actions.enable') }}
							</CButton>
							<CButton
								v-if='service.enabled'
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

export default class PixlaControl extends Vue {
	private showEditor = false
	private serviceName = 'gwman-client'
	private token: string|null = null
	private missing = false
	private unsupported = false
	private service: ServiceStatus|null = null

	created(): void {
		this.$store.commit('spinner/SHOW');
		this.getToken();
		this.getStatus();
	}

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

	private getStatus(): void {
		ServiceService.getStatus(this.serviceName)
			.then((status: ServiceStatus) => {
				this.service = status;
				this.unsupported = false;
				this.$store.commit('spinner/HIDE');
			})
			.catch(this.handleError);
	}

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
