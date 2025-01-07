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
		<v-card>
			<v-card-text>
				<ValidationObserver v-slot='{invalid}'>
					<v-form>
						<fieldset>
							<h5>{{ $t('iqrfnet.networkManager.autoNetwork.form.params.title') }}</h5>
							<ValidationProvider
								v-slot='{errors, touched, valid}'
								rules='integer|required|between:0,3'
								:custom-messages='{
									integer: $t("iqrfnet.networkManager.autoNetwork.errors.actionRetries"),
									required: $t("iqrfnet.networkManager.autoNetwork.errors.actionRetries"),
									between: $t("iqrfnet.networkManager.autoNetwork.errors.actionRetries"),
								}'
							>
								<v-text-field
									v-model.number='params.actionRetries'
									type='number'
									min='0'
									max='3'
									:label='$t("iqrfnet.networkManager.autoNetwork.form.params.actionRetries")'
									:success='touched ? valid : null'
									:error-messages='errors'
								/>
							</ValidationProvider>
							<ValidationProvider
								v-slot='{errors, touched, valid}'
								rules='integer|required|between:0,7'
								:custom-messages='{
									integer: $t("iqrfnet.networkManager.autoNetwork.errors.discoveryTxPower"),
									required: $t("iqrfnet.networkManager.autoNetwork.errors.discoveryTxPower"),
									between: $t("iqrfnet.networkManager.autoNetwork.errors.discoveryTxPower"),
								}'
							>
								<v-text-field
									v-model.number='params.discoveryTxPower'
									type='number'
									min='0'
									max='7'
									:label='$t("iqrfnet.networkManager.autoNetwork.form.params.discoveryTxPower")'
									:success='touched ? valid : null'
									:error-messages='errors'
								/>
							</ValidationProvider>
							<v-checkbox
								v-model='params.discoveryBeforeStart'
								:label='$t("iqrfnet.networkManager.autoNetwork.form.params.discoveryBeforeStart")'
								dense
							/>
							<v-checkbox
								v-model='params.skipDiscoveryEachWave'
								:label='$t("iqrfnet.networkManager.autoNetwork.form.params.skipDiscoveryEachWave")'
								dense
							/>
							<v-checkbox
								v-model='params.unbondUnrespondingNodes'
								:label='$t("iqrfnet.networkManager.autoNetwork.form.params.unbondUnrespondingNodes")'
								dense
							/>
							<v-checkbox
								v-model='params.skipPrebonding'
								:label='$t("iqrfnet.networkManager.autoNetwork.form.params.skipPrebonding")'
								dense
							/>
						</fieldset>
						<fieldset>
							<h4>{{ $t('iqrfnet.networkManager.autoNetwork.form.stopConditions.title') }}</h4>
							<v-row>
								<v-col cols='12' md='6'>
									<ValidationProvider
										v-slot='{errors, touched, valid}'
										rules='integer|required|between:1,127'
										:custom-messages='{
											integer: $t("forms.errors.integer"),
											required: $t("iqrfnet.networkManager.autoNetwork.errors.waves"),
											between: $t("iqrfnet.networkManager.autoNetwork.errors.waves")
										}'
									>
										<v-text-field
											v-model.number='waves'
											type='number'
											min='1'
											max='127'
											:label='$t("iqrfnet.networkManager.autoNetwork.form.stopConditions.waves")'
											:is-valid='touched ? valid : null'
											:invalid-feedback='$t(errors[0])'
											:disabled='!useWaves'
										>
											<template #prepend>
												<v-simple-checkbox
													v-model='useWaves'
													color='primary'
												/>
											</template>
										</v-text-field>
									</ValidationProvider>
								</v-col>
								<v-col cols='12' md='6'>
									<ValidationProvider
										v-slot='{errors, touched, valid}'
										rules='integer|required|between:1,127'
										:custom-messages='{
											integer: $t("forms.errors.integer"),
											required: $t("iqrfnet.networkManager.autoNetwork.errors.emptyWaves"),
											between: $t("iqrfnet.networkManager.autoNetwork.errors.emptyWaves")
										}'
									>
										<v-text-field
											v-model.number='emptyWaves'
											type='number'
											min='1'
											max='127'
											:label='$t("iqrfnet.networkManager.autoNetwork.form.stopConditions.emptyWaves")'
											:success='touched ? valid : null'
											:error-messages='errors'
										/>
									</ValidationProvider>
								</v-col>
							</v-row>
							<v-row>
								<v-col cols='12' md='6'>
									<v-select
										v-model='nodeCondition'
										:label='$t("iqrfnet.networkManager.autoNetwork.form.stopConditions.nodeCount")'
										:items='nodeOptions'
										:disabled='!useNodes'
									>
										<template #prepend>
											<v-simple-checkbox
												v-model='useNodes'
												color='primary'
											/>
										</template>
									</v-select>
								</v-col>
								<v-col cols='12' md='6'>
									<ValidationProvider
										v-slot='{errors, touched, valid}'
										rules='integer|between:1,239|required'
										:custom-messages='{
											between: $t("iqrfnet.networkManager.autoNetwork.errors." + (nodeCondition === StopConditionNode.TOTAL ? "totalNodes" : "newNodes")),
											integer: $t("iqrfnet.networkManager.autoNetwork.errors." + (nodeCondition === StopConditionNode.TOTAL ? "totalNodes" : "newNodes")),
											required: $t("iqrfnet.networkManager.autoNetwork.errors." + (nodeCondition === StopConditionNode.TOTAL ? "totalNodes" : "newNodes")),
										}'
									>
										<v-text-field
											v-model.number='nodeCount'
											type='number'
											min='1'
											max='239'
											:success='touched ? valid : null'
											:error-messages='errors'
											:disabled='!useNodes'
										/>
									</ValidationProvider>
								</v-col>
							</v-row>
							<v-checkbox
								v-model='abortOnTooManyNodesFound'
								:label='$t("iqrfnet.networkManager.autoNetwork.form.params.abortOnTooManyNodesFound")'
								dense
								:disabled='!useNodes'
							/>
						</fieldset>
						<fieldset>
							<h5>{{ $t('iqrfnet.networkManager.autoNetwork.form.bondingControl.title') }}</h5>
							<v-checkbox
								v-model='useAddressSpace'
								:label='$t("iqrfnet.networkManager.autoNetwork.form.bondingControl.addressSpace.use")'
								:hint='$t("iqrfnet.networkManager.autoNetwork.notes.addressSpace")'
								persistent-hint
								dense
							/>
							<ValidationProvider
								v-slot='{errors, touched, valid}'
								:rules='{
									addressSpace: true,
									required: useAddressSpace,
								}'
								:custom-messages='{
									required: $t("iqrfnet.networkManager.autoNetwork.errors.addressSpace"),
									addressSpace: $t("iqrfnet.networkManager.autoNetwork.errors.addressSpace"),
								}'
							>
								<ExtendedTextField
									v-model='addressSpace'
									:label='$t("iqrfnet.networkManager.autoNetwork.form.bondingControl.addressSpace.title")'
									:description='$t("iqrfnet.networkManager.autoNetwork.notes.addressSpaceFormat")'
									:success='touched ? valid : null'
									:errors='errors'
									:disabled='!useAddressSpace'
									@change='simplifyAddressSpace'
								>
									<template #append-outer>
										<v-chip
											label
										>
											{{ addresses.length }}
										</v-chip>
									</template>
								</ExtendedTextField>
							</ValidationProvider>
							<v-checkbox
								v-model='useMid'
								:label='$t("iqrfnet.networkManager.autoNetwork.form.bondingControl.mid.use")'
								:description='$t("iqrfnet.networkManager.autoNetwork.notes.midControl")'
								dense
							/>
							<v-file-input
								v-model='file'
								class='pt-0'
								:label='$t("iqrfnet.networkManager.autoNetwork.form.bondingControl.mid.file")'
								accept='.csv,.txt'
								:disabled='!useMid'
								:prepend-icon='null'
								prepend-inner-icon='mdi-file-outline'
								@change='readFile($event)'
							>
								<template #append-outer>
									<MidListModal
										ref='midlist'
										:activator-disabled='!useMid'
										:list.sync='mid.midList'
										:invalid.sync='midInvalid'
									/>
								</template>
							</v-file-input>
							<v-checkbox
								v-model='mid.midFiltering'
								class='mb-4'
								:label='$t("iqrfnet.networkManager.autoNetwork.form.bondingControl.mid.filter")'
								:hint='$t("iqrfnet.networkManager.autoNetwork.notes.midFilter")'
								:disabled='!useMid'
								persistent-hint
								dense
							/>
							<v-checkbox
								v-model='useOverlappingNetworks'
								:label='$t("iqrfnet.networkManager.autoNetwork.form.bondingControl.overlappingNetworks.use")'
								dense
							/>
							<v-row>
								<v-col cols='12' md='6'>
									<ValidationProvider
										v-slot='{errors, touched, valid}'
										rules='integer|required|between:2,50'
										:custom-messages='{
											integer: $t("iqrfnet.networkManager.autoNetwork.errors.networks"),
											required: $t("iqrfnet.networkManager.autoNetwork.errors.networks"),
											between: $t("iqrfnet.networkManager.autoNetwork.errors.networks"),
										}'
									>
										<v-text-field
											v-model.number='overlappingNetworks.networks'
											type='number'
											min='2'
											max='50'
											:label='$t("iqrfnet.networkManager.autoNetwork.form.bondingControl.overlappingNetworks.networks")'
											:success='touched ? valid : null'
											:error-messages='errors'
											:disabled='!useOverlappingNetworks'
										/>
									</ValidationProvider>
								</v-col>
								<v-col cols='12' md='6'>
									<ValidationProvider
										v-slot='{errors, touched, valid}'
										rules='integer|required|between:1,50'
										:custom-messages='{
											integer: $t("iqrfnet.networkManager.autoNetwork.errors.network"),
											required: $t("iqrfnet.networkManager.autoNetwork.errors.network"),
											between: $t("iqrfnet.networkManager.autoNetwork.errors.network"),
										}'
									>
										<v-text-field
											v-model.number='overlappingNetworks.network'
											type='number'
											min='1'
											max='50'
											:label='$t("iqrfnet.networkManager.autoNetwork.form.bondingControl.overlappingNetworks.network")'
											:success='touched ? valid : null'
											:error-messages='errors'
											:disabled='!useOverlappingNetworks'
										/>
									</ValidationProvider>
								</v-col>
							</v-row>
						</fieldset>
						<fieldset>
							<h4>{{ $t('iqrfnet.networkManager.autoNetwork.form.hwpidFiltering.title') }}</h4>
							<v-checkbox
								v-model='useHwpid'
								:label='$t("iqrfnet.networkManager.autoNetwork.form.hwpidFiltering.use")'
								:hint='$t("iqrfnet.networkManager.autoNetwork.notes.hwpidFilter")'
								persistent-hint
								dense
							/>
							<ValidationProvider
								v-slot='{errors, touched, valid}'
								:rules='{
									hwpidFilter: true,
									required: useHwpid,
								}'
								:custom-messages='{
									hwpidFilter: $t("iqrfnet.networkManager.autoNetwork.errors.hwpidFilter"),
									required: $t("iqrfnet.networkManager.autoNetwork.errors.hwpidFilter"),
								}'
							>
								<ExtendedTextField
									v-model='hwpidList'
									:label='$t("iqrfnet.networkManager.autoNetwork.form.hwpidFiltering.list")'
									:description='$t("iqrfnet.networkManager.autoNetwork.notes.hwpidFilterFormat")'
									:success='touched ? valid : null'
									:errors='errors'
									:disabled='!useHwpid'
								/>
							</ValidationProvider>
						</fieldset>
						<v-btn
							color='primary'
							:disabled='invalid'
							@click='startAutonetwork'
						>
							{{ $t('forms.runAutonetwork') }}
						</v-btn>
					</v-form>
				</ValidationObserver>
			</v-card-text>
		</v-card>
		<AutoNetworkResultModal ref='result' @finished='updateDevices' />
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import AutoNetworkResultModal from '@/components/IqrfNet/NetworkManager/AutoNetwork/AutoNetworkResultModal.vue';
import MidListModal from '@/components/IqrfNet/NetworkManager/AutoNetwork/MidListModal.vue';
import ExtendedTextField from '@/components/ExtendedTextField.vue';

