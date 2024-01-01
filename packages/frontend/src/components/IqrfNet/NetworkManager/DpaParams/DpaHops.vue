<!--
Copyright 2017-2024 IQRF Tech s.r.o.
Copyright 2019-2024 MICRORISC s.r.o.

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
	<v-card flat tile>
		<v-card-title>{{ $t('iqrfnet.networkManager.dpaParams.dpaHops.title') }}</v-card-title>
		<v-card-text>
			<small class='text-muted'>{{ $t('iqrfnet.networkManager.dpaParams.dpaHops.notes.hops') }}</small>
			<ValidationObserver v-slot='{invalid}'>
				<v-form>
					<v-row align='center'>
						<v-col cols='12' lg='6' md='3'>
							<ValidationProvider
								v-slot='{errors, touched, valid}'
								:rules='{
									between: {
										min: rqDRM ? 0 : 1,
										max: rqDOM ? 255 : 239,
									},
									integer: true,
									required: true,
								}'
								:custom-messages='{
									between: $t("iqrfnet.networkManager.dpaParams.dpaHops.errors.requestHops"),
									integer: $t("iqrfnet.networkManager.dpaParams.dpaHops.errors.requestHops"),
									required: $t("iqrfnet.networkManager.dpaParams.dpaHops.errors.requestHops"),
								}'
							>
								<v-text-field
									v-model.number='requestHops'
									type='number'
									min='1'
									max='239'
									:label='$t("iqrfnet.networkManager.dpaParams.dpaHops.requestHops")'
									:success='touched ? valid : null'
									:error-messages='errors'
									:disabled='rqDOM || rqDRM'
								/>
							</ValidationProvider>
						</v-col>
						<v-col cols='12' md='3'>
							<v-tooltip top>
								<template #activator='{attrs, on}'>
									<v-checkbox
										v-model='rqDOM'
										:label='$t("iqrfnet.networkManager.dpaParams.dpaHops.optimizeDom")'
										v-bind='attrs'
										dense
										v-on='on'
										@click='rqDOMChange'
									/>
								</template>
								<span>{{ $t('iqrfnet.networkManager.dpaParams.dpaHops.notes.dom') }}</span>
							</v-tooltip>
						</v-col>
						<v-col cols='12' md='3'>
							<v-tooltip top>
								<template #activator='{attrs, on}'>
									<v-checkbox
										v-model='rqDRM'
										:label='$t("iqrfnet.networkManager.dpaParams.dpaHops.optimizeDrm")'
										v-bind='attrs'
										dense
										v-on='on'
										@click='rqDRMChange'
									/>
								</template>
								{{ $t('iqrfnet.networkManager.dpaParams.dpaHops.notes.drm') }}
							</v-tooltip>
						</v-col>
					</v-row>
					<v-row align='center'>
						<v-col cols='12' lg='6' md='3'>
							<ValidationProvider
								v-slot='{errors, touched, valid}'
								:rules='{
									between: {
										min: 1,
										max: rspDOM ? 255 : 239,
									},
									integer: true,
									required: true,
								}'
								:custom-messages='{
									between: $t("iqrfnet.networkManager.dpaParams.dpaHops.errors.responseHops"),
									integer: $t("iqrfnet.networkManager.dpaParams.dpaHops.errors.responseHops"),
									required: $t("iqrfnet.networkManager.dpaParams.dpaHops.errors.responseHops"),
								}'
							>
								<v-text-field
									v-model.number='responseHops'
									type='number'
									min='1'
									max='239'
									:label='$t("iqrfnet.networkManager.dpaParams.dpaHops.responseHops")'
									:success='touched ? valid : null'
									:error-messages='errors'
									:disabled='rspDOM'
								/>
							</ValidationProvider>
						</v-col>
						<v-col cols='12' md='3'>
							<v-tooltip top>
								<template #activator='{attrs, on}'>
									<v-checkbox
										v-model='rspDOM'
										:label='$t("iqrfnet.networkManager.dpaParams.dpaHops.optimizeDom")'
										v-bind='attrs'
										dense
										v-on='on'
										@click='rspChange'
									/>
								</template>
								{{ $t('iqrfnet.networkManager.dpaParams.dpaHops.notes.dom') }}
							</v-tooltip>
						</v-col>
					</v-row>
					<v-btn
						class='mr-1'
						color='primary'
						@click='getHops'
					>
						{{ $t('forms.get') }}
					</v-btn>
					<v-btn
						color='primary'
						:disabled='invalid'
						@click='setHops'
					>
						{{ $t('forms.set') }}
					</v-btn>
				</v-form>
			</ValidationObserver>
		</v-card-text>
	</v-card>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';

import {between, integer, required} from 'vee-validate/dist/rules';
import {DpaParamAction} from '@/enums/IqrfNet/DpaParams';
import DaemonMessageOptions from '@/ws/DaemonMessageOptions';

import IqmeshNetworkService from '@/services/DaemonApi/IqmeshNetworkService';

import {MutationPayload} from 'vuex';

/**
 * DPA params DPA hops component
 */
