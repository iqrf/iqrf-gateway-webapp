<template>
	<div>
		<h1>{{ title }}</h1>
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
			<span v-if='unknown'>
				{{ $t('service.states.unknown') }}
			</span>
			<span v-else>
				{{ $t('service.states.' + (service.enabled ? 'enabled' : 'disabled')) }},
				{{ $t('service.states.' + (service.active ? 'active' : 'inactive')) }}
			</span>
			<br><br>
			<pre v-if='service.status !== null' class='log'>{{ service.status }}</pre>
		</CCard>
	</div>
</template>

<script lang='ts'>
import {Component, Prop, Vue, Watch} from 'vue-property-decorator';
import {CButton, CCard} from '@coreui/vue/src';
import ServiceService from '../../services/ServiceService';
import {AxiosError} from 'axios';

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

interface IService {
	active: boolean
	enabled: boolean
	status: string|null
}

@Component({
	components: {
		CButton,
		CCard,
	},
	metaInfo() {
		return {
			title: (this as unknown as ServiceControl).pageTitle
		};
	}
})

export default class ServiceControl extends Vue {
	private missing = false;
	private unknown =  false;
	private unsupported = false;
	private service: IService = {
		active: false,
		enabled: false,
		status: null
	}
	private title = '';

	@Prop({required: true}) serviceName!: string;

	beforeRouteEnter(to, from, next): void {
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
	}

	@Watch('serviceName')
	getServiceStatus(): void {
		this.$store.commit('spinner/SHOW');
		this.getStatus();
	}

	created(): void {
		this.$store.commit('spinner/SHOW');
		this.getStatus();
	}

	public enable(): void {
		this.$store.commit('spinner/SHOW');
		ServiceService.enable(this.serviceName)
			.then(() => (this.handleSuccess('enable')))
			.catch(this.handleError);
	}

	public disable(): void {
		this.$store.commit('spinner/SHOW');
		ServiceService.disable(this.serviceName)
			.then(() => (this.handleSuccess('disable')))
			.catch(this.handleError);
	}

	public getStatus(): void {
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
	}

	public handleError(error: AxiosError): void {
		this.$store.commit('spinner/HIDE');
		let response = error.response;
		if (response === undefined) {
			this.unknown = true;
			this.$toast.error(this.$t('service.errors.processTimeout').toString());
			return;
		}
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

	public handleSuccess(action: string): void {
		this.getStatus();
		this.$toast.success(
			this.$t('service.' + this.serviceName + '.messages.' + action)
				.toString()
		);
	}

	public refreshStatus(): void {
		this.$store.commit('spinner/SHOW');
		this.getStatus();
	}

	public restart(): void {
		this.$store.commit('spinner/SHOW');
		ServiceService.restart(this.serviceName)
			.then(() => (this.handleSuccess('restart')))
			.catch(this.handleError);
	}

	public start(): void {
		this.$store.commit('spinner/SHOW');
		ServiceService.start(this.serviceName)
			.then(() => (this.handleSuccess('start')))
			.catch(this.handleError);
	}

	public stop(): void {
		this.$store.commit('spinner/SHOW');
		ServiceService.stop(this.serviceName)
			.then(() => (this.handleSuccess('stop')))
			.catch(this.handleError);
	}

	get pageTitle(): string {
		const title = whitelisted.includes(this.serviceName) ?
			'service.' + this.serviceName + '.title' :
			'service.unsupported.title';
		return this.title = this.$t(title).toString();
	}
}
</script>

<style scoped>
.btn {
  margin: 0 3px 0 0;
}
</style>