import {between, integer, required} from 'vee-validate/dist/rules';
import {hwpidFilter} from '@/helpers/validationRules/Iqrfnet';
import {StopConditionNode} from '@/enums/IqrfNet/Autonetwork';
import IqrfNetService from '@/services/IqrfNetService';
import DaemonMessageOptions from '@/ws/DaemonMessageOptions';

import {IAtnwParams, IAtnwOverlappingNetworks, IAtnwMid, IAtnwMidList, IAtnwMidErrorList} from '@/interfaces/DaemonApi/Iqmesh/Autonetwork';
import {ISelectItem} from '@/interfaces/Vuetify';
import {MutationPayload} from 'vuex';

@Component({
	components: {
		AutoNetworkResultModal,
		ExtendedTextField,
		MidListModal,
		ValidationObserver,
		ValidationProvider
	},
	data: () => ({
		StopConditionNode,
	}),
})

/**
 * AutoNetwork card for IqrfNet network manager
 */
export default class AutoNetwork extends Vue {
	/**
	 * @var {string} msgId Daemon API message ID
	 */
	private msgId = '';

	/**
	 * @var {IAtnwParams} autoNetwork Basic AutoNetwork process configuration
	 */
	private params: IAtnwParams = {
		actionRetries: 1,
		discoveryBeforeStart: false,
		discoveryTxPower: 7,
		skipDiscoveryEachWave: false,
		unbondUnrespondingNodes: false,
		skipPrebonding: false,
	};

