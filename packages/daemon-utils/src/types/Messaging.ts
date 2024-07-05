import { type MessagingType } from '../enums';

/**
 * Messaging service instance interface
 */
export interface MessagingInstance {
	/// Instance name
	instance: string;
	/// Messaging type
	type: MessagingType;
}
