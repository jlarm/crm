import MailgunWebhookController from './MailgunWebhookController'
import DashboardController from './DashboardController'
import Settings from './Settings'

const Controllers = {
    MailgunWebhookController: Object.assign(MailgunWebhookController, MailgunWebhookController),
    DashboardController: Object.assign(DashboardController, DashboardController),
    Settings: Object.assign(Settings, Settings),
}

export default Controllers