	/**
	 * @var {boolean} abortOnTooManyNodesFound Abort Autonetwork when enough nodes are found
	 */
	private abortOnTooManyNodesFound = false;

	/**
	 * @var {boolean} useWaves Use waves
	 */
	private useWaves = false;

	/**
	 * @var {number} waves Number of waves
	 */
	private waves = 10;

	/**
	 * @var {number} emptyWaves Number of empty waves
	 */
	private emptyWaves = 2;

	/**
	 * @var {boolean} useNodes Use nodes found stop condition
	 */
	private useNodes = false;

	/**
	 * @var {StopConditionNode} nodeCondition Node count stop condition
	 */
	private nodeCondition: StopConditionNode = StopConditionNode.TOTAL;

	/**
	 * @var {Array<ISelectItem>} nodeOptions Node count stop condition options
	 */
	private nodeOptions: Array<ISelectItem> = [
		{
			value: StopConditionNode.TOTAL,
			text: this.$t('iqrfnet.networkManager.autoNetwork.form.stopConditions.total').toString(),
		},
		{
			value: StopConditionNode.NEW,
			text: this.$t('iqrfnet.networkManager.autoNetwork.form.stopConditions.new').toString(),
		},
	];

	/**
	 * @var {number} nodeCount
	 */
	private nodeCount = 239;

