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
	<v-card-text>
		<v-overlay
			v-if='running'
			:opacity='0.65'
			absolute
		>
			<v-progress-circular color='primary' indeterminate />
		</v-overlay>
		<ValidationObserver v-slot='{invalid}'>
			<v-form>
				<ValidationProvider
					v-slot='{errors, valid}'
					rules='required'
					:custom-messages='{
						required: $t("maintenance.backup.errors.restoreArchive"),
					}'
				>
					<ExtendedFileInput
						v-model='archive'
						accept='.zip'
						:label='$t("maintenance.backup.form.archive")'
						:success='valid'
						:errors='errors'
						:prepend-icon='null'
						prepend-inner-icon='mdi-archive-arrow-up'
					/>
				</ValidationProvider>
				<v-btn
					color='primary'
					:disabled='invalid'
					@click='restore'
				>
					{{ $t('maintenance.backup.form.restore') }}
				</v-btn>
			</v-form>
		</ValidationObserver>
	</v-card-text>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import ExtendedFileInput from '@/components/ExtendedFileInput.vue';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';

import {extendedErrorToast} from '@/helpers/errorToast';
import {required} from 'vee-validate/dist/rules';

import BackupService from '@/services/BackupService';

import {AxiosError, AxiosResponse} from 'axios';

@Component({
	components: {
		ExtendedFileInput,
		ValidationObserver,
		ValidationProvider,
	},
})

/**
 * Gateway restore component
 */
export default class GatewayRestore extends Vue {

	/**
	 * @var {File|null} archive Archive to restore
	 */
	private archive: File|null = null;

	/**
	 * @var {boolean} running Indicates whether restore operation is running
	 */
	private running = false;

	/**
	 * Initializes validation rules
	 */
	created(): void {
		extend('required', required);
	}

	/**
	 * Performs gateway restore
	 */
	private restore(): void {
		if (this.archive === null) {
			return;
		}
		this.showBlockingElement();
		BackupService.restore(this.archive)
			.then((response: AxiosResponse) => {
				const time = new Date(response.data.timestamp * 1000).toLocaleTimeString();
				this.hideBlockingElement();
				this.$toast.success(
					this.$t('maintenance.backup.messages.restoreSuccess', {time: time}).toString()
				);
				if (this.$route.path.includes('/install/restore')) {
					this.$router.push('/sign/in');
				}
			})
			.catch((error: AxiosError) => {
				this.hideBlockingElement();
				extendedErrorToast(error, 'maintenance.backup.messages.restoreFailed');
			});
	}

	/**
	 * Shows interface blocking element depending on the location
	 */
	private showBlockingElement(): void {
		if (this.$route.path.includes('/install/restore')) {
			this.running = true;
		} else {
			this.$store.commit('spinner/SHOW');
			this.$store.commit('spinner/UPDATE_TEXT', this.$t('maintenance.backup.messages.restore').toString());
		}
	}

	/**
	 * Hides interface blocking element depending on the location
	 */
	private hideBlockingElement(): void {
		if (this.$route.path.includes('/install/restore')) {
			this.running = false;
		} else {
			this.$store.commit('spinner/HIDE');
		}
	}

}
</script>
