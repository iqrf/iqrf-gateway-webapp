<!--
Copyright 2017-2025 IQRF Tech s.r.o.
Copyright 2019-2025 MICRORISC s.r.o.

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software,
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied
See the License for the specific language governing permissions and
limitations under the License.
-->
<template>
	<div>
		<h1>{{ $t('iqrfnet.enumeration.title') }}</h1>
		<div v-if='response !== null && osData !== null && peripheralData !== null'>
			<v-card class='mb-5'>
				<v-card-title>{{ $t('iqrfnet.enumeration.deviceInfo') }}</v-card-title>
				<v-card-text>
					<v-simple-table>
						<tbody>
							<tr>
								<th>{{ $t('iqrfnet.enumeration.deviceAddr') }}</th>
								<td>{{ response.deviceAddr }}</td>
							</tr>
							<tr>
								<th>{{ $t('iqrfnet.enumeration.hwpid') }}</th>
								<td>{{ peripheralData.hwpId }} ({{ peripheralData.hwpId.toString(16).padStart(4, '0') }})</td>
							</tr>
							<tr>
								<th>{{ $t('iqrfnet.enumeration.hwpidVer') }}</th>
								<td>{{ peripheralData.hwpIdVer }}</td>
							</tr>
							<tr v-if='response.manufacturer !== ""'>
								<th>{{ $t('iqrfnet.enumeration.manufacturer') }}</th>
								<td>{{ response.manufacturer }}</td>
							</tr>
							<tr>
								<th>{{ $t('iqrfnet.enumeration.product') }}</th>
								<td v-if='product !== null'>
									<a :href='product.homePage'>{{ product.name }}</a>
								</td>
								<td v-else-if='response.product !== ""'>
									{{ response.product }}
								</td>
								<td v-else>
									{{ $t('iqrfnet.enumeration.uncertifiedProduct') }}
								</td>
							</tr>
							<tr v-if='product !== null'>
								<th>{{ $t('iqrfnet.enumeration.picture') }}</th>
								<td>
									<v-img
										:alt='response.product'
										class='product-picture'
										:src='product.picture'
										contain
										min-width='256px'
										min-height='256px'
									/>
								</td>
							</tr>
						</tbody>
					</v-simple-table>
				</v-card-text>
			</v-card>
			<v-card>
				<v-card-title>{{ $t('iqrfnet.enumeration.trInfo') }}</v-card-title>
				<v-card-text>
					<v-simple-table>
						<tbody>
							<tr>
								<th>{{ $t('iqrfnet.enumeration.trType') }}</th>
								<td>{{ osData.trMcuType.trType }}</td>
							</tr>
							<tr>
								<th>{{ $t('iqrfnet.enumeration.mid') }}</th>
								<td>{{ osData.mid }}</td>
							</tr>
							<tr>
								<th>{{ $t('iqrfnet.enumeration.osVersion') }}</th>
								<td>{{ osData.osVersion }} ({{ osData.osBuild }})</td>
							</tr>
							<tr>
								<th>{{ $t('iqrfnet.enumeration.dpaVersion') }}</th>
								<td>{{ peripheralData.dpaVer }}</td>
							</tr>
							<tr>
								<th>{{ $t('iqrfnet.enumeration.rfMode') }}</th>
								<td v-if='peripheralData.flags.rfModeStd'>
									<img :src='RfModeStd' alt='STD' class='rf-mode-icon'>
									<strong>{{ $t('iqrfnet.enumeration.rfModes.std') }}</strong>
								</td>
								<td v-else-if='peripheralData.flags.rfModeLp'>
									<img :src='RfModeLp' alt='LP' class='rf-mode-icon'>
									<strong>{{ $t('iqrfnet.enumeration.rfModes.lp') }}</strong>
								</td>
							</tr>
							<tr>
								<th>{{ $t('iqrfnet.enumeration.rssi') }}</th>
								<td>{{ osData.rssi }}</td>
							</tr>
							<tr>
								<th>{{ $t('iqrfnet.enumeration.supplyVoltage') }}</th>
								<td>{{ osData.supplyVoltage }}</td>
							</tr>
						</tbody>
					</v-simple-table>
					<v-btn
						color='primary'
						:to='returnButtonRoute'
					>
						{{ $t('forms.back') }}
					</v-btn>
				</v-card-text>
			</v-card>
		</div>
	</div>
</template>

<script lang='ts'>
import {ProductService} from '@iqrf/iqrf-repository-client/services';
import {Product} from '@iqrf/iqrf-repository-client/types';
import {AxiosError} from 'axios';
import {Component, Prop, Vue} from 'vue-property-decorator';
import {NavigationGuardNext, Route} from 'vue-router';
import {MutationPayload} from 'vuex';

import IqrfNetService from '@/services/IqrfNetService';
import RfModeLp from '@/assets/lp-black.svg';
import RfModeStd from '@/assets/std-black.svg';
import {IDeviceEnumeration, OsInfo, PeripheralEnumeration} from '@/interfaces/DaemonApi/Dpa';
import {DaemonClientState} from '@/interfaces/wsClient';
import {useRepositoryClient} from '@/services/IqrfRepositoryClient';