	/**
	 * @var {boolean} useOverlappingNetworks Use overlapping networks settings
	 */
	private useOverlappingNetworks = false;

	/**
	 * @var {IAtnwOverlappingNetworks} overlappingNetworks AutoNetwork overlapping networks settings
	 */
	private overlappingNetworks: IAtnwOverlappingNetworks = {
		networks: 2,
		network: 1,
	};

	/**
	 * @var {boolean} useAddressSpace Use address space
	 */
	private useAddressSpace = false;

	/**
	 * @var {string} addressSpace Address space
	 */
	private addressSpace = '';

	/**
	 * @var {Array<number>} addresses Addresses in address space
	 */
	private addresses: Array<number> = [];

	/**
	 * @var {boolean} useMid Use MID filtering
	 */
	private useMid = false;

	/**
	 * @var {File|null} file MID list file to import
	 */
	private file: File|null = null;

	/**
	 * @var {IAtnwMid} mid MID control
	 */
	private mid: IAtnwMid = {
		midList: [],
		midFiltering: false,
	};

	/**
	 * @var {Array<IAtnwMidErrorList>} midInvalid Invalid MID list records
	 */
	private midInvalid: Array<IAtnwMidErrorList> = [];

	/**
	 * @var {boolean} useHwpid Use HWPID filtering
	 */
	private useHwpid = false;

