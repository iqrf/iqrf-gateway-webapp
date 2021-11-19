<template>
	<CCard>
		<CElementCover
			v-if='!clientConnected'
			style='z-index: 1;'
			:opacity='0.85'
		>
			{{ $t('maintenance.mender.update.messages.clientReconnecting') }}
		</CElementCover>
		<CElementCover
			v-if='clientConnected && actionInProgress'
			style='z-index: 1;'
			:opacity='0.85'
		>
			<span v-if='menderAction === "install"'>
				{{ $t('maintenance.mender.update.messages.installProgress') }}
			</span>
			<span v-else-if='menderAction === "commit"'>
				{{ $t('maintenance.mender.update.messages.commitProgress') }}
			</span>
			<span v-else>
				{{ $t('maintenance.mender.update.messages.rollbackProgress') }}
			</span>
		</CElementCover>
		<CCard class='border-top-0 border-left-0 border-right-0 card-margin-bottom'>
			<CCardBody>
				<h4>{{ $t('maintenance.mender.update.update') }}</h4>
				<CForm>
					<CInputFile
						ref='artifactInput'
						:label='$t("maintenance.mender.update.form.artifact")'
						accept='.mender'
						@input='isInputEmpty'
						@click='isInputEmpty'
					/>
				</CForm>
				<CButton
					color='primary'
					:disabled='inputEmpty'
					@click='install'
				>
					{{ $t('maintenance.mender.update.form.install') }}
				</CButton> <CButton
					color='success'
					@click='commit'
				>
					{{ $t('maintenance.mender.update.form.commit') }}
				</CButton> <CButton
					color='danger'
					@click='rollback'
				>
					{{ $t('maintenance.mender.update.form.rollback') }}
				</CButton>
			</CCardBody>
		</CCard>
		<CCard class='border-0 card-margin-bottom'>
			<CCardBody>
				<h4>{{ $t('maintenance.mender.update.control') }}</h4>
				<CButton
					color='primary'
					@click='reboot()'
				>
					{{ $t('gateway.power.reboot') }}
				</CButton> <CButton
					v-if='$store.getters["features/isEnabled"]("remount")'
					color='primary'
					@click='remount(false)'
				>
					{{ $t('maintenance.mender.update.remountRo') }}
				</CButton> <CButton
					v-if='$store.getters["features/isEnabled"]("remount")'
					color='primary'
					@click='remount(true)'
				>
					{{ $t('maintenance.mender.update.remountRw') }}
				</CButton>
			</CCardBody>
		</CCard>
	</CCard>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CButton, CCard, CCardBody, CInputFile} from '@coreui/vue/src';

import {extendedErrorToast} from '../../helpers/errorToast';
import {mapGetters} from 'vuex';
import {MenderActions, MenderMessageTypes, MountModes} from '../../enums/Maintenance/Mender';

import GatewayService from '../../services/GatewayService';
import MenderService from '../../services/MenderService';

import {AxiosError, AxiosResponse} from 'axios';
import {MutationPayload} from 'vuex';
import {MenderMessage} from '../../interfaces/mender';

@Component({
	components: {
		CButton,
		CCard,
		CCardBody,
		CInputFile,
	},
	computed: {
		...mapGetters({
			clientConnected: 'menderClient/isConnected',
			menderAction: 'menderClient/action',
			actionInProgress: 'menderClient/actionInProgress',
		}),
	},
})

/**
 * Mender update control card component
 */
export default class MenderUpdateControl extends Vue {

	/**
	 * @var {boolean} inputEmpty Indicates that mender artifact file input is empty
	 */
	private inputEmpty = true

	/**
	 * Component unsubscribe function
	 */
	private unsubscribe: CallableFunction = () => {return;}

	/**
	 * Subscribes to Mender vuex mutations
	 */
	created(): void {
		this.$store.dispatch('mender_connectSocket');
		this.unsubscribe = this.$store.subscribe((mutation: MutationPayload) => {
			if (mutation.type === 'menderClient/SOCKET_ONMESSAGE') {
				this.handleMenderMessage(mutation.payload);
			}
		});
	}

	/**
	 * Unsubscribes from Mender vuex mutations
	 */
	beforeDestroy(): void {
		this.unsubscribe();
		this.$store.dispatch('mender_destroySocket');
	}

