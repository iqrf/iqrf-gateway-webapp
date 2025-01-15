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
	<v-dialog
		v-model='show'
		width='50%'
		persistent
		no-click-animation
	>
		<v-card>
			<v-card-title>{{ $t('core.security.apiKey.display.title') }}</v-card-title>
			<v-card-text>
				{{ $t('core.security.apiKey.display.prompt') }}
				<v-text-field
					class='mt-4'
					:value='apiKey'
					readonly
				>
					<template #append-outer>
						<v-btn
							color='success'
							small
							@click='copyClipboard()'
						>
							<v-icon small>
								mdi-clipboard-outline
							</v-icon>
							{{ $t('forms.clipboardCopy') }}
						</v-btn>
					</template>
				</v-text-field>
			</v-card-text>
			<v-card-actions>
				<v-spacer />
				<v-btn @click='close()'>
					{{ $t('forms.close') }}
				</v-btn>
			</v-card-actions>
		</v-card>
	</v-dialog>
</template>

<script lang='ts'>
import {Component, Prop, Vue} from 'vue-property-decorator';

@Component({})

/**
 * API key display modal
 */
export default class ApiKeyDisplayModal extends Vue {

	/**
	 * @var {boolean} show Show dialog
	 */
	private show: boolean = false;

	/**
	 * @property {string|null} apiKey Generated API key
	 */
	@Prop({required: false, default: null}) apiKey!: string|null;

	/**
	 * Copy data to clipboard and show result toast
	 */
	private async copyClipboard(): Promise<void> {
		await navigator.clipboard.writeText(this.apiKey!)
			.then(() => {
				this.$toast.success(
					this.$t('core.security.apiKey.display.copyMessage').toString(),
				);
			})
			.catch(() => {
				this.$toast.error(
					this.$t('fields.errors.clipboardCopy').toString(),
				);
			});
	}

	/**
	 * Opens modal window
	 */
	public open(): void {
		this.show = true;
	}

	/**
	 * Closes modal window
	 */
	private close(): void {
		this.show = false;
		this.$emit('closed');
	}
}
</script>
