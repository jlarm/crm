import contacts from './contacts'
import dealerEmails from './dealer-emails'
import dealerEmailTemplates from './dealer-email-templates'
import dealerships from './dealerships'
import emailTrackingEvents from './email-tracking-events'
import pdfAttachments from './pdf-attachments'
import reminders from './reminders'
import sentEmails from './sent-emails'
import users from './users'
import shield from './shield'

const resources = {
    contacts: Object.assign(contacts, contacts),
    dealerEmails: Object.assign(dealerEmails, dealerEmails),
    dealerEmailTemplates: Object.assign(dealerEmailTemplates, dealerEmailTemplates),
    dealerships: Object.assign(dealerships, dealerships),
    emailTrackingEvents: Object.assign(emailTrackingEvents, emailTrackingEvents),
    pdfAttachments: Object.assign(pdfAttachments, pdfAttachments),
    reminders: Object.assign(reminders, reminders),
    sentEmails: Object.assign(sentEmails, sentEmails),
    users: Object.assign(users, users),
    shield: Object.assign(shield, shield),
}

export default resources