	/**
	 * @var {string} hwpidList String of HWPIDs to filter nodes by
	 */
	private hwpidList = '';

	/**
	 * Component unsubscribe function
	 */
	private unsubscribe: CallableFunction = () => {return;};

	/**
	 * Initializes validation rules and websocket mutation handling
	 */
	created(): void {
		extend('between', between);
		extend('integer', integer);
		extend('required', required);
		extend('hwpidFilter', hwpidFilter);
		extend('addressSpace', (val: string) => {
			const regex = RegExp('^(,?(([1-9]|[1-9]\\d|1\\d{2}|2[0-3]\\d)|(<([1-9]|[1-9]\\d|1\\d{2}|2[0-3]\\d);([1-9]|[1-9]\\d|1\\d{2}|2[0-3]\\d)>)))+$');
			const passed = regex.test(val);
			if (!passed) {
				return false;
			}
			const items: Array<string> = val.split(',');
			const addresses: Set<number> = new Set<number>();
			if (items.length === 0) {
				this.addresses = [];
				return true;
			}
			for (let i = 0; i < items.length; ++i) {
				const item = items[i];
				if (item.startsWith('<')) {
					const range = item.substring(1, item.length -1).split(';').map((str: string) => {
						return Number(str);
					});
					if (range[1] < range[0]) {
						return false;
					}
					for (let j = range[0]; j <= range[1]; ++j) {
						addresses.add(j);
					}
				} else {
					addresses.add(Number(item));
				}
			}
			this.addresses = Array.from(addresses).sort((a: number, b: number) => {return a - b;});
			return true;
		});
		this.unsubscribe = this.$store.subscribe((mutation: MutationPayload) => {
			if (mutation.type === 'daemonClient/SOCKET_ONERROR' ||
				mutation.type === 'daemonClient/SOCKET_ONCLOSE') {
				this.$store.dispatch('daemonClient/removeMessage', this.msgId);
				this.autoNetworkError(
					'iqrfnet.networkManager.autoNetwork.resultModal.errorMessage',
					'Lost connection to IQRF Gateway Daemon.'
				);
				return;
			}
			if (mutation.type !== 'daemonClient/SOCKET_ONMESSAGE') {
				return;
			}
			if (mutation.payload.data.msgId !== this.msgId) {
				return;
			}
			if (mutation.payload.mType === 'iqmeshNetwork_AutoNetwork') {
				this.handleAutonetwork(mutation.payload.data);
			} else if (mutation.payload.mType === 'messageError') {
				this.$store.commit('spinner/HIDE');
				this.$store.dispatch('daemonClient/removeMessage', this.msgId);
				this.autoNetworkError(
					'iqrfnet.networkManager.autoNetwork.resultModal.errorMessage',
					this.$t('iqrfnet.networkManager.autoNetwork.errors.messageError')
				);
			}
		});
	}

