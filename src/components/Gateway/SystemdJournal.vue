<template>
	<CCard>
		<CCardHeader>
			{{ $t('gateway.log.systemdJournal.title') }}
			<CButton
				style='float: right;'
				color='primary'
				@click='restartJournalService'
			>
				{{ $t('gateway.log.systemdJournal.restart') }}
			</CButton>
		</CCardHeader>
		<CCardBody>
			<CElementCover 
				v-if='loading'
				style='z-index: 1;'
				:opacity='0.85'
			>
				<CSpinner size='5xl' />
			</CElementCover>
			<CElementCover 
				v-if='failed'
				style='z-index: 1;'
				:opacity='0.85'
			>
				{{ $t('gateway.log.systemdJournal.messages.journalFetchFailure') }}
			</CElementCover>
			<table v-if='config !== null' class='table table-striped'>
				<tbody>
					<tr>
						<th>{{ $t('gateway.log.systemdJournal.persistence') }}</th>
						<td>
							<CDropdown
								:color='config.persistent ? "success" : "danger"'
								:toggler-text='$t("states." + (config.persistent ? "enabled" : "disabled"))'
								placement='top-start'
								size='sm'
								style='float: right;'
							>
								<CDropdownItem @click='enablePersistence'>
									{{ $t('states.enabled') }}
								</CDropdownItem>
								<CDropdownItem @click='disablePersistence'>
									{{ $t('states.disabled') }}
								</CDropdownItem>
							</CDropdown>
						</td>
					</tr>
				</tbody>
			</table>
		</CCardBody>	
	</CCard>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CCard, CCardBody, CCardHeader, CDropdown, CDropdownItem, CElementCover, CSpinner} from '@coreui/vue/src';

import GatewayService from '../../services/GatewayService';
import ServiceService from '../../services/ServiceService';

import {AxiosError, AxiosResponse} from 'axios';
import {ISystemdJournal} from '../../interfaces/systemdJournal';

@Component({
	components: {
		CCard,
		CCardBody,
		CCardHeader,
		CDropdown,
		CDropdownItem,
		CElementCover,
		CSpinner,
	},
})

/**
 * Gateway systemd journal configuration component
 */
export default class SystemdJournal extends Vue {

	/**
	 * @var {ISystemdJournal|null} config Systemd journal configuration
	 */
	private config: ISystemdJournal|null = null

	/**
	 * @var {boolean} loading Indicates whether configuration fetch is in progress
	 */
	private loading = true;

	/**
	 * @var {boolean} failed Indicates whether configuraiton fetch failed
	 */
	private failed = false;

	/**
	 * Retrieves systemd journal configuration
	 */
	mounted(): void {
		this.getConfig();
	}

	/**
	 * Retrieves systemd journal configuration
	 */
	private getConfig(): Promise<void> {
		return GatewayService.systemdLog()
			.then((response: AxiosResponse) => {
				this.config = response.data;
				this.loading = false;
			})
			.catch((error: AxiosError) => {
				this.loading = false;
				this.failed = true;
				this.$toast.error(
					this.$t(
						'gateway.log.systemdJournal.messages.journalFetchFailureError',
						{error: error.response !== undefined ? error.response.data.message : error.message}
					).toString()
				);
			});
	}

	/**
	 * Restarts systemd journal service
	 */
	private restartJournalService(): Promise<void> {
		return ServiceService.restart('systemd-journald')
			.then(() => {
				this.loading = false;
				this.$toast.success(
					this.$t('gateway.log.systemdJournal.messages.restartSuccess').toString()
				);
			})
			.catch((error: AxiosError) => {
				this.loading = false;
				this.$toast.error(
					this.$t(
						'gateway.log.systemdJournal.messages.restartFailed',
						{error: error.response !== undefined ? error.response.data.message : error.message}
					).toString()
				);
			});
	}

	/**
	 * Disables systemd journal persistence
	 */
	private disablePersistence(): void {
		if (this.config === null || !this.config.persistent) {
			return;
		}
		this.loading = true;
		GatewayService.disablePersistence()
			.then(() => this.persistenceSuccess(false))
			.catch((error: AxiosError) => this.persistenceError(error, false));
	}

	/**
	 * Enables systemd journal persistence
	 */
	private enablePersistence(): void {
		if (this.config === null || this.config.persistent) {
			return;
		}
		this.loading = true;
		GatewayService.enablePersistence()
			.then(() => this.persistenceSuccess(true))
			.catch((error: AxiosError) => this.persistenceError(error, true));
	}

	/**
	 * Handles persistence change success
	 * @param {boolean} enabled Persistence enabled?
	 */
	private persistenceSuccess(enabled: boolean): void {
		this.getConfig().then(() => 
			this.$toast.success(
				this.$t('gateway.log.systemdJournal.messages.' + (enabled ? 'enable' : 'disable') + 'PersistenceSuccess').toString()
			)
		);
	}

	/**
	 * Handles persistence change failure
	 * @param {AxiosError} error Axios error
	 * @param {boolean} enabled Persistence enabled?
	 */
	private persistenceError(error: AxiosError, enabled: boolean): void {
		this.loading = false;
		this.$toast.error(
			this.$t(
				'gateway.log.systemdJournal.messages.' + (enabled ? 'enable' : 'disable') + 'PersistenceFailure',
				{error: error.response !== undefined ? error.response.data.message : error.message}
			).toString()
		);
	}

}
</script>
