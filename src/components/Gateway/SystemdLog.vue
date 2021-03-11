 <template>
	<CCard>
		<CCardHeader>
			{{ $t('gateway.log.systemd.title') }}
		</CCardHeader>
		<CCardBody>
			<table v-if='config !== null' class='table table-striped'>
				<tbody>
					<tr>
						<th>{{ $t('gateway.log.systemd.persistence') }}</th>
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
import {CCard, CCardBody, CCardHeader, CDropdown, CDropdownItem} from '@coreui/vue/src';

import GatewayService from '../../services/GatewayService';

import {AxiosError, AxiosResponse} from 'axios';
import {ISystemdLog} from '../../interfaces/systemdLog';

@Component({
	components: {
		CCard,
		CCardBody,
		CCardHeader,
		CDropdown,
		CDropdownItem,
	},
})

/**
 * Gateway system log configuration component
 */
export default class SystemdLog extends Vue {

	/**
	 * @var {ISystemdLog|null} config Systemd log configuration
	 */
	private config: ISystemdLog|null = null

	/**
	 * Retrieves systemd log configuration
	 */
	mounted(): void {
		this.getConfig();
	}

	/**
	 * Retrieves systemd log configuration
	 */
	private getConfig(): Promise<void> {
		return GatewayService.systemdLog()
			.then((response: AxiosResponse) => {
				this.config = response.data;
			})
			.catch((error: AxiosError) => {
				this.$toast.error(
					this.$t(
						'gateway.log.messages.journalFetchFailure',
						{error: error.response !== undefined ? error.response.data.message : error.message}
					).toString()
				);
			});
	}

	/**
	 * Disables systemd log persistence
	 */
	private disablePersistence(): void {
		if (this.config === null || !this.config.persistent) {
			return;
		}
		GatewayService.disablePersistence()
			.then(() => this.persistenceSuccess(false))
			.catch((error: AxiosError) => this.persistenceError(error, false));
	}

	/**
	 * Enables systemd log persistence
	 */
	private enablePersistence(): void {
		if (this.config === null || this.config.persistent) {
			return;
		}
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
				this.$t('gateway.log.messages.' + (enabled ? 'enable' : 'disable') + 'PersistenceSuccess').toString()
			)
		);
	}

	/**
	 * Handles persistence change failure
	 * @param {AxiosError} error Axios error
	 * @param {boolean} enabled Persistence enabled?
	 */
	private persistenceError(error: AxiosError, enabled: boolean): void {
		this.$toast.error(
			this.$t(
				'gateway.log.messages.' + (enabled ? 'enable' : 'disable') + 'PersistenceFailure',
				{error: error.response !== undefined ? error.response.data.message : error.message}
			).toString()
		);
	}

}
</script>