	/**
	 * Clears possible registered message IDs and unregisters mutation handling
	 */
	beforeDestroy(): void {
		this.$store.dispatch('daemonClient/removeMessage', this.msgId);
		this.unsubscribe();
	}

	/**
	 * Calculates address space ranges to simplify input and updates address space input
	 */
	private simplifyAddressSpace(): void {
		if (this.addresses.length === 0 || this.addressSpace.length === 0) {
			return;
		}
		let updatedAddressSpace = '';
		let first = 0, last = 0;
		for (let i = 0; i < this.addresses.length; ++i) {
			first = last = this.addresses[i];
			while ((this.addresses[i] + 1) === this.addresses[i + 1]) {
				last = this.addresses[i + 1];
				i++;
			}
			if (first === last) {
				updatedAddressSpace += `${first},`;
			} else {
				updatedAddressSpace += `<${first};${last}>,`;
			}
		}
		this.addressSpace = updatedAddressSpace.slice(0, -1);
	}

	/**
	 * Attempts to read file contents
	 * @param {File} file File
	 */
	private readFile(file: File): void {
		if (!file) {
			return;
		}
		this.$store.commit('spinner/SHOW');
		file.text()
			.then((content: string) => {
				this.parseContent(content);
				(this.$refs.midlist as MidListModal).showModal();
			})
			.catch(() => {
				this.$store.commit('spinner/HIDE');
				this.$toast.error(
					this.$t('iqrfnet.networkManager.autoNetwork.messages.readFailed').toString()
				);
			});
	}

	/**
	 * Validates and parses file content
	 * @param {string} content MID list file content
	 */
	private parseContent(content: string): void {
		const lines = content.trim().split('\n');
		if (lines.length === 0) {
			return;
		}
		const midRegex = new RegExp(/^[0-9a-f]{8}$/, 'i');
		const addrRegex = new RegExp(/^\d+$/);
		const valid: Array<IAtnwMidList> = [];
		const invalid: Array<IAtnwMidErrorList> = [];
		for (let i = 0; i < lines.length; ++i) {
			const line = lines[i].trim();
			if (line.length === 0 || line.startsWith(',,') || line.startsWith(';;')) {
				continue;
			}
			const tokens = line.split(/[;,]/);
			if (!midRegex.test(tokens[0])) {
				invalid.push({
					line: i+1,
					content: line,
					error: this.$t('iqrfnet.networkManager.autoNetwork.midListErrors.midFormat').toString()
				});
				continue;
			}
			const mid = tokens[0];
			const midIdx = valid.findIndex((item: IAtnwMidList) => item.deviceMID === mid);
			if (midIdx !== -1) {
				invalid.push({
					line: i+1,
					content: line,
					error: this.$t('iqrfnet.networkManager.autoNetwork.midListErrors.midDuplicate', {line: midIdx + 1}).toString()
				});
				continue;
			}
			const entry: IAtnwMidList = {
				deviceMID: mid,
			};
			if (tokens.length >= 2 && tokens[1].length > 0) {
				if (!addrRegex.test(tokens[1])) {
					invalid.push({
						line: i+1,
						content: line,
						error: this.$t('iqrfnet.networkManager.autoNetwork.midListErrors.addrFormat').toString()
					});
					continue;
				}
				const addr = Number.parseInt(tokens[1]);
				if (addr < 1 || addr > 239) {
					invalid.push({
						line: i+1,
						content: line,
						error: this.$t('iqrfnet.networkManager.autoNetwork.midListErrors.addrRange').toString()
					});
					continue;
				}
				const addrIdx = valid.findIndex((item: IAtnwMidList) => item.deviceAddr === addr);
				if (addrIdx !== -1) {
					invalid.push({
						line: i+1,
						content: line,
						error: this.$t('iqrfnet.networkManager.autoNetwork.midListErrors.addrDuplicate', {line: midIdx + 1}).toString()
					});
					continue;
				}
				entry.deviceAddr = addr;
			}
			if (tokens.length >= 3) {
				const notes = tokens.slice(2);
				if (notes.length !== 0) {
					entry.note = notes.join(', ');
				}
			}
			valid.push(entry);
		}
		this.mid.midList = valid;
		this.midInvalid = invalid;
		this.$store.commit('spinner/HIDE');
	}

