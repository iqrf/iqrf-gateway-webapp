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
	<v-dialog
		v-model='show'
		width='66%'
		persistent
		no-click-animation
	>
		<template #activator='{on, attrs}'>
			<v-btn
				color='primary'
				small
				v-bind='attrs'
				v-on='on'
			>
				{{ $t('iqrfnet.product.browse') }}
			</v-btn>
		</template>
		<v-card>
			<v-card-title>
				{{ $t('iqrfnet.product.title') }}
			</v-card-title>
			<v-card-text>
				<v-row>
					<v-col cols='12' md='6'>
						<v-text-field
							v-model='filters.companyName'
							:label='$t("iqrfnet.enumeration.manufacturer")'
						/>
					</v-col>
					<v-col cols='12' md='6'>
						<v-text-field
							v-model='filters.name'
							:label='$t("iqrfnet.enumeration.product")'
						/>
					</v-col>
				</v-row>
				<v-data-table
					:headers='header'
					:items='items'
					:loading='loading'
					mobile-breakpoint='0'
				>
					<template #[`item.actions`]='{item}'>
						<v-btn
							color='success'
							small
							@click='selectProduct(item)'
						>
							<v-icon small>
								mdi-check
							</v-icon>
						</v-btn>
					</template>
				</v-data-table>
			</v-card-text>
			<v-card-actions>
				<v-spacer />
				<v-btn
					@click='show = false'
				>
					{{ $t('forms.close') }}
				</v-btn>
			</v-card-actions>
		</v-card>
	</v-dialog>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';

import ProductService from '@/services/IqrfRepository/ProductService';

import {IProduct} from '@/interfaces/repository';
import {DataTableHeader} from 'vuetify';

/**
 * Product filters
 */
interface ProductFilters {
	companyName: string|null;
	name: string|null;
}

@Component({})

/**
 * Product modal component
 */
export default class ProductDialog extends Vue {

	/**
	 * @var {boolean} show Dialog visibility
	 */
	private show = false;

	/**
	 * @var {boolean} loading Indicates whether the product list is being loaded
	 */
	private loading = false;

	/**
	 * @var {ProductFilters} filters Product filters
	 */
	private filters: ProductFilters = {
		companyName: null,
		name: null,
	};

	/**
	 * @var {Array<IProduct>} products Array of products from repository
	 */
	private products: Array<IProduct> = [];

	/**
	 * @var {Array<DataTableHeader>} header Data table header
   */
	private header: Array<DataTableHeader> = [
		{
			value: 'companyName',
			text: this.$t('iqrfnet.enumeration.manufacturer').toString(),
			width: '20%',
		},
		{
			value: 'name',
			text: this.$t('iqrfnet.enumeration.product').toString(),
			width: '50%',
		},
		{
			value: 'hwpid',
			text: this.$t('iqrfnet.enumeration.hwpid').toString(),
			width: '15%',
		},
		{
			value: 'actions',
			text: this.$t('table.actions.title').toString(),
			sortable: false,
			filterable: false,
			align: 'end',
			width: '15%',
		},
	];

	/**
	 * Returns filtered products
	 * @return {Array<IProduct>} Filtered products
	 */
	get items(): Array<IProduct> {
		return this.products.filter((product: IProduct) => {
			return (
				(this.filters.companyName === null || product.companyName.toLowerCase().includes(this.filters.companyName.toLowerCase())) &&
				(this.filters.name === null || product.name.toLowerCase().includes(this.filters.name.toLowerCase()))
			);
		});
	}

	/**
	 * Retrieves products from repository
	 */
	private getProducts(): void {
		this.loading = true;
		ProductService.getAll()
			.then((products: Array<IProduct>) => {
				this.products = products;
				this.loading = false;
			})
			.catch(() => this.loading = false);
	}

	/**
	 * Selects product from table and emits data to the parent component
	 */
	private selectProduct(product: IProduct): void {
		this.show = false;
		this.$emit('selected-product', product);
	}

	/**
	 * Mounted lifecycle hook
	 */
	protected mounted(): void {
		this.getProducts();
	}

}
</script>
