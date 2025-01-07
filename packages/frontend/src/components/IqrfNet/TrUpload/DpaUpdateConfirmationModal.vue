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
			<v-card-title>
				{{ $t('iqrfnet.trUpload.dpaUpload.modal.title') }}
			</v-card-title>
			<v-card-text>
				{{ $t('iqrfnet.trUpload.dpaUpload.modal.prompt', {version: currentDpa}) }}
			</v-card-text>
			<v-card-actions>
				<v-spacer />
				<v-btn
					@click='hideModal'
				>
					{{ $t('forms.cancel') }}
				</v-btn>
				<v-btn
					color='warning'
					@click='upload'
				>
					{{ $t('forms.upload') }}
				</v-btn>
			</v-card-actions>
		</v-card>
	</v-dialog>
</template>

<script lang='ts'>
import {Component, Prop, VModel, Vue} from 'vue-property-decorator';

/**
 * DPA update confirmation modal
 */
@Component
export default class DpaUpdateConfirmationModal extends Vue {

	/**
	 * @var {boolean} show Modal display control
	 */
	@VModel({required: false, default: false}) show!: boolean;

	/**
	 * @property {string} currentDpa Current DPA version
	 */
	@Prop({required: false, default: ''}) currentDpa!: string;

	/**
	 * Emits event to start upload
	 */
	private upload(): void {
		this.hideModal();
		this.$emit('upload');
	}

	/**
	 * Hides modal window
	 */
	private hideModal(): void {
		this.show = false;
	}
}
</script>
