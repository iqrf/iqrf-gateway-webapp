<!--
Copyright 2017-2021 IQRF Tech s.r.o.
Copyright 2019-2021 MICRORISC s.r.o.

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
	<v-card>
		<v-card-title>
			{{ $t('service.systemd-journald.config.title') }}
		</v-card-title>
		<v-card-text>
			<CElementCover
				v-if='failed'
				style='z-index: 1;'
				:opacity='0.85'
			>
				{{ $t('service.systemd-journald.config.messages.fetchFailed') }}
			</CElementCover>
			<ValidationObserver
				v-if='config !== null'
				v-slot='{invalid}'
			>
				<form @submit.prevent='saveConfig'>
					<v-checkbox
						v-model='config.forwardToSyslog'
						:label='$t("service.systemd-journald.config.form.forwardToSyslog")'
					/>
					<v-select
						v-model='config.persistence'
						:label='$t("service.systemd-journald.config.form.storage")'
						:items='storageOptions'
					/>
					<div class='form-group'>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='required|integer|min:0'
							:custom-messages='{
								required: $t("service.systemd-journald.config.errors.maxDisk"),
								integer: $t("service.systemd-journald.config.errors.maxDiskInvalid"),
								min: $t("service.systemd-journald.config.errors.maxDiskInvalid"),
							}'
						>
							<v-text-field
								v-model.number='config.maxDiskSize'
								type='number'
								min='0'
								:label='$t("service.systemd-journald.config.form.maxDisk")'
								:success='touched ? valid : null'
								:error-messages='errors'
							/>
						</ValidationProvider>
						<em>{{ $t('service.systemd-journald.config.form.defaultNote') }}</em>
					</div>
					<div class='form-group'>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='required|integer|min:1'
							:custom-messages='{
								required: $t("service.systemd-journald.config.errors.maxFiles"),
								integer: $t("service.systemd-journald.config.errors.maxFilesInvalid"),
								min: $t("service.systemd-journald.config.errors.maxFilesInvalid"),
							}'
						>
							<v-text-field
								v-model.number='config.maxFiles'
								type='number'
								min='1'
								:label='$t("service.systemd-journald.config.form.maxFiles")'
								:success='touched ? valid : null'
								:error-messages='errors'
							/>
						</ValidationProvider>
						<em>{{ $t('service.systemd-journald.config.form.maxFilesNote') }}</em>
					</div>
					<div class='form-group'>
						<label>
							<strong>{{ $t('service.systemd-journald.config.form.sizeRotation') }}</strong>
						</label><br>
						<v-switch
							v-model='sizeRotation'
							color='primary'
							inset
						/>
					</div>
					<div
						v-if='sizeRotation'
						class='form-group'
					>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='required|integer|min:0'
							:custom-messages='{
								required: $t("service.systemd-journald.config.errors.maxFileSize"),
								integer: $t("service.systemd-journald.config.errors.maxFileSizeInvalid"),
								min: $t("service.systemd-journald.config.errors.maxFileSizeInvalid"),
							}'
						>
							<v-text-field
								v-model.number='config.sizeRotation.maxFileSize'
								type='number'
								min='0'
								:label='$t("service.systemd-journald.config.form.maxFileSize")'
								:success='touched ? valid : null'
								:error-messages='errors'
							/>
						</ValidationProvider>
						<em>{{ $t('service.systemd-journald.config.form.defaultNote') }}</em>
					</div>
					<div class='form-group'>
						<label>
							<strong>{{ $t('service.systemd-journald.config.form.timeRotation') }}</strong>
						</label><br>
						<v-switch
							v-model='timeRotation'
							color='primary'
							inset
						/>
					</div>
					<div
						v-if='timeRotation'
						class='form-group'
					>
						<v-select
							v-model='config.timeRotation.unit'
							:label='$t("service.systemd-journald.config.form.unit")'
							:items='unitOptions'
						/>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='required|integer|min:1'
							:custom-messages='{
								required: $t("service.systemd-journald.config.errors.count"),
								integer: $t("service.systemd-journald.config.errors.countInvalid"),
								min: $t("service.systemd-journald.config.errors.countInvalid"),
							}'
						>
							<v-text-field
								v-model.number='config.timeRotation.count'
								type='number'
								min='1'
								:label='$t("service.systemd-journald.config.form.count")'
								:success='touched ? valid : null'
								:error-messages='errors'
							/>
						</ValidationProvider>
					</div>
					<v-btn
						color='primary'
						type='submit'
						:disabled='invalid'
					>
						{{ $t('forms.save') }}
					</v-btn>
				</form>
			</ValidationObserver>
		</v-card-text>
	</v-card>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {CElementCover} from '@coreui/vue/src';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';

