/**
 * Gateway log entry
 */
export interface IServiceLog {
    /**
     * Service name
     */
    name: string

    /**
     * Service log
     */
    log: string|null

    /**
     * Service log loaded
     */
    loaded: boolean
}