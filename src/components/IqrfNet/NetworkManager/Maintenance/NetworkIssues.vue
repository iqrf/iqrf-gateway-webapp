<!--
Copyright 2017-2021 IQRF Tech s.r.o.
Copyright 2019-2021 MICRORISC s.r.o.

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
	<CCard class='border-0 card-margin-bottom'>
		<CCardBody>
			<CCardTitle>{{ $t('iqrfnet.networkManager.maintenance.networkIssues.title') }}</CCardTitle>
			<CForm>
				<CInputRadioGroup
					:checked.sync='issue'
					:options='issues'
				/>
				<CButton
					color='primary'
					@click='resolve'
				>
					{{ $t('iqrfnet.networkManager.maintenance.networkIssues.resolve') }}
				</CButton>
			</CForm>
		</CCardBody>
	</CCard>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CButton, CCard, CCardBody, CCardTitle, CForm, CInputRadioGroup} from '@coreui/vue/src';

import {NetworkIssueTypes} from '@/enums/IqrfNet/Maintenance';
import DaemonMessageOptions from '@/ws/DaemonMessageOptions';

import IqmeshNetworkService from '@/services/DaemonApi/IqmeshNetworkService';

import {IOption} from '@/interfaces/coreui';
import {MutationPayload} from 'vuex';

/**
 * Maintenance network issues component
 */
@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CCardTitle,
		CForm,
		CInputRadioGroup,
	},
})
export default class NetworkIssues extends Vue {
	/**
	 * @var {string} msgId Message ID
	 */
	private msgId = '';

	/**
	 * @var {NetworkIssueTypes} issue Network issue to solve
	 */
	private issue: NetworkIssueTypes = NetworkIssueTypes.INCONSISTENT_MIDS_IN_COORDINATOR;
	
	/**
	 * @const {Array<IOption>} issues Issues options
	 */
	private issues: Array<IOption> = [
		{
			value: NetworkIssueTypes.INCONSISTENT_MIDS_IN_COORDINATOR,
			label: this.$t('iqrfnet.networkManager.maintenance.networkIssues.options.inconsistentMidsInCoordinator').toString(),
			props: {
				description: this.$t('iqrfnet.networkManager.maintenance.networkIssues.help.inconsistentMidsInCoordinator').toString(),
			},
		},
		{
			value: NetworkIssueTypes.DUPLICATED_ADDRESSES,
			label: this.$t('iqrfnet.networkManager.maintenance.networkIssues.options.duplicatedAddresses').toString(),
			props: {
				description: this.$t('iqrfnet.networkManager.maintenance.networkIssues.help.duplicatedAddresses').toString(),
			},
		},
		{
			value: NetworkIssueTypes.USELESS_PREBONDED_NODES,
			label: this.$t('iqrfnet.networkManager.maintenance.networkIssues.options.uselessPrebondedNodes').toString(),
			props: {
				description: this.$t('iqrfnet.networkManager.maintenance.networkIssues.help.uselessPrebondedNodes').toString(),
			},
		},
	];

	/**
	 * Component unsubscribe function
	 */
	private unsubscribe: CallableFunction = () => {return;};

	/**
	 * Registers mutation handling
	 */
	created(): void {
		this.unsubscribe = this.$store.subscribe((mutation: MutationPayload) => {
			if (mutation.type !== 'daemonClient/SOCKET_ONMESSAGE') {
				return;
			}
			if (mutation.payload.data.msgId !== this.msgId) {
				return;
			}
			this.$store.dispatch('daemonClient/removeMessage', this.msgId);
			if (mutation.payload.mType === 'iqmeshNetwork_MaintenanceInconsistentMIDsInCoord') {
				this.handleInconsistentMids(mutation.payload.data);
			} else if (mutation.payload.mType === 'iqmeshNetwork_MaintenanceDuplicatedAddresses') {
				this.handleDuplicatedAddresses(mutation.payload.data);
			} else {
				this.handlePrebondedNodes(mutation.payload.data);
			}
		});
	}

	/**
	 * Unregisters mutation handling
	 */
	beforeDestroy(): void {
		this.$store.dispatch('daemonClient/removeMessage', this.msgId);
		this.unsubscribe();
	}