	/**
	 * Handles Mender message from Mender ws server
	 * @param {MenderMessage} message Mender message
	 */
	private handleMenderMessage(message: MenderMessage): void {
		if (message.messageType !== MenderMessageTypes.EXIT) {
			this.updateLog(message['payload']);
			return;
		}
		const action = this.$store.getters['menderClient/action'];
		if (message['payload'] !== '0') {
			if (action === MenderActions.INSTALL) {
				this.$toast.error(
					this.$t('maintenance.mender.update.messages.installFailed').toString()
				);
			} else if (action === MenderActions.COMMIT) {
				this.$toast.error(
					this.$t('maintenance.mender.update.messages.commitFailed').toString()
				);
			} else {
				this.$toast.error(
					this.$t('maintenance.mender.update.messages.rollbackFailed').toString()
				);
			}
			this.$store.commit('menderClient/ACTION_END');
			return;
		}
		if (action === MenderActions.INSTALL) {
			this.$toast.success(
				this.$t('maintenance.mender.update.messages.installSuccess').toString()
			);
		} else if (action === MenderActions.COMMIT) {
			this.$toast.success(
				this.$t('maintenance.mender.update.messages.commitSuccess').toString()
			);
		} else {
			this.$toast.success(
				this.$t('maintenance.mender.update.messages.rollbackSuccess').toString()
			);
		}
		this.$store.commit('menderClient/ACTION_END');
	}

	/**
	 * Retrieves selected file from file input
	 * @return {FileList} List of uploaded files
	 */
	private getInputFile(): FileList {
		const input = ((this.$refs.artifactInput as CInputFile).$el.children[1] as HTMLInputElement);
		return (input.files as FileList);
	}

	/**
	 * Checks if artifact file input is empty
	 */
	private isInputEmpty(): void {
		this.inputEmpty = this.getInputFile().length === 0;
	}

	/**
	 * Performs mender install artifact task
	 */
	private install(): void {
		this.$store.commit('menderClient/ACTION_START', MenderActions.INSTALL);
		const formData = new FormData();
		const file = this.getInputFile()[0];
		formData.append('file', file);
		MenderService.install(formData)
			.then(() => {
				this.$toast.info(
					this.$t('maintenance.mender.update.messages.installProgress').toString()
				);
			})
			.catch(() => {
				this.$store.commit('menderClient/ACTION_END');
				this.$toast.error(
					this.$t('maintenance.mender.update.messages.installFailed').toString()
				);
			});
	}

	/**
	 * Performs mender commit task
	 */
	private commit(): void {
		this.$store.commit('menderClient/ACTION_START', MenderActions.COMMIT);
		MenderService.commit()
			.then(() => {
				this.$toast.info(
					this.$t('maintenance.mender.update.messages.commitProgress').toString()
				);
			})
			.catch(() => {
				this.$store.commit('menderClient/ACTION_END');
				this.$toast.error(
					this.$t('maintenance.mender.update.messages.commitFailed').toString()
				);
			});
	}

	/**
	 * Performs mender rollback task
	 */
	private rollback(): void {
		this.$store.commit('menderClient/ACTION_START', MenderActions.ROLLBACK);
		MenderService.rollback()
			.then(() => {
				this.$toast.info(
					this.$t('maintenance.mender.update.messages.rollbackProgress').toString()
				);
			})
			.catch(() => {
				this.$store.commit('menderClient/ACTION_END');
				this.$toast.error(
					this.$t('maintenance.mender.update.messages.rollbackFailed').toString()
				);
			});
	}

	/**
	 * Performs reboot after commit
	 */
	private reboot(): void {
		this.$store.commit('spinner/SHOW');
		GatewayService.performReboot()
			.then((response: AxiosResponse) => {
				let time = new Date(response.data.timestamp * 1000).toLocaleTimeString();
				this.$store.commit('spinner/HIDE');
				this.$toast.success(
					this.$t(
						'gateway.power.messages.rebootSuccess',
						{time: time},
					).toString()
				);
			});
	}

	/**
	 * Remounts filesystem
	 * @param {boolean} writable Make filesystem writable
	 */
	private remount(writable: boolean): void {
		const conf = {
			mode: writable ? MountModes.RW : MountModes.RO
		};
		this.$store.commit('spinner/SHOW');
		MenderService.remount(conf)
			.then(() => {
				this.$store.commit('spinner/HIDE');
				this.$toast.success(
					this.$t('maintenance.mender.update.messages.remountSuccess').toString()
				);
			})
			.catch((error: AxiosError) => extendedErrorToast(
				error,
				'maintenance.mender.update.messages.remountFailed',
			));
	}

	/**
	 * Updates execution log in the log component
	 */
	private updateLog(log: string) {
		this.$emit('update-log', log);
	}
}
</script>
