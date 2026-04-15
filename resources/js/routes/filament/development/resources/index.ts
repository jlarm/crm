import dealerships from './dealerships'
import reminders from './reminders'

const resources = {
    dealerships: Object.assign(dealerships, dealerships),
    reminders: Object.assign(reminders, reminders),
}

export default resources