	/**
	 * Resolves network issue depending on selected option
	 */
	private resolve(): void {
		if (this.issue === NetworkIssueTypes.INCONSISTENT_MIDS_IN_COORDINATOR) {
			this.resolveInconsistentMids();
		} else if (this.issue === NetworkIssueTypes.DUPLICATED_ADDRESSES) {
			this.resolveDuplicatedAddresses();
		} else {
			this.resolvePrebondedNodes();
		}
	}

	/**
	 * Resolves inconsistent MIDs in coordinator issue
	 */
	private resolveInconsistentMids(): void {
		this.$store.dispatch('spinner/show', {
			timeout: 1620000,
			text: this.$t('iqrfnet.networkManager.maintenance.networkIssues.messages.inconsistentMids').toString(),
		});
		const options = new DaemonMessageOptions(null, 1620000, 'iqrfnet.networkManager.maintenance.networkIssues.messages.inconsistentMidsFailed', () => this.msgId = '');
		IqmeshNetworkService.maintenanceInconsistentMidsInCoordinator(options)
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Handles MaintenanceInconsistentMidsInCoord response
	 * @param response Response
	 */
	private handleInconsistentMids(response): void {
		if (response.status === 0) {
			this.handleSuccess('iqrfnet.networkManager.maintenance.networkIssues.messages.inconsistentMidsSuccess');
		} else if (response.status === 1003) {
			this.handleInfo('forms.messages.noBondedNodes');
		} else {
			this.handleFailure('iqrfnet.networkManager.maintenance.networkIssues.messages.inconsistentMidsFailed');
		}
	}

	/**
	 * Resolves duplicated addresses issue
	 */
	private resolveDuplicatedAddresses(): void {
		this.$store.dispatch('spinner/show', {
			timeout: 505000,
			text: this.$t('iqrfnet.networkManager.maintenance.networkIssues.messages.duplicatedAddresses').toString()
		});
		const options = new DaemonMessageOptions(null, 505000, 'iqrfnet.networkManager.maintenance.networkIssues.messages.duplicatedAddressesFailed', () => this.msgId = '');
		IqmeshNetworkService.maintenanceDuplicatedAddress(options)
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Handles MaintenanceDuplicatedAddresses response
	 * @param response Response
	 */
	private handleDuplicatedAddresses(response): void {
		if (response.status === 0) {
			this.handleSuccess('iqrfnet.networkManager.maintenance.networkIssues.messages.duplicatedAddressesSuccess');
		} else if (response.status === 1003) {
			this.handleInfo('forms.messages.noBondedNodes');
		} else {
			this.handleFailure('iqrfnet.networkManager.maintenance.networkIssues.messages.duplicatedAddressesFailed');
		}
	}

	/**
	 * Resolves useless prebonded nodes issue
	 */
	private resolvePrebondedNodes(): void {
		this.$store.dispatch('spinner/show', {
			timeout: 15000,
			text: this.$t('iqrfnet.networkManager.maintenance.networkIssues.messages.prebondedNodes').toString(),
		});
		const options = new DaemonMessageOptions(null, 15000, 'iqrfnet.networkManager.maintenance.networkIssues.messages.inconsistentMidsFailed', () => this.msgId = '');
		IqmeshNetworkService.maintenanceUselessPrebondedNodes(options)
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Handles MaintenanceUselessPrebondedNodes response
	 * @param response Response
	 */
	private handlePrebondedNodes(response): void {
		if (response.status === 0) {
			this.handleSuccess('iqrfnet.networkManager.maintenance.networkIssues.messages.prebondedNodesSuccess');
		} else if (response.status === 1003) {
			this.handleInfo('forms.messages.noBondedNodes');
		} else {
			this.handleFailure('iqrfnet.networkManager.maintenance.networkIssues.messages.prebondedNodesFailed');
		}
	}

	/**
	 * Hides spinner and shows success toast
	 * @param {string} message Toast message
	 */
	private handleSuccess(message: string): void {
		this.$store.dispatch('spinner/hide');
		this.$toast.success(this.$t(message).toString());
	}

	/**
	 * Hides spinner and shows error toast
	 * @param {string} message Toast message
	 */
	private handleFailure(message: string): void {
		this.$store.dispatch('spinner/hide');
		this.$toast.error(this.$t(message).toString());
	}

	/**
	 * Hides spinner and shows info toast
	 */
	private handleInfo(message: string): void {
		this.$store.dispatch('spinner/hide');
		this.$toast.info(this.$t(message).toString());
	}
}

</script>
