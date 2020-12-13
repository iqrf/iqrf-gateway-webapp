<template>
	<div>
		<h1>{{ $t('gateway.troubleshooter.title') }}</h1>
		<CCard>
			<CCardBody>
				<table class='table'>
					<tbody>
						<tr>
							<th>{{ $t('gateway.troubleshooter.table.headers.item') }}</th>
							<th>{{ $t('gateway.troubleshooter.table.headers.state') }}</th>
							<th>{{ $t('gateway.troubleshooter.table.headers.action') }}</th>
						</tr>
						<tr v-for='entry of entries' :key='entries.indexOf(entry)'>
							<th>{{ entry.item }}</th>
							<td>{{ entry.state }}</td>
							<td>{{ entry.action }}</td>
						</tr>
					</tbody>
				</table>
			</CCardBody>
		</CCard>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CButton, CCard, CCardBody} from '@coreui/vue/src';
import FormErrorHandler from '../../helpers/FormErrorHandler';
import DaemonConfigurationService from '../../services/DaemonConfigurationService';
import {AxiosError, AxiosResponse} from 'axios';
import {IComponent} from '../../interfaces/daemonComponent';
import { Dictionary } from 'vue-router/types/router';

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
	},
	metaInfo: {
		title: 'gateway.troubleshooter.title',
	},
})

/**
 * Gateway Troubleshooter component
 */
export default class Troubleshooter extends Vue {
	/**
	 * @var {Array<Dictionary<string>>}
	 */
	private entries: Array<Dictionary<string>> = []
	
	/**
	 * @var {Dictionary<boolean>} interfaces Dictionary of IQRF interfaces
	 */
	private interfaces: Dictionary<boolean> = {
		'iqrf::IqrfCdc': false,
		'iqrf::IqrfSpi': false,
		'iqrf::IqrfUart': false,
	}

	/**
	 * @constant {Array<string>} whitelistedComponents Array of IQRF interface components for filtering
	 */
	private whitelistedComponents: Array<string> = [
		'iqrf::IqrfCdc',
		'iqrf::IqrfSpi',
		'iqrf::IqrfUart',
	]
	
	/**
	 * Vue lifecycle hook mounted
	 */
	mounted(): void {
		this.getInterfaces();
	}

	/**
	 * Retrieves active IQRF interfaces
	 */
	private getInterfaces(): void {
		this.$store.commit('spinner/SHOW');
		DaemonConfigurationService.getComponent('')
			.then((response: AxiosResponse) => {
				this.$store.commit('spinner/HIDE');
				for (const component of response.data.components as Array<IComponent>) {
					if (!this.whitelistedComponents.includes(component.name)) {
						continue;
					}
					this.interfaces[component.name] = component.enabled;
				}
				this.interfaceEntry();
			})
			.catch((error: AxiosError) => FormErrorHandler.configError(error));
	}

	/**
	 * Creates the interface entry with current state and suggested actions if an action is necessary
	 */
	private interfaceEntry() {
		let messageTokens: Array<string> = [];
		for (const [key, value] of Object.entries(this.interfaces)) {
			if (value) {
				messageTokens.push(key);
			}
		}
		let entry = {
			item: this.$t('gateway.troubleshooter.table.interface').toString(),
			state: messageTokens.join(', ')
		};
		let action = this.$t('gateway.troubleshooter.actions.noAction').toString();
		if (messageTokens.length === 0) {
			Object.assign(entry, {state: this.$t('gateway.troubleshooter.states.noInterface').toString()});
			action = this.$t('gateway.troubleshooter.actions.noInterface').toString();
		} else if (messageTokens.length > 1) {
			action = this.$t('gateway.troubleshooter.actions.multipleInterfaces').toString();
		}
		Object.assign(entry, {action: action});
		this.entries.push(entry);
	}
}
</script>

<style scoped>
table {
    table-layout: fixed;
}
</style>
