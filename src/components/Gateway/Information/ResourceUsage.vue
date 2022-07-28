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
	<div class='d-inline'>
		{{ $t('gateway.info.usages.used') }}
		{{ usage.usage.replace('%', ' %') }}
		({{ usage.used }} / {{ usage.size }})
		<v-progress-linear
			v-model='value'
			:color='color'
			height='1em'
			rounded
		/>
	</div>
</template>

<script lang='ts'>
import {Component, Prop, Vue} from 'vue-property-decorator';

/**
 * Resource usage data
 */
export interface UsageData {

	/**
	 * Usage as percentage
	 */
	usage: string;

	/**
	 * Used size
	 */
	used: string;

	/**
	 * Total size
	 */
	size: string;

}

@Component({})

/**
 * Resource usage component for gateway information
 */
export default class ResourceUsage extends Vue {
	/**
	 * @property {Record<string, string>} usage Dictionary of gateway device resource usage
	 */
	@Prop({required: true}) usage!: UsageData;

	/**
	 * Returns progress bar value
	 * @returns {number} Progress bar value
	 */
	get value(): number {
		return Number.parseFloat(this.usage.usage.replace('%', ''));
	}

	/**
	 * Returns progress bar color
	 * @return {string} Progress bar color
	 */
	get color(): string {
		if (this.value >= 90) {
			return 'error';
		} else if (this.value >= 80) {
			return 'warning';
		} else {
			return 'success';
		}
	}
}
</script>
