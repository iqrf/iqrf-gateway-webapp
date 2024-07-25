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
		useApiClient().getSecurityServices().getSshKeyService().listKeyTypes()
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
