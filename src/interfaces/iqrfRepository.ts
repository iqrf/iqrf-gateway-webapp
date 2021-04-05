/**
 * IQRF Repository component instance interface
 */
export interface IIqrfRepository {
    /**
     * Component name
     */
    component: string
    
    /**
     * Component instance name
     */
    instance: string
    
    /**
     * Repository URL
     */
    urlRepo: string
    
    /**
     * Check period in minutes
     */
    checkPeriodInMinutes: number
    
    /**
     * Download date if repository cache is empty?
     */
	downloadIfRepoCacheEmpty: boolean
}
