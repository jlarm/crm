import ListReminders from './ListReminders'
import CreateReminder from './CreateReminder'
import EditReminder from './EditReminder'

const Pages = {
    ListReminders: Object.assign(ListReminders, ListReminders),
    CreateReminder: Object.assign(CreateReminder, CreateReminder),
    EditReminder: Object.assign(EditReminder, EditReminder),
}

export default Pages