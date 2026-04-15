import ListDealerEmailTemplates from './ListDealerEmailTemplates'
import CreateDealerEmailTemplate from './CreateDealerEmailTemplate'
import ViewDealerEmailTemplate from './ViewDealerEmailTemplate'
import EditDealerEmailTemplate from './EditDealerEmailTemplate'

const Pages = {
    ListDealerEmailTemplates: Object.assign(ListDealerEmailTemplates, ListDealerEmailTemplates),
    CreateDealerEmailTemplate: Object.assign(CreateDealerEmailTemplate, CreateDealerEmailTemplate),
    ViewDealerEmailTemplate: Object.assign(ViewDealerEmailTemplate, ViewDealerEmailTemplate),
    EditDealerEmailTemplate: Object.assign(EditDealerEmailTemplate, EditDealerEmailTemplate),
}

export default Pages