import {StorageMethod, TimeUnit} from '@/enums/Gateway/SystemdJournal';

import {extendedErrorToast} from '@/helpers/errorToast';
import {integer, min_value, required} from 'vee-validate/dist/rules';
import GatewayService from '@/services/GatewayService';

import {AxiosError, AxiosResponse} from 'axios';
import {IOption} from '@/interfaces/coreui';
import {ISystemdJournal} from '@/interfaces/systemdJournal';

@Component({
	components: {
		CElementCover,
		ValidationObserver,
		ValidationProvider,
	},
})

/**
 * Gateway systemd journal configuration component
 */
export default class SystemdJournaldConfig extends Vue {

	/**
	 * @var {ISystemdJournal|null} config Systemd journal configuration
	 */
	private config: ISystemdJournal|null = null;

	/**
	 * @var {boolean} sizeRotation Use size based log rotation
	 */
	private sizeRotation = false;

	/**
	 * @var {boolean} timeRotation Use time based log rotation
	 */
	private timeRotation = false;

	/**
	 * @constant {Array<IOption>} storageOptions Array of CoreUI select storage method options
	 */
	private storageOptions: Array<IOption> = [
		{
			text: this.$t('service.systemd-journald.config.form.storageMethods.volatile').toString(),
			value: StorageMethod.VOLATILE,
		},
		{
			text: this.$t('service.systemd-journald.config.form.storageMethods.persistent').toString(),
			value: StorageMethod.PERSISTENT,
		},
	];

	/**
	 * @constant {Array<IOption>} unitOptions Array of CoreUI select unit options
	 */
	private unitOptions: Array<IOption> = [
		{
			text: this.$t('service.systemd-journald.config.form.units.seconds').toString(),
			value: TimeUnit.SECONDS,
		},
		{
			text: this.$t('service.systemd-journald.config.form.units.minutes').toString(),
			value: TimeUnit.MINUTES,
		},
		{
			text: this.$t('service.systemd-journald.config.form.units.hours').toString(),
			value: TimeUnit.HOURS,
		},
		{
			text: this.$t('service.systemd-journald.config.form.units.days').toString(),
			value: TimeUnit.DAYS,
		},
		{
			text: this.$t('service.systemd-journald.config.form.units.weeks').toString(),
			value: TimeUnit.WEEKS,
		},
		{
			text: this.$t('service.systemd-journald.config.form.units.months').toString(),
			value: TimeUnit.MONTHS,
		},
		{
			text: this.$t('service.systemd-journald.config.form.units.years').toString(),
			value: TimeUnit.YEAR,
		},
	];

	/**
	 * @var {boolean} failed Indicates whether configuration fetch failed
	 */
	private failed = false;

	/**
	 * Initializes validation rules
	 */
	created(): void {
		extend('integer', integer);
		extend('min', min_value);
		extend('required', required);
	}

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
		return GatewayService.getSystemdJournalConfig()
			.then((response: AxiosResponse) => {
				const config: ISystemdJournal = response.data;
				if (config.sizeRotation.maxFileSize !== 0) {
					this.sizeRotation = true;
				}
				if (config.timeRotation.unit !== TimeUnit.MONTHS ||
					config.timeRotation.count !== 1) {
					this.timeRotation = true;
				}
				this.config = config;
			})
			.catch((error: AxiosError) => {
				this.failed = true;
				extendedErrorToast(error, 'service.systemd-journald.config.messages.fetchFailedError');
			});
	}

	/**
	 * Saves systemd journal configuration
	 */
	private saveConfig(): void {
		if (this.config === null) {
			return;
		}
		const config: ISystemdJournal = JSON.parse(JSON.stringify(this.config));
		if (!this.sizeRotation) {
			config.sizeRotation.maxFileSize = 0;
		}
		if (!this.timeRotation) {
			config.timeRotation.unit = TimeUnit.MONTHS;
			config.timeRotation.count = 1;
		}
		this.$store.commit('spinner/SHOW');
		GatewayService.saveSystemdJournalConfig(config)
			.then(() => {
				this.$store.commit('spinner/HIDE');
				this.$toast.success(
					this.$t('service.systemd-journald.config.messages.saveSuccess').toString()
				);
			})
			.catch((error: AxiosError) => extendedErrorToast(error, 'service.systemd-journald.config.messages.saveFailed'));
	}
}
</script>
