/**
 * Product interface
 */
export interface IProduct {
	/**
	 * Product HWPID
	 */
	hwpid: number

	/**
	 * Product name
	 */
	name: string

	/**
	 * Product manufacturer identifier
	 */
	manufacturerID: number

	/**
	 * Company name
	 */
	companyName: string

	/**
	 * Product image url
	 */
	picture: string

	/**
	 * RF mode
	 */
	rfMode: number

	/**
	 * Original product image
	 */
	pictureOriginal: string
}
