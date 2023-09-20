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
	<v-dialog
		v-model='show'
		width='50%'
		persistent
		no-click-animation
	>
		<template #activator='{on, attrs}'>
			<v-btn
				class='mr-1'
				color='info'
				small
				v-bind='attrs'
				v-on='on'
				@click='openModal'
			>
				<v-icon small>
					mdi-import
				</v-icon>
				{{ $t('forms.import') }}
			</v-btn>
		</template>
		<ValidationObserver v-slot='{invalid}'>
			<v-card>
				<v-card-title>
					{{ $t('config.daemon.scheduler.import.title') }}
				</v-card-title>
				<v-card-text>
					<v-form>
						<ValidationProvider
							v-slot='{errors, valid}'
							rules='required|taskFile'
							:custom-messages='{
								required: $t("config.daemon.scheduler.import.errors.file"),
								taskFile: $t("config.daemon.scheduler.import.errors.invalidFile"),
							}'
						>
							<v-file-input
								v-model='file'
								accept='application/json,.zip'
								:label='$t("config.daemon.scheduler.import.file")'
								:error-messages='errors'
								:success='valid'
								:prepend-icon='null'
								prepend-inner-icon='mdi-file-outline'
								required
							/>
						</ValidationProvider>
					</v-form>
				</v-card-text>
				<v-card-actions>
					<v-spacer />
					<v-btn
						@click='closeModal'
					>
						{{ $t('forms.cancel') }}
					</v-btn>
					<v-btn
						color='primary'
						:disabled='invalid'
						@click='importScheduler'
					>
						{{ $t('forms.import') }}
					</v-btn>
				</v-card-actions>
			</v-card>
		</ValidationObserver>
	</v-dialog>
</template>

<script lang='ts'>
import {Component} from 'vue-property-decorator';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';

import {daemonErrorToast, extendedErrorToast} from '@/helpers/errorToast';
import {required} from 'vee-validate/dist/rules';

import SchedulerService from '@/services/SchedulerService';

import {AxiosError} from 'axios';
import ModalBase from '@/components/ModalBase.vue';
import {useApiClient} from '@/services/ApiClient';

@Component({
	components: {
		ValidationObserver,
		ValidationProvider,
	},
})

/**
 * Scheduler task import Modal component
 */
export default class TaskImportModal extends ModalBase {

	/**
	 * @var {File|null} file Task file to import
	 */
	private file: File|null = null;

	created(): void {
		extend('required', required);
		extend('taskFile', (file: File|null) => {
			if (!file) {
				return false;
			}
			if (!['application/json', 'application/zip'].includes(file.type)) {
				return false;
			}
			return true;
		});
	}

	/**
	 * Imports scheduler tasks from zip file
	 */
	private importScheduler(): void {
		if (!this.file) {
			return;
		}
		this.$store.commit('spinner/SHOW');
		SchedulerService.importConfig(this.file)
			.then(() => {
				this.$toast.success(
					this.$t('config.daemon.scheduler.messages.importSuccess').toString()
				);
				this.closeModal();
				useApiClient().getServiceService().restart('iqrf-gateway-daemon')
					.then(() => {
						this.$store.commit('spinner/HIDE');
						this.$toast.info(
							this.$t('service.iqrf-gateway-daemon.messages.restart')
								.toString()
						);
						this.$emit('imported');
					})
					.catch((error: AxiosError) => daemonErrorToast(error, 'service.messages.restartFailed'));
			})
			.catch((error: AxiosError) => extendedErrorToast(error, 'config.daemon.scheduler.messages.importFailed'));
	}
}
</script>
