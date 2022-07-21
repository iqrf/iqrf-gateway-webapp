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
	<ValidationObserver v-slot='{invalid}'>
		<v-dialog
			v-model='show'
			width='50%'
			persistent
			no-click-animation
		>
			<template #activator='{attrs, on}'>
				<v-btn
					v-if='operator.id === undefined'
					color='success'
					small
					tile
					elevation='0'
					v-bind='attrs'
					v-on='on'
					@click='openDialog'
				>
					<v-icon small>
						mdi-plus
					</v-icon>
				</v-btn>
				<v-list-item
					v-else
					type='button'
					v-bind='attrs'
					v-on='on'

					@click='showDialog'
				>
					<v-icon dense>
						mdi-pencil
					</v-icon>
					{{ $t('network.operators.edit') }}
				</v-list-item>
			</template>
			<v-card>
				<v-card-title>
					{{ modalTitle }}
				</v-card-title>
				<v-card-text>
					<v-form>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='required'
							:custom-messages='{
								required: $t("network.operators.errors.nameRequired"),
							}'
						>
							<v-text-field
								v-model='operator.name'
								:label='$t("network.operators.form.name")'
								:success='touched ? valid : null'
								:error-messages='errors'
							/>
						</ValidationProvider>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='required'
							:custom-messages='{
								required: $t("network.operators.errors.apnRequired"),
							}'
						>
							<v-text-field
								v-model='operator.apn'
								:label='$t("network.operators.form.apn")'
								:success='touched ? valid : null'
								:error-messages='errors'
							/>
						</ValidationProvider>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							:rules='{
								required: operator.password.length > 0
							}'
							:custom-messages='{
								required: $t("forms.errors.credentials"),
							}'
						>
							<v-text-field
								v-model='operator.username'
								:label='$t("network.operators.form.username")'
								:success='touched ? valid : null'
								:error-messages='errors'
							/>
						</ValidationProvider>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							:rules='{
								required: operator.username.length > 0
							}'
							:custom-messages='{
								required: $t("forms.errors.credentials"),
							}'
						>
							<v-text-field
								v-model='operator.password'
								:label='$t("network.operators.form.password")'
								:success='touched ? valid : null'
								:error-messages='errors'
							/>
						</ValidationProvider>
					</v-form>
				</v-card-text>
				<v-card-actions>
					<v-spacer />
					<v-btn
						@click='closeDialog'
					>
						{{ $t('forms.cancel') }}
					</v-btn> <v-btn
						:color='modalColor'
						:disabled='invalid'
					>
						{{ $t('forms.save') }}
					</v-btn>
				</v-card-actions>
			</v-card>
		</v-dialog>
	</ValidationObserver>
</template>

<script lang='ts'>
import {Component, Prop} from 'vue-property-decorator';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';
import DialogBase from '../DialogBase.vue';

import {digits, required} from 'vee-validate/dist/rules';
import {extendedErrorToast} from '@/helpers/errorToast';

import NetworkOperatorService from '@/services/NetworkOperatorService';

import {AxiosError} from 'axios';
import {IOperator} from '@/interfaces/network';


@Component({
	components: {
		ValidationObserver,
		ValidationProvider,
	},
})

/**
 * Network operator form dialog component
 */
export default class NetworkOperatorFormDialog extends DialogBase {
	@Prop({
		type: Object,
		default: () => {return {name: '', apn: '', username: '', password: ''};}
	}) operator!: IOperator;

	/**
	 * Returns modal color depending on operator id
	 */
	get modalColor(): string {
		if (!this.operator.id) {
			return 'success';
		}
		return 'primary';
	}

	/**
	 * Returns modal title depending on operator title
	 */
	get modalTitle(): string {
		if (!this.operator.id) {
			return this.$t('network.operators.form.add').toString();
		}
		return this.$t('network.operators.form.edit').toString();
	}

	/**
	 * Initializes validation rules
	 */
	created(): void {
		extend('digits', digits);
		extend('required', required);
	}


	/**
	 * Saves network operator configuration
	 */
	private saveOperator(): void {
		if (!this.operator.id) {
			this.addOperator();
		} else {
			this.editOperator();
		}
	}

	/**
	 * Saves new network operator
	 */
	private addOperator(): void {
		this.$store.commit('spinner/SHOW');
		NetworkOperatorService.addOperator(this.operator)
			.then(() => {
				this.$store.commit('spinner/HIDE');
				this.$toast.success(
					this.$t('network.operators.messages.addSuccess', {operator: this.operator.name}).toString()
				);
				this.$emit('saved');
			})
			.catch((err: AxiosError) => extendedErrorToast(err, 'network.operators.messages.addFailed'));
	}

	/**
	 * Edits existing network operator
	 */
	private editOperator(): void {
		if (this.operator.id === undefined) {
			return;
		}
		this.$store.commit('spinner/SHOW');
		NetworkOperatorService.editOperator(this.operator.id, this.operator)
			.then(() => {
				this.$store.commit('spinner/HIDE');
				this.$toast.success(
					this.$t('network.operators.messages.editSuccess', {operator: this.operator.name}).toString()
				);
				this.$emit('saved');
			})
			.catch((err: AxiosError) => extendedErrorToast(err, 'network.operators.messages.editFailed'));
	}

	/**
	 * Emits event to close parent menu (temporary workaround) and opens dialog
	 */
	private showDialog(): void {
		this.$emit('close-menu');
		this.openDialog();
	}
}
</script>
