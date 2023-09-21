<template>
	<v-alert
		v-if='fetched !== null'
		:color='fetched ? "primary" : "danger"'
		text
	>
		<span v-if='fetched'>
			<span v-if='types.length === 0'>
				{{ $t('core.security.ssh.messages.typeListNone') }}
			</span>
			<span v-else>
				{{ $t('core.security.ssh.messages.typeListSuccess') }}
				<ul>
					<li
						v-for='key of types'
						:key='key'
					>
						{{ key }}
					</li>
				</ul>
			</span>
		</span>
		<span v-else>
			{{ $t('core.security.ssh.messages.typeListFailed') }}
		</span>
	</v-alert>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';

import {useApiClient} from '@/services/ApiClient';
import {SshKeyUtils} from '@iqrf/iqrf-gateway-webapp-client/utils';

@Component({})

/**
 * SSH key types list component
 */
export default class SshKeyTypes extends Vue {

	/**
	 * @var {boolean|null} fetched Indicates that key type list request has been completed
	 */
	private fetched: boolean|null = null;

	/**
	 * @var {Array<string>} types Array of SSH key types
	 */
	private types: Array<string> = [];

	/**
	 * Retrieves key types
	 */
	created(): void {
		useApiClient().getGatewayServices().getSshKeyService().fetchKeyTypes()
			.then((response: string[]) => {
				this.types = response;
				this.fetched = true;
				this.$emit('fetch');
			})
			.catch(() => {
				this.fetched = false;
				this.$emit('fetch');
			});
	}

	/**
	 * Validates key against supported key types
	 * @param {string} key SSH public key to validate
	 * @returns {boolean} True if valid, false otherwise
	 */
	validateKey(key: string): boolean {
		try {
			SshKeyUtils.validatePublicKey(key, this.types);
			return true;
		} catch (e: Error) {
			return false;
		}
	}
}
</script>
