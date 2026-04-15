import ListSentEmails from './ListSentEmails'
import CreateSentEmail from './CreateSentEmail'
import EditSentEmail from './EditSentEmail'

const Pages = {
    ListSentEmails: Object.assign(ListSentEmails, ListSentEmails),
    CreateSentEmail: Object.assign(CreateSentEmail, CreateSentEmail),
    EditSentEmail: Object.assign(EditSentEmail, EditSentEmail),
}

export default Pages