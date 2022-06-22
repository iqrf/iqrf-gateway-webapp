<template>
	<CCard>
		<CCardHeader>{{ $t('network.mobile.modem.title') }}</CCardHeader>
		<CCardBody>
			<table
				v-if='modems.length > 0'
				style='width: 100%;'
			>
				<tr>
					<th>{{ $t('network.mobile.modem.product') }}</th>
					<th>{{ $t('network.mobile.modem.imei') }}</th>
					<th>{{ $t('network.mobile.modem.interface') }}</th>
					<th>{{ $t('network.mobile.modem.state') }}</th>
					<th>{{ $t('network.mobile.modem.operator') }}</th>
					<th>{{ $t('network.mobile.table.signal') }}</th>
					<th>{{ $t('network.mobile.modem.technology') }}</th>
				</tr>
				<tr
					v-for='(modem, i) of modems'
					:key='i'
				>
					<td>{{ `${modem.manufacturer} ${modem.model}` }}</td>
					<td>{{ modem.imei }}</td>
					<td>{{ modem.interface }}</td>
					<td>{{ modem.state === 'failed' ? `${modem.state}: ${modem.error}` : modem.state }}</td>
					<td>{{ modem.operator || '--' }}</td>
					<td>{{ modem.signal || '--' }}</td>
					<td>{{ modem.technology || '--' }}</td>
				</tr>
			</table>
		</CCardBody>
	</CCard>
</template>

<script lang='ts'>
import {Component, Prop, Vue} from 'vue-property-decorator';
import {CCard, CCardBody, CCardHeader} from '@coreui/vue/src';

import {IModem} from '@/interfaces/network';

@Component({
	components: {
		CCard,
		CCardBody,
		CCardHeader,
	},
})

/**
 * Modem simple table component
 */
export default class Modems extends Vue {

	@Prop({default: []}) modems!: Array<IModem>;
}
</script>
