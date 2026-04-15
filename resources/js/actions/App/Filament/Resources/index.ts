import ContactResource from './ContactResource'
import DealerEmailResource from './DealerEmailResource'
import DealerEmailTemplateResource from './DealerEmailTemplateResource'
import DealershipResource from './DealershipResource'
import EmailTrackingEventResource from './EmailTrackingEventResource'
import PdfAttachmentResource from './PdfAttachmentResource'
import ReminderResource from './ReminderResource'
import SentEmailResource from './SentEmailResource'
import UserResource from './UserResource'

const Resources = {
    ContactResource: Object.assign(ContactResource, ContactResource),
    DealerEmailResource: Object.assign(DealerEmailResource, DealerEmailResource),
    DealerEmailTemplateResource: Object.assign(DealerEmailTemplateResource, DealerEmailTemplateResource),
    DealershipResource: Object.assign(DealershipResource, DealershipResource),
    EmailTrackingEventResource: Object.assign(EmailTrackingEventResource, EmailTrackingEventResource),
    PdfAttachmentResource: Object.assign(PdfAttachmentResource, PdfAttachmentResource),
    ReminderResource: Object.assign(ReminderResource, ReminderResource),
    SentEmailResource: Object.assign(SentEmailResource, SentEmailResource),
    UserResource: Object.assign(UserResource, UserResource),
}

export default Resources