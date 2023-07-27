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

import SshService from '@/services/SshService';

import {AxiosResponse} from 'axios';

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
		SshService.listKeyTypes()
			.then((response: AxiosResponse) => {
				this.types = response.data;
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
		const sections = key.trim().split(' ');
		if (sections.length < 2) {
			return false;
		}
		return this.types.includes(sections[0]);
	}
}
</script>
