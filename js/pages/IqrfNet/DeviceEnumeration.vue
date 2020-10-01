<template>
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
								/>
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
								<CIcon alt='STD' class='rfModeIcon' src='/img/std-black.svg' />
								<strong>{{ $t('iqrfnet.enumeration.rfModes.std') }}</strong>
							</td>
							<td v-else-if='peripheralData.flags.rfModeLp'>
								<CIcon alt='LP' class='rfModeIcon' src='/img/lp-black.svg' />
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
	<div v-else />
</template>

<script>
import {CButton, CCard, CCardBody, CCardHeader, CIcon} from '@coreui/vue/src';
import IqrfNetService from '../../services/IqrfNetService';
import ProductService from '../../services/IqrfRepository/ProductService';
import {timeout} from '../../helpers/timeout';

export default {
	name: 'DeviceEnumeration',
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardHeader,
		CIcon,
	},
	props: {
		address: {
			type: Number,
			required: false,
			default: 0,
		},
	},
	data() {
		return {
			response: undefined,
			osData: undefined,
			peripheralData: undefined,
			product: undefined,
		};
	},
	created() {
		if (this.$store.getters.isSocketConnected) {
			this.$store.commit('spinner/SHOW');
			IqrfNetService.enumerateDevice(this.address);
		}
		this.unsubscribe = this.$store.subscribe(mutation => {
			if (mutation.type === 'SOCKET_ONOPEN') {
				this.$store.commit('spinner/SHOW');
				IqrfNetService.enumerateDevice(this.address);
				return;
			}
			if (mutation.type === 'SOCKET_ONSEND' &&
					mutation.payload.mType !== 'iqmeshNetwork_EnumerateDevice') {
				this.timeout = timeout('iqrfnet.enumeration.messages.failure', 30000);
			}
			if (mutation.type !== 'SOCKET_ONMESSAGE' ||
					mutation.payload.mType !== 'iqmeshNetwork_EnumerateDevice') {
				return;
			}
			clearTimeout(this.timeout);
			this.$store.commit('spinner/HIDE');
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
		this.unsubscribe();
	},
	metaInfo: {
		title: 'iqrfnet.enumeration.title',
	},
};
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