@Component({
	data: () => {
		return {
			RfModeLp,
			RfModeStd,
		};
	},
	metaInfo: {
		title: 'iqrfnet.enumeration.title',
	},
	beforeRouteEnter(to: Route, from: Route, next: NavigationGuardNext): void {
		next((vm: Vue) => {
			(vm as DeviceEnumeration).fromRoute = from;
		});
	},
})

/**
 * Device enumeration page component
 */
export default class DeviceEnumeration extends Vue {
	/**
	 * @property {number} address Address of device to enumerate
	 */
	@Prop({required: false, default: 0}) address!: number;

	/**
	 * @var {string} msgId Daemon API message ID
	 */
	private msgId = '';

	/**
	 * @var {OsInfo|null} osData Device OS information
	 */
	public osData: OsInfo|null = null;

	/**
	 * @var {PeripheralEnumeration|null} peripheralData Device peripheral information
	 */
	public peripheralData: PeripheralEnumeration|null = null;

	/**
	 * @var {Product|null} product Device product information
	 */
	public product: Product|null = null;

	/**
	 * @var {IDeviceEnumeration} response Device enumeration data
	 */
	public response: IDeviceEnumeration|null = null;

	/**
	 * Component unsubscribe function
	 */
	private unsubscribe: CallableFunction = () => {return;};

	/**
	 * Component unwatch function
	 */
	private unwatch: CallableFunction = () => {return;};

	/**
	 * @var {Route|null} fromRoute Previous route
	 */
	private fromRoute: Route|null = null;

	/**
	 * @property {ProductService|undefined} productService Product service
   * @private
   */
	private productService!: ProductService;

	/**
	 * @property {string} returnButtonRoute Computes route for return button
	 */
	get returnButtonRoute(): string {
		if (this.fromRoute === null || this.fromRoute.path === '/') {
			return '/iqrfnet/network/';
		}
		return this.fromRoute.path;
	}

	/**
	 * Vue lifecycle hook created
	 */
	async created(): Promise <void> {
		const repositoryClient = await useRepositoryClient();
		this.productService = repositoryClient.getProductService();
		this.unsubscribe = this.$store.subscribe((mutation: MutationPayload) => {
			if (mutation.type !== 'daemonClient/SOCKET_ONMESSAGE') {
				return;
			}
			if (mutation.payload.data.msgId !== this.msgId) {
				return;
			}
			this.$store.dispatch('spinner/hide');
			this.$store.dispatch('daemonClient/removeMessage', this.msgId);
			if (mutation.payload.mType === 'iqmeshNetwork_EnumerateDevice') {
				this.handleEnumerateResponse(mutation.payload.data);
			}
		});
		if (this.$store.getters['daemonClient/isConnected']) {
			this.enumerate();
		} else {
			this.unwatch = this.$store.watch(
				(_state: DaemonClientState, getter) => getter['daemonClient/isConnected'],
				(newVal: boolean, oldVal: boolean) => {
					if (!oldVal && newVal) {
						this.enumerate();
						this.unwatch();
					}
				}
			);
		}
	}

	/**
	 * Vue lifecycle hook beforeDestroy
	 */
	beforeDestroy(): void {
		this.$store.dispatch('daemonClient/removeMessage', this.msgId);
		this.unwatch();
		this.unsubscribe();
	}

	/**
	 * Performs enumeration on device specified by address
	 */
	private enumerate(): void {
		this.$store.dispatch('spinner/show', {timeout: 300000});
		IqrfNetService.enumerateDevice(this.address, 300000, 'iqrfnet.enumeration.messages.failure', () => this.msgId = '')
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Handles device enumeration request response
	 * @param response Response
	 */
	private handleEnumerateResponse(response): void {
		if (response.status !== 0) {
			this.$router.push('/iqrfnet/network/');
			this.$toast.error(
				this.$t('iqrfnet.enumeration.messages.failure').toString()
			);
			return;
		}
		const data = response.rsp;
		if (data) {
			this.response = data;
			this.peripheralData = data.peripheralEnumeration;
			this.osData = data.osRead;
			if (this.peripheralData === null) {
				return;
			}
			const hwpId = this.peripheralData.hwpId;
			if ((hwpId & 0xf) === 0xf) {
				return;
			}
			this.getProductInformation(hwpId);
		}
	}

	/**
	 * Retrieves product information from IQRF repository
	 * @param {number} hwpId HW profile ID
	 */
	private getProductInformation(hwpId: number): void {
		this.productService.get(hwpId)
			.then((response: Product) => {
				this.product = response;
			})
			.catch((error: AxiosError) => {
				if (error.response !== undefined && error.response.status === 404) {
					return;
				}
				this.$toast.error(
					this.$t('iqrfnet.enumeration.messages.repositoryUnavailable')
						.toString()
				);
			});
	}
}
</script>

<style scoped>
@media (width <= 768px) {
	.product-picture {
		max-height: 100%;
		max-width: 100%;
	}
}

@media (width >= 768px) {
	.product-picture {
		max-height: 33%;
		max-width: 33%;
	}
}
</style>