	/**
	 * Checks autonetwork params and builds autonetwork request parameters object
	 */
	private buildAutonetworkParams() {
		const params = JSON.parse(JSON.stringify(this.params));
		if (this.useOverlappingNetworks) {
			Object.assign(params, {overlappingNetworks: this.overlappingNetworks});
		}
		if (this.useAddressSpace) {
			Object.assign(params, {addressSpace: this.addresses});
		}
		if (this.useMid) {
			Object.assign(params, {midList: this.mid.midList});
			Object.assign(params, {midFiltering: this.mid.midFiltering});
		}
		if (this.useHwpid) {
			Object.assign(params, {hwpidFiltering: this.hwpidList.split(',').map((i) => parseInt(i))});
		}
		const stopConditions = {
			emptyWaves: this.emptyWaves,
			abortOnTooManyNodesFound: this.abortOnTooManyNodesFound,
		};
		if (this.useNodes) {
			Object.assign(stopConditions, this.nodeCondition === StopConditionNode.TOTAL ? {numberOfTotalNodes: this.nodeCount} : {numberOfNewNodes: this.nodeCount});
		}
		if (this.useWaves) {
			Object.assign(stopConditions, {waves: this.waves});
		}
		Object.assign(params, {stopConditions: stopConditions});
		return params;
	}

	/**
	 * Builds AutoNetwork configuration object and performs the AutoNetwork process
	 */
	private startAutonetwork(): void {
		const autoNetworkParams = this.buildAutonetworkParams();
		IqrfNetService.autoNetwork(autoNetworkParams, new DaemonMessageOptions(null))
			.then((msgId: string) => this.msgId = msgId);
		this.showResultDialog();
	}

	/**
	 * Shows autonetwork result modal window
	 */
	private showResultDialog(): void {
		const waves = this.useWaves ? this.waves : 0;
		const result = (this.$refs.result as AutoNetworkResultModal);
		result.showModal(waves);
	}

	/**
	 * Handles Autonetwork response
	 * @param response Response
	 */
	private handleAutonetwork(response): void {
		if (response.status === 0) {
			this.autoNetworkProgress(response.rsp);
			if (response.rsp.lastWave) {
				this.$store.dispatch('daemonClient/removeMessage', this.msgId);
			}
			return;
		}
		this.$store.dispatch('daemonClient/removeMessage', this.msgId);
		this.autoNetworkError('iqrfnet.networkManager.autoNetwork.resultModal.errorMessage', response.statusStr);
	}

	/**
	 * Creates progress message for running AutoNetwork process, used in spinner
	 * @param response Response
	 */
	private autoNetworkProgress(response): void {
		const result = (this.$refs.result as AutoNetworkResultModal);
		const code = response.waveStateCode;
		if (code < 0) {
			this.autoNetworkError('iqrfnet.networkManager.autoNetwork.resultModal.errorMessage', response.waveState);
			return;
		}
		const progress = response.progress;
		const totalNodes = response.nodesNr ?? null;
		const newNodes = response.newNodesNr ?? null;
		const lastWave = response.lastWave ?? false;
		result.updateProgress(response.wave, lastWave, progress, response.waveState, totalNodes, newNodes);
	}

	/**
	 * Stops autonetwork result progress updating and sets error message
	 * @param message Error message
	 */
	private autoNetworkError(message, error): void {
		const translation = this.$t(message, {message: error}).toString();
		(this.$refs.result as AutoNetworkResultModal).stopProgress(translation);
	}

	/**
	 * Emits device update event
	 */
	private updateDevices(): void {
		this.$emit('update-devices');
	}
}
</script>
