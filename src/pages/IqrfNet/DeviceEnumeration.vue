<template>
	<div>
		<h1>{{ $t('iqrfnet.enumeration.title') }}</h1>
		<div v-if='response !== undefined'>
			<CCard>
				<CCardHeader>{{ $t('iqrfnet.enumeration.deviceInfo') }}</CCardHeader>
				<CCardBody>
					<table class='table table-striped'>
						<tbody>
							<tr>
								<th>{{ $t('iqrfnet.enumeration.deviceAddr') }}</th>
								<td>{{ response.deviceAddr }}</td>
							</tr>
							<tr>
								<th>{{ $t('iqrfnet.enumeration.hwpid') }}</th>
								<td>{{ peripheralData.hwpId }}</td>
							</tr>
							<tr>
								<th>{{ $t('iqrfnet.enumeration.manufacturer') }}</th>
								<td>{{ response.manufacturer }}</td>
							</tr>
							<tr v-if='product !== undefined'>
								<th>{{ $t('iqrfnet.enumeration.product') }}</th>
								<td><a :href='product.homePage'>{{ product.name }}</a></td>
							</tr>
							<tr v-if='product !== undefined'>
								<th>{{ $t('iqrfnet.enumeration.picture') }}</th>
								<td>
									<img
										:alt='response.product'
										class='productPicture'
										:src='product.picture'
									>
								</td>
							</tr>
							<tr v-else>
								<th>{{ $t('iqrfnet.enumeration.product') }}</th>
								<td>{{ response.product }}</td>
							</tr>
						</tbody>
					</table>
				</CCardBody>
			</CCard>
			<CCard>
				<CCardHeader>{{ $t('iqrfnet.enumeration.trInfo') }}</CCardHeader>
				<CCardBody>
					<table class='table table-striped'>
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
									<RfModeStd alt='STD' class='rfModeIcon' />
									<strong>{{ $t('iqrfnet.enumeration.rfModes.std') }}</strong>
								</td>
								<td v-else-if='peripheralData.flags.rfModeLp'>
									<RfModeLp alt='LP' class='rfModeIcon' />
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
					</table>
					<CButton color='primary' to='/iqrfnet/network/'>
						{{ $t('iqrfnet.enumeration.back') }}
					</CButton>
				</CCardBody>
			</CCard>
		</div>
	</div>
</template>

<script lang='ts'>
import Vue from 'vue';
import {MutationPayload} from 'vuex';
import {CButton, CCard, CCardBody, CCardHeader} from '@coreui/vue/src';
import IqrfNetService from '../../services/IqrfNetService';
import ProductService from '../../services/IqrfRepository/ProductService';
import RfModeLp from '../../assets/lp-black.svg';
import RfModeStd from '../../assets/std-black.svg';

export default Vue.extend({
	name: 'DeviceEnumeration',
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		RfModeLp,
		RfModeStd,
	},
	props: {
		address: {
			type: Number,
			required: false,
			default: 0,
		},
	},
	data(): any {
		return {
			response: undefined,
			osData: undefined,
			peripheralData: undefined,
			product: undefined,
			msgId: null,
			timeout: null,
		};
	},
	created() {
		if (this.$store.getters.isSocketConnected) {
			IqrfNetService.enumerateDevice(this.address, 30000, 'iqrfnet.enumeration.messages.failure', () => this.msgId = null)
				.then((msgId) => this.msgId = msgId);
		}
		this.unsubscribe = this.$store.subscribe((mutation: MutationPayload) => {
			if (mutation.type === 'SOCKET_ONOPEN') {
				IqrfNetService.enumerateDevice(this.address, 30000, 'iqrfnet.enumeration.messages.failure', () => this.msgId = null)
					.then((msgId) => this.msgId = msgId);
				return;
			}
			if (mutation.type === 'SOCKET_ONSEND' &&
					mutation.payload.mType !== 'iqmeshNetwork_EnumerateDevice') {
				this.timeout = setTimeout(() => {
					this.$store.dispatch('spinner/hide');
					this.$router.push('/iqrf/network/');
					this.$toast.error(
						this.$t('iqrfnet.enumeration.messages.failure').toString()
					);
				}, 31000);
			}
			if (mutation.type !== 'SOCKET_ONMESSAGE' ||
					mutation.payload.mType !== 'iqmeshNetwork_EnumerateDevice') {
				return;
			}
			clearTimeout(this.timeout);
			this.$store.dispatch('spinner/hide');
			const response = mutation.payload;
			if (response.data.status !== 0) {
				this.$router.push('/iqrfnet/network/');
				this.$toast.error(
					this.$t('iqrfnet.enumeration.messages.failure').toString()
				);
				return;
			}
			const data = response.data.rsp;
			if (data) {
				this.response = data;
				this.peripheralData = data.peripheralEnumeration;
				this.osData = data.osRead;
				ProductService.get(this.peripheralData.hwpId)
					.then((response) => {
						this.product = response.data;
					})
					.catch((error) => {
						if (error.response !== undefined && error.response.status === 404) {
							return;
						}
						this.$toast.error(
							this.$t('iqrfnet.enumeration.messages.repositoryUnavailable')
								.toString()
						);
					});
			}
		});
	},
	beforeDestroy() {
		clearTimeout(this.timeout);
		this.unsubscribe();
	},
	metaInfo: {
		title: 'iqrfnet.enumeration.title',
	},
});
</script>

<style scoped>
@media (max-width: 768px) {
	.productPicture {
		max-height: 100%;
		max-width: 100%;
	}
}

@media (min-width: 768px) {
	.productPicture {
		max-height: 33%;
		max-width: 33%;
	}
}
</style>