@Component({
	components: {
		ValidationObserver,
		ValidationProvider,
	},
})
export default class DpaHops extends Vue {
	/**
	 * @var {string} msgId Daemon API msg ID
	 */
	private msgId = '';

	/**
	 * @var {number} requestHops Request hops
	 */
	private requestHops = 239;

	/**
	 * @var {boolean} rqDOM Discovered optimized mesh
	 */
	private rqDOM = false;

	/**
	 * @var {boolean} rqDRM Discovered reduced mesh
	 */
	private rqDRM = false;

	/**
	 * @var {number} responseHops Response hops
	 */
	private responseHops = 255;

	/**
	 * @var {boolean} rspDOM Discovered optimized mesh
	 */
	private rspDOM = true;

	/**
	 * Websocket mutation handler
	 */
	private unsubscribe: CallableFunction = () => {return;};

	/**
	 * Initializes mutation handling and validation rules
	 */
	created(): void {
		extend('between', between);
		extend('integer', integer);
		extend('required', required);
		this.unsubscribe = this.$store.subscribe((mutation: MutationPayload) => {
			if (mutation.type !== 'daemonClient/SOCKET_ONMESSAGE') {
				return;
			}
			if (mutation.payload.data.msgId !== this.msgId) {
				return;
			}
			this.$store.dispatch('daemonClient/removeMessage', this.msgId);
			this.$store.dispatch('spinner/hide');
			if (mutation.payload.mType === 'iqmeshNetwork_DpaHops') {
				this.handleHops(mutation.payload.data);
			} else {
				this.$toast.error(
					this.$t('iqrfnet.messages.genericError').toString()
				);
			}
		});
	}

	/**
	 * Unregister mutation handling
	 */
	beforeDestroy(): void {
		this.$store.dispatch('daemonClient/removeMessage', this.msgId);
		this.unsubscribe();
	}

	/**
	 * Updates form for optimized request DOM
	 */
	private rqDOMChange(): void {
		if (this.rqDOM) {
			this.rqDRM = false;
			this.requestHops = 255;
		} else {
			this.requestHops = 1;
		}
	}

	/**
	 * Updates form for optimized request DRM
	 */
	private rqDRMChange(): void {
		if (this.rqDRM) {
			this.rqDOM = false;
			this.requestHops = 0;
		} else {
			this.requestHops = 1;
		}
	}

	/**
	 * Updates form for optimized response DOM
	 */
	private rspChange(): void {
		if (this.rspDOM) {
			this.responseHops = 255;
		} else {
			this.responseHops = 1;
		}
	}

	/**
	 * Retrieves DPA hops
	 */
	private getHops(): void {
		this.$store.dispatch('spinner/show', {
			timeout: 5000,
			text: this.$t('iqrfnet.networkManager.dpaParams.dpaHops.messages.get').toString()
		});
		const options = new DaemonMessageOptions(null, 5000, 'iqrfnet.networkManager.dpaParams.dpaHops.messages.timeout', () => this.msgId = '');
		IqmeshNetworkService.dpaHops(DpaParamAction.GET, null, null, options)
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Sets DPA hops
	 */
	private setHops(): void {
		this.$store.dispatch('spinner/show', {
			timeout: 5000,
			text: this.$t('iqrfnet.networkManager.dpaParams.dpaHops.messages.set').toString()
		});
		const options = new DaemonMessageOptions(null, 5000, 'iqrfnet.networkManager.dpaParams.dpaHops.messages.timeout', () => this.msgId = '');
		IqmeshNetworkService.dpaHops(DpaParamAction.SET, this.requestHops, this.responseHops, options)
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Handles DpaHops response
	 * @param response Response
	 */
	private handleHops(response): void {
		if (response.status === 0) {
			const action = response.rsp.action;
			if (action === DpaParamAction.GET) {
				const rqHops = response.rsp.requestHops;
				const rspHops = response.rsp.responseHops;
				if (rqHops === 255) {
					this.rqDOM = true;
					this.rqDRM = false;
				} else if (rqHops === 0) {
					this.rqDOM = false;
					this.rqDRM = true;
				} else {
					this.rqDOM = this.rqDRM = false;
				}
				this.rspDOM = (rspHops === 255);
				this.requestHops = rqHops;
				this.responseHops = rspHops;
			}
			this.$toast.success(
				this.$t(
					'iqrfnet.networkManager.dpaParams.dpaHops.messages.' + (action === DpaParamAction.GET ? 'get' : 'set') + 'Success'
				).toString()
			);
			return;
		}
		if (response.rsp.action !== undefined) {
			const action = response.rsp.action;
			this.$toast.error(
				this.$t(
					'iqrfnet.networkManager.dpaParams.dpaHops.messages.' + (action === DpaParamAction.GET ? 'get' : 'set') + 'Failed',
					{error: response.statusStr},
				).toString()
			);
			return;
		}
		this.$toast.error(
			this.$t(
				'iqrfnet.networkManager.dpaParams.dpaHops.messages.genericError',
				{error: response.statusStr},
			).toString()
		);
	}
}
</script>
