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
	<div class='progress position-relative'>
		<div
			:class='`progress-bar bg-${color}`'
			role='progressbar'
			:style='`width: ${signal}%`'
			:aria-valuenow='signal'
			aria-valuemin='0'
			aria-valuemax='100'
		>
			<span class='progress-bar-text'>
				{{ signal }}%
			</span>
		</div>
	</div>
</template>

<script lang='ts'>
import {CProgress} from '@coreui/vue/src';
import {Component, Prop, Vue} from 'vue-property-decorator';

/**
 * Signal strength indicator
 */
@Component({
	components: {
		CProgress,
	},
})
export default class SignalIndicator extends Vue {

	/**
	 * @property {number} signal Signal strength
	 */
	@Prop({type: Number, required: true}) signal!: number;

	/**
	 * @property {string} color Returns color for progress bar depending on signal strength
	 */
	get color(): string {
		if (this.signal >= 67) {
			return 'success';
		} else if (this.signal >= 34) {
			return 'warning';
		} else {
			return 'danger';
		}
	}

}
</script>

<style lang="scss" scoped>
.progress-bar-text {
	position: absolute;
	width: 100%;
	text-align: center;
	font-weight: bold;
	color: black;
}

</style>
