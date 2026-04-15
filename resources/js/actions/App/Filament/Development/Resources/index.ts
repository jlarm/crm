import DealershipResource from './DealershipResource'
import ReminderResource from './ReminderResource'

const Resources = {
    DealershipResource: Object.assign(DealershipResource, DealershipResource),
    ReminderResource: Object.assign(ReminderResource, ReminderResource),
}

export default Resources