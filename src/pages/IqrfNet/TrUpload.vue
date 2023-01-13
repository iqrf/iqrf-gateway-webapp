<!--
Copyright 2017-2023 IQRF Tech s.r.o.
Copyright 2019-2023 MICRORISC s.r.o.

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
		<h1>{{ $t('iqrfnet.trUpload.title') }}</h1>
		<v-alert
			v-if='loaded && failed'
			color='error'
			text
			outlined
		>
			<v-row align='center'>
				<v-col class='grow'>
					{{ $t('iqrfnet.trUpload.messages.coordinatorFailed') }} {{ $t('iqrfnet.trUpload.messages.notAvailable') }}
				</v-col>
				<v-col class='shrink'>
					<v-btn
						color='primary'
						small
						@click='enumerateCoordinator'
					>
						<v-icon small>
							mdi-refresh
						</v-icon>
						{{ $t('forms.retry') }}
					</v-btn>
				</v-col>
			</v-row>
		</v-alert>
		<div v-show='loaded && !failed'>
			<HexUpload class='mb-5' />
			<DpaUpdater ref='dpaUpdater' />
			<!--<OsUpdater
				ref='osUpdater'
				class='mt-5'
				@os-upload='osInfoUpload'
			/>-->
		</div>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import DpaUpdater from '@/components/IqrfNet/TrUpload/DpaUpdater.vue';
import HexUpload from '@/components/IqrfNet/TrUpload/HexUpload.vue';
import OsUpdater from '@/components/IqrfNet/TrUpload/OsUpdater.vue';

import IqrfNetService from '@/services/IqrfNetService';

import {DaemonClientState} from '@/interfaces/wsClient';
import {MutationPayload} from 'vuex';
import {NavigationGuardNext, Route} from 'vue-router';

@Component({
	components: {
		DpaUpdater,
		HexUpload,
		OsUpdater,
	},
	beforeRouteEnter(to: Route, from: Route, next: NavigationGuardNext): void {
		next((vm: Vue) => {
			if (!vm.$store.getters['features/isEnabled']('trUpload')) {
				vm.$toast.error(
					vm.$t('iqrfnet.trUpload.messages.disabled').toString()
				);
				vm.$router.push(from.path);
			}
		});
	},
	metaInfo: {
		title: 'iqrfnet.trUpload.title'
	}
})

/**
 * Coordinator upload page component
 */
export default class TrUpload extends Vue {
	/**
	 * @constant {number} address IQRF Network address of coordinator
	 */
	private address = 0;

	/**
	 * @var {string} msgId Daemon api message id
	 */
	private msgId = '';

	/**
	 * @var {bool} loaded Finished loading information
	 */
	private loaded = false;

	/**
	 * @var {bool} failed Information loading failed
	 */
	private failed = false;

	/**
	 * Component unsubscribe function
	 */
	private unsubscribe: CallableFunction = () => {return;};

	/**
	 * Component unwatch function
	 */
	private unwatch: CallableFunction = () => {return;};

	/**
	 * Initializes validation rules and websocket callbacks
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
			if (mutation.payload.mType === 'iqmeshNetwork_EnumerateDevice') {
				this.handleEnumResponse(mutation.payload);
			} else if (mutation.payload.mType === 'messageError') {
				this.$store.commit('spinner/HIDE');
				this.$toast.error(
					this.$t('messageError', {error: mutation.payload.data.rsp.errorStr}).toString()
				);
				this.loaded = this.failed = true;
			}
		});

		if (this.$store.getters['daemonClient/isConnected']) {
			this.enumerateCoordinator();
		} else {
			this.unwatch = this.$store.watch(
				(state: DaemonClientState, getter: any) => getter['daemonClient/isConnected'],
				(newVal: boolean, oldVal: boolean) => {
					if (!oldVal && newVal) {
						this.enumerateCoordinator();
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
	 * Sends a Daemon API request to retrieve OS information
	 */
	private enumerateCoordinator(): void {
		this.loaded = this.failed = false;
		this.$store.commit('spinner/SHOW');
		IqrfNetService.enumerateDevice(this.address, 60000, 'iqrfnet.trUpload.messages.coordinatorFailed', () => this.msgId = '')
			.then((msgId: string) => this.msgId = msgId);
	}

	/**
	 * Handles Daemon API OS response
	 * @param response Daemon API response
	 */
	private handleEnumResponse(response): void {
		this.$store.commit('spinner/HIDE');
		if (response.data.status === 0) {
			(this.$refs.dpaUpdater as DpaUpdater).handleEnumResponse(response.data.rsp);
			//(this.$refs.osUpdater as OsUpdater).handleEnumResponse(response.data.rsp);
		} else {
			this.failed = true;
			this.$toast.error(
				this.$t('iqrfnet.trUpload.messages.coordinatorFailed').toString()
			);
		}
		this.loaded = true;
	}

	/**
	 * Reloads OsInfo after upload
	 */
	private osInfoUpload(): void {
		this.$store.commit('spinner/SHOW');
		this.$store.commit(
			'spinner/UPDATE_TEXT',
			this.$t('iqrfnet.trUpload.messages.postUpload').toString()
		);
		this.unwatch = this.$store.watch(
			(state: DaemonClientState, getter: any) => getter['daemonClient/isConnected'],
			(newVal: boolean, oldVal: boolean) => {
				if (!oldVal && newVal) {
					setTimeout(() => this.enumerateCoordinator(), 5000);
					this.unwatch();
				}
			}
		);
	}
}
</script>
